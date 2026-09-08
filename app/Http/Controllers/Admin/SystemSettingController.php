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
        $settings = SystemSetting::all()->pluck('setting_value', 'setting_key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'hospital_name' => 'required|string|max:150',
            'hospital_phone' => 'required|string|max:50',
            'hospital_email' => 'required|email|max:100',
            'hospital_address' => 'required|string|max:255',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
            'currency_symbol' => 'required|string|max:5',
            'appointment_slot_duration_minutes' => 'required|integer|min:5|max:120',
            'emergency_contact_number' => 'required|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value);
        }

        AuditService::log('UPDATE', 'system_settings', null, 'Updated hospital system configuration settings');

        return back()->with('success', 'System settings saved successfully!');
    }
}
