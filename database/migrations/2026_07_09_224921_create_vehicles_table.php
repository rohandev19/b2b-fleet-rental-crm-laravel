<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('vehicle_type')->index();
            $table->string('transmission')->index();
            $table->string('fuel_type')->index();
            $table->unsignedSmallInteger('seat_capacity');
            $table->decimal('base_monthly_price', 15, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['brand', 'model', 'vehicle_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
