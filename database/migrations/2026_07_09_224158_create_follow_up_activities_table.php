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
        Schema::create('follow_up_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('prospect_contacts')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('activity_type')->index();
            $table->dateTime('activity_date')->index();
            $table->string('summary', 180);
            $table->text('detail')->nullable();
            $table->dateTime('next_follow_up_at')->nullable()->index();
            $table->string('outcome')->nullable()->index();
            $table->timestamps();

            $table->index(['prospect_id', 'activity_date']);
            $table->index(['user_id', 'next_follow_up_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_up_activities');
    }
};
