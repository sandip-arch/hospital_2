<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'partially_paid', 'paid', 'cancelled', 'checking') DEFAULT 'unpaid'");
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('completed', 'failed', 'refunded', 'pending', 'rejected') DEFAULT 'completed'");
        }

        if (!Schema::hasColumn('payments', 'rejection_reason')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('rejection_reason', 255)->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('payments', 'rejection_reason')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('completed', 'failed', 'refunded') DEFAULT 'completed'");
            DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('unpaid', 'partially_paid', 'paid', 'cancelled') DEFAULT 'unpaid'");
        }
    }
};
