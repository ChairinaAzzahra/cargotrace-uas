<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // e.g. IDJKT, NLRTM
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->enum('type', ['PORT', 'AIRPORT', 'WAREHOUSE', 'CUSTOMS_CHECKPOINT'])->default('PORT');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
