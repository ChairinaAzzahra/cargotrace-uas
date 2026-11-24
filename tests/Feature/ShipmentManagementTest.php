<?php

namespace Tests\Feature;

use App\Models\Carrier;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer1;
    protected User $customer2;
    protected Carrier $carrier;
    protected Location $origin;
    protected Location $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer1 = User::factory()->create(['role' => 'customer']);
        $this->customer2 = User::factory()->create(['role' => 'customer']);

        $this->carrier = Carrier::create([
            'code' => 'MAEU',
            'name' => 'Maersk Line',
            'is_active' => true,
        ]);

        $this->origin = Location::create([
            'code' => 'IDJKT',
            'name' => 'Port of Tanjung Priok',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'type' => 'PORT',
            'latitude' => -6.1012,
            'longitude' => 106.8841,
        ]);

        $this->destination = Location::create([
            'code' => 'NLRTM',
            'name' => 'Port of Rotterdam',
            'city' => 'Rotterdam',
            'country' => 'Netherlands',
            'type' => 'PORT',
            'latitude' => 51.9244,
            'longitude' => 4.4777,
        ]);
    }

    public function test_operator_or_admin_can_create_shipment(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/shipments', [
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Automotive Parts',
            'total_weight_kg' => 8500,
            'total_volume_cbm' => 24.5,
            'expected_departure_at' => now()->toDateTimeString(),
            'expected_arrival_at' => now()->addDays(10)->toDateTimeString(),
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.current_status', 'CREATED');

        $this->assertDatabaseHas('shipments', [
            'customer_id' => $this->customer1->id,
            'cargo_type' => 'Automotive Parts',
            'current_status' => 'CREATED',
        ]);
    }

    public function test_customer_cannot_create_shipment(): void
    {
        $response = $this->actingAs($this->customer1)->postJson('/api/shipments', [
            'customer_id' => $this->customer1->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Unauthorized Cargo',
            'total_weight_kg' => 100,
            'total_volume_cbm' => 1,
            'expected_departure_at' => now()->toDateTimeString(),
            'expected_arrival_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertStatus(403);
    }

    public function test_customer_cannot_view_another_customers_shipment(): void
    {
        $shipmentOfCustomer2 = Shipment::create([
            'shipment_number' => 'CT-2026-9999',
            'customer_id' => $this->customer2->id,
            'carrier_id' => $this->carrier->id,
            'origin_id' => $this->origin->id,
            'destination_id' => $this->destination->id,
            'cargo_type' => 'Confidential Goods',
            'total_weight_kg' => 500,
            'total_volume_cbm' => 2,
            'expected_departure_at' => now(),
            'expected_arrival_at' => now()->addDays(7),
            'current_status' => 'IN_TRANSIT',
        ]);

        // Customer 1 tries to access Customer 2's shipment
        $response = $this->actingAs($this->customer1)->getJson("/api/shipments/{$shipmentOfCustomer2->id}");

        $response->assertStatus(403);
    }
}
