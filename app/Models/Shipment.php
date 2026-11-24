<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    public const STATUS_CREATED = 'CREATED';
    public const STATUS_BOOKED = 'BOOKED';
    public const STATUS_PICKED_UP = 'PICKED_UP';
    public const STATUS_IN_TRANSIT = 'IN_TRANSIT';
    public const STATUS_DELIVERED = 'DELIVERED';

    public const VALID_TRANSITIONS = [
        self::STATUS_CREATED => [self::STATUS_BOOKED],
        self::STATUS_BOOKED => [self::STATUS_PICKED_UP],
        self::STATUS_PICKED_UP => [self::STATUS_IN_TRANSIT],
        self::STATUS_IN_TRANSIT => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [], // Terminal: cannot transition
    ];

    protected $fillable = [
        'shipment_number',
        'customer_id',
        'carrier_id',
        'origin_id',
        'destination_id',
        'cargo_type',
        'total_weight_kg',
        'total_volume_cbm',
        'expected_departure_at',
        'expected_arrival_at',
        'estimated_arrival_at',
        'current_status',
        'delay_status',
        'delay_duration_hours',
        'delay_severity',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_weight_kg' => 'float',
            'total_volume_cbm' => 'float',
            'expected_departure_at' => 'datetime',
            'expected_arrival_at' => 'datetime',
            'estimated_arrival_at' => 'datetime',
            'delay_duration_hours' => 'integer',
        ];
    }

    public function isTerminal(): bool
    {
        return $this->current_status === self::STATUS_DELIVERED;
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        $allowed = self::VALID_TRANSITIONS[$this->current_status] ?? [];
        return in_array($targetStatus, $allowed, true);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function carrier()
    {
        return $this->belongsTo(Carrier::class);
    }

    public function origin()
    {
        return $this->belongsTo(Location::class, 'origin_id');
    }

    public function destination()
    {
        return $this->belongsTo(Location::class, 'destination_id');
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class)->orderBy('event_time', 'asc');
    }

    public function latestTrackingEvent()
    {
        return $this->hasOne(TrackingEvent::class)->latestOfMany('event_time');
    }

    public function exceptions()
    {
        return $this->hasMany(ShipmentException::class);
    }

    public function openExceptions()
    {
        return $this->hasMany(ShipmentException::class)->where('status', '!=', 'RESOLVED');
    }
}
