<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResolveExceptionRequest;
use App\Http\Requests\StoreExceptionRequest;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Http\Requests\TransitionStatusRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Services\ExceptionService;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use App\Services\WeatherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class ShipmentController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService,
        protected ExceptionService $exceptionService,
        protected WeatherService $weatherService
    ) {}

    /**
     * Display a listing of shipments with multi-field search and filters.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination']);

        // RBAC: Customer can only see their own
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->query('carrier_id'));
        }

        if ($request->filled('delay_status')) {
            $query->where('delay_status', $request->query('delay_status'));
        }

        $shipments = $query->latest()->paginate(10)->withQueryString();

        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.index', [
            'shipments' => $shipments,
            'carriers' => $carriers,
            'locations' => $locations,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Show form to create a new shipment.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Shipment::class);

        return view('shipments.create', [
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
            'customers' => User::where('role', 'customer')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store new shipment via Service Layer.
     */
    public function store(StoreShipmentRequest $request): RedirectResponse
    {
        $shipment = $this->shipmentService->createShipment($request->validated(), $request->user()->id);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} created successfully.");
    }

    /**
     * Detailed shipment visibility portal view: Map, Weather, Lifecycle & Timeline.
     */
    public function show(Request $request, Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions.resolver',
        ]);

        // Fetch live weather data for origin or current location
        $weatherLocation = $shipment->latestTrackingEvent?->location ?? $shipment->origin;
        $weatherData = $this->weatherService->getCurrentWeather(
            $weatherLocation->latitude,
            $weatherLocation->longitude
        );

        return view('shipments.show', [
            'shipment' => $shipment,
            'weatherData' => $weatherData,
            'weatherLocation' => $weatherLocation,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * Show form to edit shipment.
     */
    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        return view('shipments.edit', [
            'shipment' => $shipment,
            'carriers' => Carrier::where('is_active', true)->orderBy('name')->get(),
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * Update shipment.
     */
    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->shipmentService->updateShipment($shipment, $request->validated(), $request->user()->id);

        return redirect()->route('shipments.show', $shipment)
            ->with('success', "Shipment {$shipment->shipment_number} details updated.");
    }

    /**
     * Delete shipment.
     */
    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        $this->shipmentService->deleteShipment($shipment, auth()->id());

        return redirect()->route('shipments.index')
            ->with('success', "Shipment {$shipment->shipment_number} deleted successfully.");
    }

    /**
     * Transition shipment status via lifecycle state machine.
     */
    public function transition(TransitionStatusRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('transition', $shipment);

        try {
            $this->shipmentService->transitionStatus(
                $shipment,
                $request->input('status'),
                $request->validated(),
                $request->user()->id
            );

            return redirect()->route('shipments.show', $shipment)
                ->with('success', "Shipment status successfully transitioned to {$request->input('status')}.");
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add tracking event.
     */
    public function addTracking(StoreTrackingEventRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('addTracking', $shipment);

        try {
            $this->trackingService->addEvent($shipment, $request->validated(), $request->user()->id);

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'Tracking event recorded successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Raise exception.
     */
    public function raiseException(StoreExceptionRequest $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('manageExceptions', $shipment);

        $this->exceptionService->raiseException($shipment, $request->validated(), $request->user()->id);

        return redirect()->route('shipments.show', $shipment)
            ->with('warning', 'Exception flagged and notification dispatched.');
    }

    /**
     * Resolve exception.
     */
    public function resolveException(ResolveExceptionRequest $request, Shipment $shipment, ShipmentException $exception): RedirectResponse
    {
        $this->authorize('manageExceptions', $shipment);

        try {
            $this->exceptionService->resolveException($exception, $request->validated(), $request->user()->id);

            return redirect()->route('shipments.show', $shipment)
                ->with('success', 'Exception resolved successfully.');
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
