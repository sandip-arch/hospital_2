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
        if (!Schema::hasTable('ambulance_complaints')) {
            Schema::create('ambulance_complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ambulance_id')->constrained('ambulances')->cascadeOnDelete();
                $table->foreignId('driver_id')->constrained('ambulance_drivers')->cascadeOnDelete();
                $table->string('title', 150);
                $table->enum('category', [
                    'mechanical',
                    'electrical',
                    'medical_equipment',
                    'tyres_brakes',
                    'air_conditioning',
                    'fuel_oil',
                    'cleanliness',
                    'other',
                ])->default('mechanical');
                $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->text('description');
                $table->unsignedInteger('odometer_reading')->nullable();
                $table->enum('status', [
                    'submitted',
                    'under_investigation',
                    'in_maintenance',
                    'resolved',
                    'closed',
                ])->default('submitted');
                $table->text('admin_notes')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambulance_complaints');
    }
};
