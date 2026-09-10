<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure 'driver' role exists in roles table
        if (Schema::hasTable('roles')) {
            Role::firstOrCreate(
                ['name' => 'driver'],
                [
                    'display_name' => 'Ambulance Driver',
                    'description' => 'Emergency medical response and patient transport driver',
                ]
            );
        }

        // 2. Add assigned_doctor_id to ambulances table
        if (Schema::hasTable('ambulances') && !Schema::hasColumn('ambulances', 'assigned_doctor_id')) {
            Schema::table('ambulances', function (Blueprint $table) {
                $table->unsignedInteger('assigned_doctor_id')->nullable()->after('current_driver_id');
                $table->foreign('assigned_doctor_id')->references('id')->on('doctors')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ambulances') && Schema::hasColumn('ambulances', 'assigned_doctor_id')) {
            Schema::table('ambulances', function (Blueprint $table) {
                $table->dropForeign(['assigned_doctor_id']);
                $table->dropColumn('assigned_doctor_id');
            });
        }
    }
};
