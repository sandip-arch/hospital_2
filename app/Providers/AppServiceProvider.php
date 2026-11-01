<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('system_settings')) {
                \Illuminate\Support\Facades\View::composer('*', function ($view) {
                    static $cachedSettings = null;
                    if ($cachedSettings === null) {
                        $cachedSettings = \App\Models\SystemSetting::all()->pluck('setting_value', 'setting_key')->toArray();
                    }
                    $view->with('hospitalSettings', $cachedSettings);
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('medicine_orders')) {
                \Illuminate\Support\Facades\Schema::create('medicine_orders', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('patient_id')->nullable();
                    $table->unsignedBigInteger('user_id');
                    $table->unsignedInteger('medicine_id');
                    $table->integer('quantity');
                    $table->decimal('unit_price', 10, 2);
                    $table->decimal('total_price', 10, 2);
                    $table->string('delivery_instructions', 255)->nullable();
                    $table->enum('status', ['pending', 'confirmed', 'dispensed', 'cancelled'])->default('pending');
                    $table->text('cancellation_reason')->nullable();
                    $table->unsignedBigInteger('cancelled_by')->nullable();
                    $table->timestamp('cancelled_at')->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silently fallback if running in environments before DB is ready
        }
    }
}
