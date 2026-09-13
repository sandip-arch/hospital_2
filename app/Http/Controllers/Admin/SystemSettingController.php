<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Services\AuditService;

class SystemSettingController extends Controller
{
    public function index()
    {
        // Fetch all settings as key-value array for fast lookup
        $settings = SystemSetting::all()->pluck('setting_value', 'setting_key')->toArray();
        // Also fetch all records to display full table of database settings
        $allSettings = SystemSetting::orderBy('id')->get();

        return view('admin.settings.index', compact('settings', 'allSettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hospital_name' => 'required|string|max:150',
            'hospital_phone' => 'required|string|max:50',
            'hospital_email' => 'required|email|max:100',
            'hospital_address' => 'required|string|max:255',
            'hospital_latitude' => 'nullable|numeric|between:-90,90',
            'hospital_longitude' => 'nullable|numeric|between:-180,180',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
            'currency_symbol' => 'required|string|max:5',
            'appointment_slot_duration_minutes' => 'required|integer|min:5|max:120',
            'emergency_contact_number' => 'required|string|max:50',
            'custom_settings' => 'nullable|array',
        ]);

        // Save primary settings
        foreach ($validated as $key => $value) {
            if ($key !== 'custom_settings') {
                SystemSetting::set($key, $value);
            }
        }

        // Save any custom settings passed
        if ($request->has('custom_settings') && is_array($request->custom_settings)) {
            foreach ($request->custom_settings as $key => $value) {
                SystemSetting::set($key, $value);
            }
        }

        AuditService::log('UPDATE', 'system_settings', null, 'Updated hospital system configuration settings');

        return back()->with('success', 'System settings saved successfully!');
    }

    public function storeSetting(Request $request)
    {
        $validated = $request->validate([
            'setting_key' => 'required|string|max:100|unique:system_settings,setting_key',
            'setting_value' => 'required|string',
            'description' => 'nullable|string|max:255',
        ]);

        SystemSetting::create($validated);

        AuditService::log('CREATE', 'system_settings', null, "Added new system setting: {$validated['setting_key']}");

        return back()->with('success', "Setting '{$validated['setting_key']}' added successfully!");
    }

    public function destroySetting(int $id)
    {
        $setting = SystemSetting::findOrFail($id);
        $key = $setting->setting_key;
        $setting->delete();

        AuditService::log('DELETE', 'system_settings', $id, "Deleted system setting: {$key}");

        return back()->with('success', "Setting '{$key}' removed successfully!");
    }
}
