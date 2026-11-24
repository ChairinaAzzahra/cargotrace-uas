<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\ShipmentItem;
use App\Models\TrackingEvent;
use App\Models\User;
use App\Services\EtaService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed realistic demonstrative logistics dataset.
     */
    public function run(): void
    {
        // 1. Create Default Role Users (UAS Brief slide 5)
        $admin = User::firstOrCreate(
            ['email' => 'admin@cargotrace.com'],
            [
                'name' => 'Alexander Vance',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+62 811 0001 0001',
                'company_name' => 'CargoTrace Global Logistics Ltd',
            ]
        );

        $operator = User::firstOrCreate(
            ['email' => 'operator@cargotrace.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => 'operator',
                'phone' => '+62 812 3456 7890',
                'company_name' => 'CargoTrace Ops Center Jakarta',
            ]
        );

        $customer1 = User::firstOrCreate(
            ['email' => 'customer@globaltrade.com'],
            [
                'name' => 'Elena Rostova',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+31 20 555 0192',
                'company_name' => 'Global Trade & Retail BV',
            ]
        );

        $customer2 = User::firstOrCreate(
            ['email' => 'customer@asiapacific.com'],
            [
                'name' => 'Hiroshi Tanaka',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+81 3 5555 0144',
                'company_name' => 'Pacific Electronics Corp',
            ]
        );

        // 2. Logistics Carriers (Slide 11)
        $carriersData = [
            ['code' => 'MAEU', 'name' => 'Maersk Line', 'contact_email' => 'booking@maersk.com', 'contact_phone' => '+45 3363 3363'],
            ['code' => 'EMC', 'name' => 'Evergreen Marine', 'contact_email' => 'cargo@evergreen-marine.com', 'contact_phone' => '+886 2 2501 1111'],
            ['code' => 'MSC', 'name' => 'Mediterranean Shipping Company', 'contact_email' => 'operations@msc.com', 'contact_phone' => '+41 22 703 8888'],
            ['code' => 'ONE', 'name' => 'Ocean Network Express', 'contact_email' => 'support@one-line.com', 'contact_phone' => '+65 6899 9000'],
            ['code' => 'DHL', 'name' => 'DHL Global Forwarding', 'contact_email' => 'ocean@dhl.com', 'contact_phone' => '+49 228 1820'],
        ];

        $carrierModels = [];
        foreach ($carriersData as $c) {
            $carrierModels[$c['code']] = Carrier::firstOrCreate(['code' => $c['code']], $c);
        }

        // 3. Strategic Ports & Hubs (Slide 11)
        $locationsData = [
            ['code' => 'IDJKT', 'name' => 'Port of Tanjung Priok', 'city' => 'Jakarta', 'country' => 'Indonesia', 'type' => 'PORT', 'latitude' => -6.1012, 'longitude' => 106.8841],
            ['code' => 'SGSIN', 'name' => 'Port of Singapore', 'city' => 'Singapore', 'country' => 'Singapore', 'type' => 'PORT', 'latitude' => 1.2644, 'longitude' => 103.8228],
            ['code' => 'CNSHA', 'name' => 'Port of Shanghai', 'city' => 'Shanghai', 'country' => 'China', 'type' => 'PORT', 'latitude' => 31.2304, 'longitude' => 121.4737],
            ['code' => 'NLRTM', 'name' => 'Port of Rotterdam', 'city' => 'Rotterdam', 'country' => 'Netherlands', 'type' => 'PORT', 'latitude' => 51.9244, 'longitude' => 4.4777],
            ['code' => 'USLAX', 'name' => 'Port of Los Angeles', 'city' => 'Los Angeles', 'country' => 'United States', 'type' => 'PORT', 'latitude' => 33.7405, 'longitude' => -118.2667],
            ['code' => 'DEHAM', 'name' => 'Port of Hamburg', 'city' => 'Hamburg', 'country' => 'Germany', 'type' => 'PORT', 'latitude' => 53.5453, 'longitude' => 9.9667],
            ['code' => 'AUMEY', 'name' => 'Port Botany', 'city' => 'Sydney', 'country' => 'Australia', 'type' => 'PORT', 'latitude' => -33.9744, 'longitude' => 151.2185],
        ];

        $locationModels = [];
        foreach ($locationsData as $l) {
            $locationModels[$l['code']] = Location::firstOrCreate(['code' => $l['code']], $l);
        }

        $etaService = app(EtaService::class);

        // 4. Create Demonstrative Shipments across all stages

        // Shipment 1: IN_TRANSIT with waypoints & Delayed (+28 hrs, HIGH severity) - Jakarta to Rotterdam (Customer 1)
        $expDep1 = Carbon::now()->subDays(6);
        $expArr1 = Carbon::now()->addDays(8);
        $estArr1 = Carbon::now()->addDays(9)->addHours(4); // 28 hours late!
        $delay1 = $etaService->calculateDelay($expArr1, $estArr1);

        $shipment1 = Shipment::create([
            'shipment_number' => 'CT-2026-0001',
            'customer_id' => $customer1->id,
            'carrier_id' => $carrierModels['MAEU']->id,
            'origin_id' => $locationModels['IDJKT']->id,
            'destination_id' => $locationModels['NLRTM']->id,
            'cargo_type' => 'Consumer Electronics & Solar Inverters',
            'total_weight_kg' => 24800,
            'total_volume_cbm' => 56.4,
            'expected_departure_at' => $expDep1,
            'expected_arrival_at' => $expArr1,
            'estimated_arrival_at' => $estArr1,
            'current_status' => Shipment::STATUS_IN_TRANSIT,
            'delay_status' => $delay1['is_delayed'] ? 'DELAYED' : 'ON_TIME',
            'delay_duration_hours' => $delay1['delay_hours'],
            'delay_severity' => $delay1['severity'],
            'notes' => 'Temperature sensitive microchip components. Group A Scenario (SE Asia -> Europe).',
        ]);

        // Items for Shipment 1
        ShipmentItem::create(['shipment_id' => $shipment1->id, 'item_description' => 'Solar Hybrid Inverter 10kW', 'hs_code' => '8504.40', 'quantity' => 180, 'weight_kg' => 12600, 'volume_cbm' => 28.2]);
        ShipmentItem::create(['shipment_id' => $shipment1->id, 'item_description' => 'Lithium Storage Packs 5kWh', 'hs_code' => '8507.60', 'quantity' => 240, 'weight_kg' => 12200, 'volume_cbm' => 28.2]);

        // Historical Tracking Events for Shipment 1 (Chronological!)
        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'status' => Shipment::STATUS_CREATED,
            'event_type' => 'BOOKING_RECEIVED',
            'location_id' => $locationModels['IDJKT']->id,
            'latitude' => -6.1012,
            'longitude' => 106.8841,
            'event_time' => $expDep1->copy()->subDays(2),
            'description' => 'Booking manifest confirmed and equipment allocated at Tanjung Priok Container Terminal.',
            'is_exception' => false,
            'created_by_user_id' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'status' => Shipment::STATUS_BOOKED,
            'event_type' => 'CUSTOMS_EXPORT_CLEARED',
            'location_id' => $locationModels['IDJKT']->id,
            'latitude' => -6.1012,
            'longitude' => 106.8841,
            'event_time' => $expDep1->copy()->subDays(1),
            'description' => 'Export declaration validated and customs gate-in seal verified.',
            'is_exception' => false,
            'created_by_user_id' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'status' => Shipment::STATUS_PICKED_UP,
            'event_type' => 'VESSEL_LOADED',
            'location_id' => $locationModels['IDJKT']->id,
            'latitude' => -6.1012,
            'longitude' => 106.8841,
            'event_time' => $expDep1->copy(),
            'description' => 'Container loaded aboard vessel Maersk Mc-Kinney Moller (Voyage 2604W).',
            'is_exception' => false,
            'created_by_user_id' => $operator->id,
        ]);

        TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_type' => 'TRANSIT_TRANSSHIPMENT',
            'location_id' => $locationModels['SGSIN']->id,
            'latitude' => 1.2644,
            'longitude' => 103.8228,
            'event_time' => $expDep1->copy()->addDays(2),
            'description' => 'Vessel berthed at Port of Singapore for feeder consolidation.',
            'is_exception' => false,
            'created_by_user_id' => $operator->id,
        ]);

        // Active Exception for Shipment 1 (CUSTOMS_HOLD)
        $excTracking1 = TrackingEvent::create([
            'shipment_id' => $shipment1->id,
            'status' => Shipment::STATUS_IN_TRANSIT,
            'event_type' => 'CUSTOMS_HOLD',
            'location_id' => $locationModels['SGSIN']->id,
            'latitude' => 1.2644,
            'longitude' => 103.8228,
            'event_time' => $expDep1->copy()->addDays(3),
            'description' => 'Maritime customs secondary verification required regarding battery certificates.',
            'is_exception' => true,
            'created_by_user_id' => $operator->id,
        ]);

        ShipmentException::create([
            'shipment_id' => $shipment1->id,
            'tracking_event_id' => $excTracking1->id,
            'exception_type' => 'CUSTOMS_HOLD',
            'severity' => 'HIGH',
            'status' => 'OPEN',
            'root_cause' => 'DG (Dangerous Goods) battery documentation clearance pending transit approval.',
            'detected_at' => $expDep1->copy()->addDays(3),
        ]);

        // Shipment 2: DELIVERED (Terminal State) - Shanghai to Los Angeles (Customer 2)
        $expDep2 = Carbon::now()->subDays(18);
        $expArr2 = Carbon::now()->subDays(3);
        $shipment2 = Shipment::create([
            'shipment_number' => 'CT-2026-0002',
            'customer_id' => $customer2->id,
            'carrier_id' => $carrierModels['EMC']->id,
            'origin_id' => $locationModels['CNSHA']->id,
            'destination_id' => $locationModels['USLAX']->id,
            'cargo_type' => 'Automotive Microprocessors & Sensors',
            'total_weight_kg' => 18400,
            'total_volume_cbm' => 42.0,
            'expected_departure_at' => $expDep2,
            'expected_arrival_at' => $expArr2,
            'estimated_arrival_at' => $expArr2,
            'current_status' => Shipment::STATUS_DELIVERED,
            'delay_status' => 'ON_TIME',
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'notes' => 'Completed cross-pacific transit on schedule.',
        ]);

        TrackingEvent::create(['shipment_id' => $shipment2->id, 'status' => 'CREATED', 'event_type' => 'ORDER_PLACED', 'location_id' => $locationModels['CNSHA']->id, 'latitude' => 31.23, 'longitude' => 121.47, 'event_time' => $expDep2->copy()->subDays(2), 'description' => 'Order created in port system.', 'created_by_user_id' => $operator->id]);
        TrackingEvent::create(['shipment_id' => $shipment2->id, 'status' => 'BOOKED', 'event_type' => 'SPACE_ALLOCATED', 'location_id' => $locationModels['CNSHA']->id, 'latitude' => 31.23, 'longitude' => 121.47, 'event_time' => $expDep2->copy()->subDays(1), 'description' => 'Space secured on vessel Ever Given.', 'created_by_user_id' => $operator->id]);
        TrackingEvent::create(['shipment_id' => $shipment2->id, 'status' => 'PICKED_UP', 'event_type' => 'LOADED_ON_VESSEL', 'location_id' => $locationModels['CNSHA']->id, 'latitude' => 31.23, 'longitude' => 121.47, 'event_time' => $expDep2, 'description' => 'Vessel departed Shanghai deep water port.', 'created_by_user_id' => $operator->id]);
        TrackingEvent::create(['shipment_id' => $shipment2->id, 'status' => 'IN_TRANSIT', 'event_type' => 'PACIFIC_CROSSING', 'latitude' => 32.5, 'longitude' => -150.0, 'event_time' => $expDep2->copy()->addDays(7), 'description' => 'Mid-Pacific voyage waypoint, sea conditions optimal.', 'created_by_user_id' => $operator->id]);
        TrackingEvent::create(['shipment_id' => $shipment2->id, 'status' => 'DELIVERED', 'event_type' => 'FINAL_DELIVERY_RECEIPT', 'location_id' => $locationModels['USLAX']->id, 'latitude' => 33.7405, 'longitude' => -118.2667, 'event_time' => $expArr2, 'description' => 'Cargo discharged and received by consignee at Los Angeles Logistics Center.', 'created_by_user_id' => $operator->id]);

        // Shipment 3: BOOKED - Jakarta to Sydney (Customer 1)
        Shipment::create([
            'shipment_number' => 'CT-2026-0003',
            'customer_id' => $customer1->id,
            'carrier_id' => $carrierModels['ONE']->id,
            'origin_id' => $locationModels['IDJKT']->id,
            'destination_id' => $locationModels['AUMEY']->id,
            'cargo_type' => 'Specialty Coffee Beans & Cocoa',
            'total_weight_kg' => 21000,
            'total_volume_cbm' => 45.0,
            'expected_departure_at' => Carbon::now()->addDays(2),
            'expected_arrival_at' => Carbon::now()->addDays(14),
            'current_status' => Shipment::STATUS_BOOKED,
            'delay_status' => 'ON_TIME',
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'notes' => 'Pre-cooling Reefer container standard 18C.',
        ]);

        // Shipment 4: CREATED - Shanghai to Hamburg (Customer 2)
        Shipment::create([
            'shipment_number' => 'CT-2026-0004',
            'customer_id' => $customer2->id,
            'carrier_id' => $carrierModels['MSC']->id,
            'origin_id' => $locationModels['CNSHA']->id,
            'destination_id' => $locationModels['DEHAM']->id,
            'cargo_type' => 'Precision Medical Optics',
            'total_weight_kg' => 6400,
            'total_volume_cbm' => 18.0,
            'expected_departure_at' => Carbon::now()->addDays(3),
            'expected_arrival_at' => Carbon::now()->addDays(28),
            'current_status' => Shipment::STATUS_CREATED,
            'delay_status' => 'ON_TIME',
            'delay_duration_hours' => 0,
            'delay_severity' => 'NONE',
            'notes' => 'Awaiting freight forwarder final booking confirmation.',
        ]);

        // 5. Initial Notifications
        Notification::create([
            'user_id' => $customer1->id,
            'shipment_id' => $shipment1->id,
            'title' => 'Customs Verification: CT-2026-0001',
            'message' => 'Operational delay flagged (+28h) at Singapore transit hub due to battery documentation check.',
            'type' => 'EXCEPTION_ALERT',
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $customer2->id,
            'shipment_id' => $shipment2->id,
            'title' => 'Shipment Delivered: CT-2026-0002',
            'message' => 'Cargo successfully discharged at Port of Los Angeles. Proof of delivery uploaded.',
            'type' => 'STATUS_UPDATE',
            'is_read' => true,
            'read_at' => now(),
        ]);

        // 6. Audit Trail Sample
        AuditLog::create([
            'user_id' => $operator->id,
            'action' => 'RAISE_EXCEPTION',
            'entity_type' => 'ShipmentException',
            'entity_id' => 1,
            'old_values' => null,
            'new_values' => ['exception_type' => 'CUSTOMS_HOLD', 'severity' => 'HIGH', 'shipment_id' => $shipment1->id],
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
        ]);
    }
}
