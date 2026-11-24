<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Services\ShipmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ShipmentLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected User $operator;
    protected Shipment $shipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->operator = User::factory()->create(['role' => 'operator']);
        $customer = User::factory()->create(['role' => 'customer']);

        $carrier = Carrier::create(['code' => 'EMC', 'name' => 'Evergreen', 'is_active' => true]);
        $origin = Location::create(['code' => 'CNSHA', 'name' => 'Shanghai Port', 'city' => 'Shanghai', 'country' => 'China', 'type' => 'PORT', 'latitude' => 31.23, 'longitude' => 121.47]);
        $dest = Location::create(['code' => 'USLAX', 'name' => 'Port of Los Angeles', 'city' => 'Los Angeles', 'country' => 'USA', 'type' => 'PORT', 'latitude' => 33.74, 'longitude' => -118.26]);

        $this->shipment = Shipment::create([
            'shipment_number' => 'CT-2026-5555',
            'customer_id' => $customer->id,
            'carrier_id' => $carrier->id,
            'origin_id' => $origin->id,
            'destination_id' => $dest->id,
            'cargo_type' => 'Semiconductors',
            'total_weight_kg' => 2000,
            'total_volume_cbm' => 10,
            'expected_departure_at' => now(),
            'expected_arrival_at' => now()->addDays(12),
            'current_status' => 'CREATED',
        ]);
    }

    public function test_valid_step_by_step_lifecycle_transition(): void
    {
        $service = app(ShipmentService::class);

        // 1. CREATED -> BOOKED
        $service->transitionStatus($this->shipment, 'BOOKED', ['description' => 'Carrier booking confirmed'], $this->operator->id);
        $this->assertEquals('BOOKED', $this->shipment->fresh()->current_status);

        // 2. BOOKED -> PICKED_UP
        $service->transitionStatus($this->shipment, 'PICKED_UP', ['description' => 'Container collected at warehouse'], $this->operator->id);
        $this->assertEquals('PICKED_UP', $this->shipment->fresh()->current_status);

        // 3. PICKED_UP -> IN_TRANSIT
        $service->transitionStatus($this->shipment, 'IN_TRANSIT', ['description' => 'Vessel departed origin'], $this->operator->id);
        $this->assertEquals('IN_TRANSIT', $this->shipment->fresh()->current_status);

        // 4. IN_TRANSIT -> DELIVERED (Terminal)
        $service->transitionStatus($this->shipment, 'DELIVERED', ['description' => 'Discharged and cleared at destination'], $this->operator->id);
        $this->assertEquals('DELIVERED', $this->shipment->fresh()->current_status);
        $this->assertTrue($this->shipment->fresh()->isTerminal());
    }

    public function test_invalid_lifecycle_transition_is_blocked(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $service = app(ShipmentService::class);
        // Attempt jumping directly from CREATED to DELIVERED
        $service->transitionStatus($this->shipment, 'DELIVERED', [], $this->operator->id);
    }

    public function test_terminal_delivered_status_cannot_be_reverted(): void
    {
        $service = app(ShipmentService::class);

        // Transition through to DELIVERED
        $service->transitionStatus($this->shipment, 'BOOKED', [], $this->operator->id);
        $service->transitionStatus($this->shipment, 'PICKED_UP', [], $this->operator->id);
        $service->transitionStatus($this->shipment, 'IN_TRANSIT', [], $this->operator->id);
        $service->transitionStatus($this->shipment, 'DELIVERED', [], $this->operator->id);

        $this->expectException(InvalidArgumentException::class);
        // Attempt moving backward from DELIVERED to IN_TRANSIT
        $service->transitionStatus($this->shipment, 'IN_TRANSIT', [], $this->operator->id);
    }
}
