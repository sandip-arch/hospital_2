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
        // 6.1 rooms
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('room_number', 20)->unique();
            $table->enum('room_type', ['ICU', 'Private', 'Semi-Private', 'General Ward', 'Operating Theater']);
            $table->unsignedInteger('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->decimal('daily_rate', 10, 2);
            $table->enum('status', ['available', 'full', 'maintenance'])->default('available');
            $table->timestamps();
        });

        // 6.2 beds
        Schema::create('beds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');
            $table->string('bed_number', 20);
            $table->enum('status', ['available', 'occupied', 'cleaning', 'maintenance'])->default('available');
            $table->timestamps();
        });

        // 6.3 admissions
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->unsignedInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('restrict');
            $table->unsignedInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->dateTime('admission_date');
            $table->dateTime('discharge_date')->nullable();
            $table->text('admission_reason')->nullable();
            $table->text('discharge_notes')->nullable();
            $table->enum('status', ['admitted', 'discharged', 'transferred'])->default('admitted');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('rooms');
    }
};
