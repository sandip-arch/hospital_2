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
        // 2.1 departments
        Schema::create('departments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable()->default('hospital');
            $table->timestamps();
        });

        // 2.2 doctors
        Schema::create('doctors', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->string('specialization', 100);
            $table->string('license_number', 50)->unique();
            $table->decimal('consultation_fee', 10, 2)->default(0.00);
            $table->string('phone', 20);
            $table->text('bio')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        // 2.3 staff
        Schema::create('staff', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->string('job_title', 100); // e.g. Receptionist, Nurse, Pharmacist, Lab Technician, Billing Clerk
            $table->string('phone', 20);
            $table->date('hire_date')->nullable();
            $table->timestamps();
        });

        // 2.4 managers
        Schema::create('managers', function (Blueprint $table) {
            $table->increments('id');
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->string('title', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managers');
        Schema::dropIfExists('staff');
        Schema::dropIfExists('doctors');
        Schema::dropIfExists('departments');
    }
};
