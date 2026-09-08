@extends('layouts.app')

@section('title', 'System Settings & Parameters')
@section('header_title', 'Master System Settings')
@section('header_subtitle', 'Global hospital branding, contact parameters, currency, tax rates, and clinical defaults')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Hospital Branding & Contact -->
            <div>
                <h4 class="font-heading text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-hospital text-purple-600"></i> Healthcare Facility Branding & Info
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Hospital / Medical Center Name *</label>
                        <input type="text" name="hospital_name" value="{{ old('hospital_name', $settings['hospital_name'] ?? 'Apex Horizon International Medical Center') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Support / Contact Email *</label>
                        <input type="email" name="hospital_email" value="{{ old('hospital_email', $settings['hospital_email'] ?? 'contact@apexmedical.test') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Hospital Phone / Reception *</label>
                        <input type="text" name="hospital_phone" value="{{ old('hospital_phone', $settings['hospital_phone'] ?? '+1 (800) 555-APEX / +1 (555) 019-9000') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">24/7 Emergency Contact Hotline *</label>
                        <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $settings['emergency_contact_number'] ?? '911 / +1 (555) 911-0000') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Physical Facility Address *</label>
                    <input type="text" name="hospital_address" value="{{ old('hospital_address', $settings['hospital_address'] ?? '500 Health Sciences Blvd, Medical District, Boston MA 02115') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Financial & Clinical Defaults -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="font-heading text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-coins text-purple-600"></i> Financial & Clinical Parameters
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">System Currency Symbol *</label>
                        <select name="currency_symbol" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="$" {{ ($settings['currency_symbol'] ?? '$') == '$' ? 'selected' : '' }}>USD ($ - United States Dollar)</option>
                            <option value="€" {{ ($settings['currency_symbol'] ?? '') == '€' ? 'selected' : '' }}>EUR (€ - Euro)</option>
                            <option value="£" {{ ($settings['currency_symbol'] ?? '') == '£' ? 'selected' : '' }}>GBP (£ - British Pound)</option>
                            <option value="₹" {{ ($settings['currency_symbol'] ?? '') == '₹' ? 'selected' : '' }}>INR (₹ - Indian Rupee)</option>
                            <option value="C$" {{ ($settings['currency_symbol'] ?? '') == 'C$' ? 'selected' : '' }}>CAD (C$ - Canadian Dollar)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Standard Tax Rate (%) *</label>
                        <input type="number" step="0.1" name="tax_rate_percent" value="{{ old('tax_rate_percent', $settings['tax_rate_percent'] ?? '5.0') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Default Slot Duration (Mins) *</label>
                        <input type="number" name="appointment_slot_duration_minutes" value="{{ old('appointment_slot_duration_minutes', $settings['appointment_slot_duration_minutes'] ?? '30') }}" min="5" max="120" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save System Settings
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
