<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TrackingService
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Add a historical tracking event.
     * Tracking events are append-only (never overwrite existing events).
     */
    public function addEvent(Shipment $shipment, array $data, ?int $userId = null): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $data, $userId) {
            $eventTime = isset($data['event_time']) 
                ? Carbon::parse($data['event_time']) 
                : now();

            // Validate that event timestamp is not earlier than the latest event by more than reasonable threshold
            $latestEvent = $shipment->latestTrackingEvent;
            if ($latestEvent && $eventTime->lt($latestEvent->event_time->subHours(24))) {
                throw new InvalidArgumentException(
                    "Event timestamp cannot precede previous recorded event ({$latestEvent->event_time->toDateTimeString()}) by more than 24 hours without operational review."
                );
            }

            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $data['status'] ?? $shipment->current_status,
                'event_type' => $data['event_type'],
                'location_id' => $data['location_id'] ?? null,
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
                'event_time' => $eventTime,
                'description' => $data['description'],
                'is_exception' => (bool) ($data['is_exception'] ?? false),
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);

            // Notify customer of significant milestone
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Shipment Update: {$shipment->shipment_number}",
                'message' => "New tracking event: {$data['event_type']} - {$data['description']}",
                'type' => $event->is_exception ? 'EXCEPTION_ALERT' : 'STATUS_UPDATE',
            ]);

            $this->auditLogService->log(
                action: 'ADD_TRACKING_EVENT',
                entityType: 'TrackingEvent',
                entityId: $event->id,
                oldValues: null,
                newValues: $event->toArray(),
                userId: $userId
            );

            return $event;
        });
    }
}
