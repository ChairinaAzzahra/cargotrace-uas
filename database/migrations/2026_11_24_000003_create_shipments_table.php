<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 30)->unique()->index();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('carriers');
            $table->foreignId('origin_id')->constrained('locations');
            $table->foreignId('destination_id')->constrained('locations');
            $table->string('cargo_type');
            $table->decimal('total_weight_kg', 10, 2)->default(0);
            $table->decimal('total_volume_cbm', 10, 2)->default(0);
            $table->dateTime('expected_departure_at');
            $table->dateTime('expected_arrival_at');
            $table->dateTime('estimated_arrival_at')->nullable();
            $table->enum('current_status', ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'])->default('CREATED')->index();
            $table->enum('delay_status', ['ON_TIME', 'DELAYED'])->default('ON_TIME')->index();
            $table->integer('delay_duration_hours')->default(0);
            $table->enum('delay_severity', ['NONE', 'LOW', 'MEDIUM', 'HIGH'])->default('NONE')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
