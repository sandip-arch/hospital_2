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
        // 3.1 patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('set null');
            $table->string('patient_code', 30)->unique(); // UPI
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->date('dob');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->nullable();
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->text('medical_history')->nullable();
            $table->timestamps();
        });

        // 3.2 emergency_contacts
        Schema::create('emergency_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('contact_name', 100);
            $table->string('relationship', 50);
            $table->string('phone', 20);
            $table->string('alt_phone', 20)->nullable();
            $table->timestamps();
        });

        // 3.3 patient_documents
        Schema::create('patient_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('document_type', 50); // Insurance, ID Card, X-Ray, Lab Report
            $table->string('file_path', 255);
            $table->string('file_name', 255)->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_documents');
        Schema::dropIfExists('emergency_contacts');
        Schema::dropIfExists('patients');
    }
};
