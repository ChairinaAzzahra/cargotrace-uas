<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ExceptionService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Raise an operational exception on a shipment.
     */
    public function raiseException(Shipment $shipment, array $data, ?int $userId = null): ShipmentException
    {
        return DB::transaction(function () use ($shipment, $data, $userId) {
            $detectedAt = isset($data['detected_at']) ? Carbon::parse($data['detected_at']) : now();

            // 1. Create a linked tracking event if requested or appropriate
            $trackingEvent = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $shipment->current_status,
                'event_type' => 'EXCEPTION_' . $data['exception_type'],
                'location_id' => $data['location_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'event_time' => $detectedAt,
                'description' => "Operational Exception ({$data['exception_type']}): " . ($data['root_cause'] ?? 'Investigation initiated'),
                'is_exception' => true,
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);

            // 2. Create the exception record
            $exception = ShipmentException::create([
                'shipment_id' => $shipment->id,
                'tracking_event_id' => $trackingEvent->id,
                'exception_type' => $data['exception_type'],
                'severity' => $data['severity'] ?? 'MEDIUM',
                'status' => 'OPEN',
                'root_cause' => $data['root_cause'] ?? null,
                'resolution_notes' => null,
                'detected_at' => $detectedAt,
                'resolved_at' => null,
                'resolved_by_user_id' => null,
            ]);

            // 3. Notify customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Exception Alert: {$shipment->shipment_number}",
                'message' => "Shipment has encountered a {$data['exception_type']} ({$data['severity']} severity). Support team is investigating.",
                'type' => 'EXCEPTION_RAISED',
            ]);

            // 4. Log audit
            $this->auditLogService->log(
                action: 'RAISE_EXCEPTION',
                entityType: 'ShipmentException',
                entityId: $exception->id,
                oldValues: null,
                newValues: $exception->toArray(),
                userId: $userId
            );

            return $exception;
        });
    }

    /**
     * Resolve an existing exception.
     */
    public function resolveException(ShipmentException $exception, array $data, ?int $userId = null): ShipmentException
    {
        return DB::transaction(function () use ($exception, $data, $userId) {
            if ($exception->status === 'RESOLVED') {
                throw new InvalidArgumentException("Exception is already marked as resolved.");
            }

            $oldValues = $exception->toArray();

            $exception->update([
                'status' => 'RESOLVED',
                'resolution_notes' => $data['resolution_notes'] ?? 'Issue resolved by logistics operations.',
                'resolved_at' => now(),
                'resolved_by_user_id' => $userId ?? auth()->id(),
            ]);

            // Create tracking event for resolution
            TrackingEvent::create([
                'shipment_id' => $exception->shipment_id,
                'status' => $exception->shipment->current_status,
                'event_type' => 'EXCEPTION_RESOLVED',
                'location_id' => null,
                'event_time' => now(),
                'description' => "Resolved {$exception->exception_type}: " . ($data['resolution_notes'] ?? 'Normal handling resumed'),
                'is_exception' => false,
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);

            // Notify customer
            Notification::create([
                'user_id' => $exception->shipment->customer_id,
                'shipment_id' => $exception->shipment_id,
                'title' => "Exception Resolved: {$exception->shipment->shipment_number}",
                'message' => "The {$exception->exception_type} exception has been resolved. Shipment processing continues.",
                'type' => 'EXCEPTION_RESOLVED',
            ]);

            $this->auditLogService->log(
                action: 'RESOLVE_EXCEPTION',
                entityType: 'ShipmentException',
                entityId: $exception->id,
                oldValues: $oldValues,
                newValues: $exception->toArray(),
                userId: $userId
            );

            return $exception;
        });
    }
}
