<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the operational overview dashboard.
     * Generates live aggregated stats from database (Slide 10 of brief).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Base query scoped to role
        $shipmentsQuery = Shipment::query();
        $exceptionsQuery = ShipmentException::query();

        if ($user->isCustomer()) {
            $shipmentsQuery->where('customer_id', $user->id);
            $exceptionsQuery->whereHas('shipment', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }

        $totalShipments = (clone $shipmentsQuery)->count();
        $inTransitCount = (clone $shipmentsQuery)->where('current_status', Shipment::STATUS_IN_TRANSIT)->count();
        $deliveredCount = (clone $shipmentsQuery)->where('current_status', Shipment::STATUS_DELIVERED)->count();
        $delayedCount = (clone $shipmentsQuery)->where('delay_status', 'DELAYED')->count();
        $openExceptionsCount = (clone $exceptionsQuery)->where('status', '!=', 'RESOLVED')->count();

        // Rates
        $onTimeRate = $totalShipments > 0 
            ? round((($totalShipments - $delayedCount) / $totalShipments) * 100, 1) 
            : 100;

        $exceptionRate = $totalShipments > 0
            ? round(($openExceptionsCount / $totalShipments) * 100, 1)
            : 0;

        $avgDelayHours = (clone $shipmentsQuery)->where('delay_status', 'DELAYED')->avg('delay_duration_hours') ?? 0;

        // Breakdown by status for Chart
        $statusCounts = (clone $shipmentsQuery)
            ->select('current_status', DB::raw('count(*) as total'))
            ->groupBy('current_status')
            ->pluck('total', 'current_status')
            ->toArray();

        $allStatuses = [
            Shipment::STATUS_CREATED => $statusCounts[Shipment::STATUS_CREATED] ?? 0,
            Shipment::STATUS_BOOKED => $statusCounts[Shipment::STATUS_BOOKED] ?? 0,
            Shipment::STATUS_PICKED_UP => $statusCounts[Shipment::STATUS_PICKED_UP] ?? 0,
            Shipment::STATUS_IN_TRANSIT => $statusCounts[Shipment::STATUS_IN_TRANSIT] ?? 0,
            Shipment::STATUS_DELIVERED => $statusCounts[Shipment::STATUS_DELIVERED] ?? 0,
        ];

        // Breakdown by carrier for Chart
        $carrierCounts = (clone $shipmentsQuery)
            ->join('carriers', 'shipments.carrier_id', '=', 'carriers.id')
            ->select('carriers.name', DB::raw('count(*) as total'))
            ->groupBy('carriers.name')
            ->pluck('total', 'carriers.name')
            ->toArray();

        // Recent shipments
        $recentShipments = (clone $shipmentsQuery)
            ->with(['carrier', 'origin', 'destination', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', [
            'totalShipments' => $totalShipments,
            'inTransitCount' => $inTransitCount,
            'deliveredCount' => $deliveredCount,
            'delayedCount' => $delayedCount,
            'openExceptionsCount' => $openExceptionsCount,
            'onTimeRate' => $onTimeRate,
            'exceptionRate' => $exceptionRate,
            'avgDelayHours' => round($avgDelayHours, 1),
            'statusCounts' => $allStatuses,
            'carrierCounts' => $carrierCounts,
            'recentShipments' => $recentShipments,
        ]);
    }
}
