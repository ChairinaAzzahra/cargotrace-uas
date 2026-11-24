<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'country',
        'type',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_id');
    }

    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_id');
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class);
    }
}
