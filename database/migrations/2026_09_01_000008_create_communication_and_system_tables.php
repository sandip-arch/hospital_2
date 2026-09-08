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
        // 8.1 notifications
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title', 150);
            $table->text('message');
            $table->enum('type', ['appointment', 'billing', 'lab_result', 'system']);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 8.2 messages
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('message_body');
            $table->timestamp('sent_at')->useCurrent();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 9.1 audit_logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action', 100); // CREATE, UPDATE, DELETE, LOGIN, LOGOUT
            $table->string('table_name', 50)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('details')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 9.2 system_settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('setting_key', 100)->unique();
            $table->text('setting_value');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('notifications');
    }
};
