<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('rooms') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `rooms` MODIFY COLUMN `room_type` ENUM('Emergency', 'ICU', 'Private', 'Semi-Private', 'General Ward', 'Operating Theater') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rooms')) {
            DB::statement("ALTER TABLE `rooms` MODIFY COLUMN `room_type` ENUM('ICU', 'Private', 'Semi-Private', 'General Ward', 'Operating Theater') NOT NULL");
        }
    }
};
