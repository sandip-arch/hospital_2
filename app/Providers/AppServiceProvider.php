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
        } catch (\Throwable $e) {
            // Silently fallback if running in environments before DB is ready
        }
    }
}
