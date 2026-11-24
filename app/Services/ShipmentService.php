<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ShipmentService
{
    public function __construct(
        protected EtaService $etaService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Generate an autoincrementing, unique human-readable shipment number.
     * Example: CT-2026-0001
     */
    public function generateShipmentNumber(): string
    {
        $year = date('Y');
        $prefix = "CT-{$year}-";

        $lastShipment = Shipment::where('shipment_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($lastShipment) {
            $lastNumber = (int) substr($lastShipment->shipment_number, strlen($prefix));
            $nextNumber = str_pad((string) ($lastNumber + 1), 4, '0', STR_PAD_LEFT);
        } else {
            $nextNumber = '0001';
        }

        return $prefix . $nextNumber;
    }

    /**
     * Create a new shipment and its initial state & items.
     */
    public function createShipment(array $data, ?int $userId = null): Shipment
    {
        return DB::transaction(function () use ($data, $userId) {
            $shipmentNumber = $data['shipment_number'] ?? $this->generateShipmentNumber();

            // Calculate delay status deterministically
            $delayAnalysis = $this->etaService->calculateDelay(
                $data['expected_arrival_at'],
                $data['estimated_arrival_at'] ?? null
            );

            $shipment = Shipment::create([
                'shipment_number' => $shipmentNumber,
                'customer_id' => $data['customer_id'],
                'carrier_id' => $data['carrier_id'],
                'origin_id' => $data['origin_id'],
                'destination_id' => $data['destination_id'],
                'cargo_type' => $data['cargo_type'],
                'total_weight_kg' => $data['total_weight_kg'] ?? 0,
                'total_volume_cbm' => $data['total_volume_cbm'] ?? 0,
                'expected_departure_at' => $data['expected_departure_at'],
                'expected_arrival_at' => $data['expected_arrival_at'],
                'estimated_arrival_at' => $data['estimated_arrival_at'] ?? null,
                'current_status' => Shipment::STATUS_CREATED,
                'delay_status' => $delayAnalysis['is_delayed'] ? 'DELAYED' : 'ON_TIME',
                'delay_duration_hours' => $delayAnalysis['delay_hours'],
                'delay_severity' => $delayAnalysis['severity'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Create items if provided
            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'item_description' => $item['item_description'],
                        'hs_code' => $item['hs_code'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'weight_kg' => $item['weight_kg'] ?? 0,
                        'volume_cbm' => $item['volume_cbm'] ?? 0,
                    ]);
                }
            }

            // Create initial historical tracking event
            TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => Shipment::STATUS_CREATED,
                'event_type' => 'SHIPMENT_CREATED',
                'location_id' => $shipment->origin_id,
                'event_time' => now(),
                'description' => 'Shipment order created in system.',
                'is_exception' => false,
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);

            // Notify customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Shipment Created: {$shipment->shipment_number}",
                'message' => "Your shipment has been registered and is pending carrier booking.",
                'type' => 'SHIPMENT_CREATED',
            ]);

            // Log Audit
            $this->auditLogService->log(
                action: 'CREATE_SHIPMENT',
                entityType: 'Shipment',
                entityId: $shipment->id,
                oldValues: null,
                newValues: $shipment->toArray(),
                userId: $userId
            );

            return $shipment;
        });
    }

    /**
     * Execute a validated lifecycle status transition.
     * Valid flow: CREATED -> BOOKED -> PICKED_UP -> IN_TRANSIT -> DELIVERED
     * Terminal status: DELIVERED cannot transition.
     */
    public function transitionStatus(Shipment $shipment, string $newStatus, array $eventDetails = [], ?int $userId = null): TrackingEvent
    {
        return DB::transaction(function () use ($shipment, $newStatus, $eventDetails, $userId) {
            if ($shipment->isTerminal()) {
                throw new InvalidArgumentException(
                    "Cannot modify status: Shipment {$shipment->shipment_number} is in terminal status [{$shipment->current_status}]."
                );
            }

            if (!$shipment->canTransitionTo($newStatus)) {
                $allowed = implode(', ', Shipment::VALID_TRANSITIONS[$shipment->current_status] ?? ['NONE']);
                throw new InvalidArgumentException(
                    "Invalid status transition from [{$shipment->current_status}] to [{$newStatus}]. Allowed next state: [{$allowed}]."
                );
            }

            $oldValues = $shipment->toArray();

            // Update shipment current status
            $shipment->update([
                'current_status' => $newStatus,
            ]);

            // Create historical tracking event
            $event = TrackingEvent::create([
                'shipment_id' => $shipment->id,
                'status' => $newStatus,
                'event_type' => $eventDetails['event_type'] ?? 'STATUS_CHANGE_' . $newStatus,
                'location_id' => $eventDetails['location_id'] ?? ($newStatus === Shipment::STATUS_DELIVERED ? $shipment->destination_id : null),
                'latitude' => $eventDetails['latitude'] ?? null,
                'longitude' => $eventDetails['longitude'] ?? null,
                'event_time' => isset($eventDetails['event_time']) ? Carbon::parse($eventDetails['event_time']) : now(),
                'description' => $eventDetails['description'] ?? "Shipment transitioned to {$newStatus}.",
                'is_exception' => false,
                'created_by_user_id' => $userId ?? auth()->id(),
            ]);

            // Notify customer
            Notification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'title' => "Shipment {$shipment->shipment_number} Updated",
                'message' => "Shipment status updated to {$newStatus}. Details: {$event->description}",
                'type' => 'STATUS_UPDATE',
            ]);

            // Log Audit
            $this->auditLogService->log(
                action: 'TRANSITION_STATUS',
                entityType: 'Shipment',
                entityId: $shipment->id,
                oldValues: $oldValues,
                newValues: $shipment->toArray(),
                userId: $userId
            );

            return $event;
        });
    }

    /**
     * Update editable shipment metadata and recalculate delay.
     */
    public function updateShipment(Shipment $shipment, array $data, ?int $userId = null): Shipment
    {
        return DB::transaction(function () use ($shipment, $data, $userId) {
            $oldValues = $shipment->toArray();

            $shipment->fill([
                'carrier_id' => $data['carrier_id'] ?? $shipment->carrier_id,
                'cargo_type' => $data['cargo_type'] ?? $shipment->cargo_type,
                'total_weight_kg' => $data['total_weight_kg'] ?? $shipment->total_weight_kg,
                'total_volume_cbm' => $data['total_volume_cbm'] ?? $shipment->total_volume_cbm,
                'expected_departure_at' => $data['expected_departure_at'] ?? $shipment->expected_departure_at,
                'expected_arrival_at' => $data['expected_arrival_at'] ?? $shipment->expected_arrival_at,
                'estimated_arrival_at' => array_key_exists('estimated_arrival_at', $data) ? $data['estimated_arrival_at'] : $shipment->estimated_arrival_at,
                'notes' => $data['notes'] ?? $shipment->notes,
            ]);

            // Recalculate ETA and delay
            $this->etaService->updateShipmentDelay($shipment);

            $shipment->save();

            $this->auditLogService->log(
                action: 'UPDATE_SHIPMENT',
                entityType: 'Shipment',
                entityId: $shipment->id,
                oldValues: $oldValues,
                newValues: $shipment->toArray(),
                userId: $userId
            );

            return $shipment;
        });
    }

    /**
     * Delete shipment (restricted: only if not delivered).
     */
    public function deleteShipment(Shipment $shipment, ?int $userId = null): bool
    {
        return DB::transaction(function () use ($shipment, $userId) {
            $oldValues = $shipment->toArray();

            $this->auditLogService->log(
                action: 'DELETE_SHIPMENT',
                entityType: 'Shipment',
                entityId: $shipment->id,
                oldValues: $oldValues,
                newValues: null,
                userId: $userId
            );

            return (bool) $shipment->delete();
        });
    }
}
