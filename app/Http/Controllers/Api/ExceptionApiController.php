<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResolveExceptionRequest;
use App\Http\Requests\StoreExceptionRequest;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Services\ExceptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ExceptionApiController extends Controller
{
    public function __construct(
        protected ExceptionService $exceptionService
    ) {}

    /**
     * GET /api/shipments/{id}/exceptions
     */
    public function indexForShipment(Request $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('view', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $exceptions = $shipment->exceptions()->with(['trackingEvent', 'resolver:id,name'])->get();
        return response()->json(['success' => true, 'data' => $exceptions], 200);
    }

    /**
     * POST /api/shipments/{id}/exceptions
     * Raise an exception.
     */
    public function store(StoreExceptionRequest $request, int $shipmentId): JsonResponse
    {
        $shipment = Shipment::find($shipmentId);
        if (!$shipment) {
            return response()->json(['success' => false, 'message' => 'Shipment not found.'], 404);
        }

        if ($request->user()->cannot('manageExceptions', $shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        $exception = $this->exceptionService->raiseException(
            $shipment,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Exception recorded and operational team alerted.',
            'data' => $exception->load(['trackingEvent']),
        ], 201);
    }

    /**
     * PATCH /api/exceptions/{id}
     * Resolve an exception.
     */
    public function resolve(ResolveExceptionRequest $request, int $id): JsonResponse
    {
        $exception = ShipmentException::with('shipment')->find($id);
        if (!$exception) {
            return response()->json(['success' => false, 'message' => 'Exception record not found.'], 404);
        }

        if ($request->user()->cannot('manageExceptions', $exception->shipment)) {
            return response()->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        try {
            $resolved = $this->exceptionService->resolveException(
                $exception,
                $request->validated(),
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Exception resolved successfully.',
                'data' => $resolved->load(['resolver:id,name']),
            ], 200);
        } catch (InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
