<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'description',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('setting_key', $key)->first();
        if ($setting && $setting->setting_value !== null && $setting->setting_value !== '') {
            return $setting->setting_value;
        }
        return $default;
    }

    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'description' => $description]
        );
    }
}
