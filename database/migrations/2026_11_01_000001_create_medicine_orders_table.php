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
        if (!Schema::hasTable('medicine_orders')) {
            Schema::create('medicine_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_id')->nullable()->constrained('patients')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->unsignedInteger('medicine_id');
                $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
                $table->integer('quantity');
                $table->decimal('unit_price', 10, 2);
                $table->decimal('total_price', 10, 2);
                $table->string('delivery_instructions', 255)->nullable();
                $table->enum('status', ['pending', 'confirmed', 'dispensed', 'cancelled'])->default('pending');
                $table->text('cancellation_reason')->nullable();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicine_orders');
    }
};

