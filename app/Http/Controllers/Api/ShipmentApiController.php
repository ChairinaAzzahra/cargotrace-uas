<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShipmentRequest;
use App\Http\Requests\StoreTrackingEventRequest;
use App\Http\Requests\TransitionStatusRequest;
use App\Http\Requests\UpdateShipmentRequest;
use App\Models\Shipment;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

class ShipmentApiController extends Controller
{
    public function __construct(
        protected ShipmentService $shipmentService,
        protected TrackingService $trackingService
    ) {}

    /**
     * GET /api/shipments
     * List shipments with server-side pagination, sorting, and multi-field filters.
     * Enforces RBAC: Customers can ONLY see their own shipments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Shipment::with(['customer:id,name,email,company_name', 'carrier:id,name,code', 'origin:id,name,city,country', 'destination:id,name,city,country']);

        // Scope to customer's own shipments if not admin or operator
        if ($user->isCustomer()) {
            $query->where('customer_id', $user->id);
        } elseif ($request->filled('customer_id')) {
            $query->where('customer_id', $request->query('customer_id'));
        }

        // Search by shipment number or notes
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('shipment_number', 'like', "%{$search}%")
                  ->orWhere('cargo_type', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        // Filter by carrier
        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->query('carrier_id'));
        }

        // Filter by delay status
        if ($request->filled('delay_status')) {
            $query->where('delay_status', $request->query('delay_status'));
        }

        // Filter by origin / destination
        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->query('origin_id'));
        }
        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->query('destination_id'));
        }

        // Sorting
        $sortField = $request->query('sort_by', 'created_at');
        $sortDirection = $request->query('sort_direction', 'desc');
        $allowedSorts = ['id', 'shipment_number', 'created_at', 'expected_departure_at', 'expected_arrival_at', 'delay_duration_hours'];
        if (in_array($sortField, $allowedSorts, true)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min((int) $request->query('per_page', 15), 100);
        $shipments = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $shipments->items(),
            'meta' => [
                'current_page' => $shipments->currentPage(),
                'last_page' => $shipments->lastPage(),
                'per_page' => $shipments->perPage(),
                'total' => $shipments->total(),
            ],
        ], 200);
    }

    /**
     * POST /api/shipments
     * Create a new shipment (Admin/Operator only).
     */
    public function store(StoreShipmentRequest $request): JsonResponse
    {
        $shipment = $this->shipmentService->createShipment($request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created successfully.',
            'data' => $shipment->load(['items', 'origin', 'destination', 'carrier', 'customer']),
        ], 201);
    }

    /**
     * GET /api/shipments/{id}
     * Retrieve single shipment details. Enforces authorization.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::with([
            'customer:id,name,email,company_name,phone',
            'carrier',
            'origin',
            'destination',
            'items',
            'trackingEvents.location',
            'trackingEvents.creator:id,name',
            'exceptions.resolver:id,name'
        ])->find($id);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not found.',
            ], 404);
        }

        // Check authorization policy
        if ($request->user()->cannot('view', $shipment)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: You do not have permission to view this shipment.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ], 200);
    }

    /**
     * PUT/PATCH /api/shipments/{id}
     * Update shipment metadata.
     */
    public function update(UpdateShipmentRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('update', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $updated = $this->shipmentService->updateShipment($shipment, $request->validated(), $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Shipment updated successfully.',
            'data' => $updated->fresh(['origin', 'destination', 'carrier', 'customer']),
        ], 200);
    }

    /**
     * DELETE /api/shipments/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('delete', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden: Only administrators can delete shipments.'], 403);
        }

        $this->shipmentService->deleteShipment($shipment, $request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Shipment deleted successfully.',
        ], 200);
    }

    /**
     * POST /api/shipments/{id}/transition
     * Execute lifecycle status transition with business rules validation.
     */
    public function transition(TransitionStatusRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('transition', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $event = $this->shipmentService->transitionStatus(
                $shipment,
                $request->input('status'),
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => "Shipment status successfully transitioned to {$request->input('status')}.",
                'data' => [
                    'shipment' => $shipment->fresh(),
                    'tracking_event' => $event,
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /api/shipments/{id}/tracking
     * Chronological tracking timeline.
     */
    public function tracking(Request $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('view', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $events = $shipment->trackingEvents()
            ->with(['location', 'creator:id,name', 'exception'])
            ->orderBy('event_time', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $events,
        ], 200);
    }

    /**
     * POST /api/shipments/{id}/tracking
     * Add historical tracking event.
     */
    public function addTracking(StoreTrackingEventRequest $request, int $id): JsonResponse
    {
        $shipment = Shipment::find($id);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('addTracking', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $event = $this->trackingService->addEvent($shipment, $request->validated(), $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Tracking event added successfully.',
                'data' => $event->load(['location', 'creator:id,name']),
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
