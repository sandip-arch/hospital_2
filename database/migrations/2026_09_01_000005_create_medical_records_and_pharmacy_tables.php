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
        // 5.1 medical_records
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->unsignedInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->dateTime('visit_date');
            $table->text('diagnosis');
            $table->text('symptoms')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 5.2 medical_record_details (Vital Signs & Observations)
        Schema::create('medical_record_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->onDelete('cascade');
            $table->string('vital_sign_name', 50); // e.g. Blood Pressure, Heart Rate, SpO2, Temperature, Respiratory Rate, Weight
            $table->string('vital_sign_value', 50);
            $table->timestamps();
        });

        // 5.4 medicines
        Schema::create('medicines', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('generic_name', 100)->nullable();
            $table->string('category', 50); // Antibiotic, Analgesic, Antipyretic, Antihypertensive, etc.
            $table->decimal('unit_price', 10, 2);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(10);
            $table->date('expiry_date')->nullable();
            $table->timestamps();
        });

        // 5.3 prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->unsignedInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->date('prescribed_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'dispensed', 'cancelled'])->default('active');
            $table->timestamps();
        });

        // 5.5 prescription_items
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->onDelete('cascade');
            $table->unsignedInteger('medicine_id');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('restrict');
            $table->string('dosage', 50); // e.g. 500mg
            $table->string('frequency', 50); // e.g. Twice daily after meals
            $table->integer('duration_days')->default(1);
            $table->integer('quantity_prescribed')->default(1);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // 5.6 lab_tests
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('test_name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->decimal('cost', 10, 2);
            $table->timestamps();
        });

        // 5.7 lab_reports
        Schema::create('lab_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->unsignedInteger('lab_test_id');
            $table->foreign('lab_test_id')->references('id')->on('lab_tests')->onDelete('restrict');
            $table->unsignedInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status', ['requested', 'in_progress', 'completed', 'cancelled'])->default('requested');
            $table->text('result_summary')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->dateTime('report_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_reports');
        Schema::dropIfExists('lab_tests');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('medical_record_details');
        Schema::dropIfExists('medical_records');
    }
};
