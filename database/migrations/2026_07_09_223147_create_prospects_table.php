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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 150);
            $table->string('industry', 100)->nullable();
            $table->string('company_size')->default('medium')->index();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable()->index();
            $table->string('province', 100)->nullable();
            $table->string('website')->nullable();
            $table->string('source')->default('manual')->index();
            $table->string('status')->default('new')->index();
            $table->string('priority')->default('medium')->index();
            $table->unsignedInteger('estimated_vehicle_need')->nullable();
            $table->foreignId('assigned_sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('next_follow_up_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
