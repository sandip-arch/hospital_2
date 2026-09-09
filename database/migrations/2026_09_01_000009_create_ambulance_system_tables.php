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
        if (!Schema::hasTable('ambulance_drivers')) {
            Schema::create('ambulance_drivers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('license_number', 50);
                $table->string('contact_number', 20);
                $table->enum('status', ['on_duty', 'off_duty'])->default('off_duty');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('ambulances')) {
            Schema::create('ambulances', function (Blueprint $table) {
                $table->id();
                $table->string('vehicle_number', 20)->unique();
                $table->string('model', 100);
                $table->enum('type', ['Basic', 'Advanced_Life_Support', 'Patient_Transport']);
                $table->unsignedBigInteger('current_driver_id')->nullable();
                $table->enum('status', ['available', 'dispatched', 'in_transit', 'maintenance'])->default('available');
                $table->decimal('current_latitude', 10, 7)->nullable();
                $table->decimal('current_longitude', 10, 7)->nullable();
                $table->timestamp('last_location_update')->nullable();
                $table->timestamps();

                $table->foreign('current_driver_id')->references('id')->on('ambulance_drivers')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('ambulance_bookings')) {
            Schema::create('ambulance_bookings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('patient_id')->nullable();
                $table->unsignedBigInteger('ambulance_id');
                $table->unsignedBigInteger('driver_id')->nullable();
                $table->string('contact_phone', 20);
                $table->text('pickup_address');
                $table->decimal('pickup_latitude', 10, 7);
                $table->decimal('pickup_longitude', 10, 7);
                $table->unsignedInteger('destination_hospital_department_id')->nullable();
                $table->enum('booking_status', ['requested', 'assigned', 'en_route', 'arrived', 'completed', 'cancelled'])->default('requested');
                $table->timestamp('booking_time')->useCurrent();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('ambulance_id')->references('id')->on('ambulances')->cascadeOnDelete();
                $table->foreign('driver_id')->references('id')->on('ambulance_drivers')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('ambulance_location_logs')) {
            Schema::create('ambulance_location_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ambulance_id');
                $table->unsignedBigInteger('booking_id')->nullable();
                $table->decimal('latitude', 10, 7);
                $table->decimal('longitude', 10, 7);
                $table->timestamp('recorded_at')->useCurrent();

                $table->foreign('ambulance_id')->references('id')->on('ambulances')->cascadeOnDelete();
                $table->foreign('booking_id')->references('id')->on('ambulance_bookings')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ambulance_location_logs');
        Schema::dropIfExists('ambulance_bookings');
        Schema::dropIfExists('ambulances');
        Schema::dropIfExists('ambulance_drivers');
    }
};
