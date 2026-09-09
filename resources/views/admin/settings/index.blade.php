@extends('layouts.app')

@section('title', 'System Settings & Parameters')
@section('header_title', 'Master System Settings')
@section('header_subtitle', 'Global hospital branding, contact parameters, currency, tax rates, and clinical defaults')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Success & Error Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- 1. Primary Hospital Configuration Form -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Hospital Branding & Contact -->
            <div>
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="font-heading text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-hospital text-purple-600"></i> Healthcare Facility Branding & Info
                    </h4>
                    <span class="text-[11px] font-mono text-slate-400">Live Database Values</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Hospital / Medical Center Name *</label>
                        <input type="text" name="hospital_name" value="{{ old('hospital_name', $settings['hospital_name'] ?? '') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Primary Support / Contact Email *</label>
                        <input type="email" name="hospital_email" value="{{ old('hospital_email', $settings['hospital_email'] ?? '') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Hospital Phone / Reception *</label>
                        <input type="text" name="hospital_phone" value="{{ old('hospital_phone', $settings['hospital_phone'] ?? '') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">24/7 Emergency Contact Hotline *</label>
                        <input type="text" name="emergency_contact_number" value="{{ old('emergency_contact_number', $settings['emergency_contact_number'] ?? '') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Physical Facility Address *</label>
                    <input type="text" name="hospital_address" value="{{ old('hospital_address', $settings['hospital_address'] ?? '') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
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
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Default Slot Duration (Mins) *</label>
                        <input type="number" name="appointment_slot_duration_minutes" value="{{ old('appointment_slot_duration_minutes', $settings['appointment_slot_duration_minutes'] ?? '30') }}" min="5" max="120" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
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

    <!-- 2. Live Database Settings Master Table & Custom Parameters -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- All DB Settings Table (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div>
                    <h4 class="font-heading text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-database text-cyan-600"></i> All Database System Settings
                    </h4>
                    <p class="text-[11px] text-slate-500">Live records currently fetched from MySQL `system_settings` table ({{ $allSettings->count() }} parameters)</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Key</th>
                            <th class="py-3 px-4">Value (Stored in DB)</th>
                            <th class="py-3 px-4">Last Updated</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($allSettings as $item)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                {{ $item->setting_key }}
                            </td>
                            <td class="py-3 px-4 max-w-xs truncate text-slate-900 font-semibold" title="{{ $item->setting_value }}">
                                {{ $item->setting_value }}
                            </td>
                            <td class="py-3 px-4 text-[11px] text-slate-400 font-mono whitespace-nowrap">
                                {{ $item->updated_at ? $item->updated_at->diffForHumans() : 'N/A' }}
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap">
                                @php
                                    $coreKeys = ['hospital_name', 'hospital_phone', 'hospital_email', 'hospital_address', 'tax_rate_percent', 'currency_symbol', 'appointment_slot_duration_minutes', 'emergency_contact_number'];
                                @endphp
                                @if(!in_array($item->setting_key, $coreKeys))
                                <form action="{{ route('admin.settings.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete setting {{ $item->setting_key }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 rounded-lg hover:bg-rose-50 transition" title="Delete setting">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-[10px] font-bold text-slate-400 uppercase bg-slate-100 px-2 py-0.5 rounded-full">Core</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-400">No settings found in database.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Custom Setting (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft space-y-4">
            <div>
                <h4 class="font-heading text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-plus-circle text-purple-600"></i> Add Custom Parameter
                </h4>
                <p class="text-[11px] text-slate-500 mt-1">Insert any new custom key-value parameter directly into `system_settings` table.</p>
            </div>

            <form action="{{ route('admin.settings.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Setting Key *</label>
                    <input type="text" name="setting_key" placeholder="e.g. ambulance_dispatch_hotline" required
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Setting Value *</label>
                    <textarea name="setting_value" rows="2" placeholder="Setting value..." required
                              class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                    <input type="text" name="description" placeholder="Brief note..."
                           class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <button type="submit" class="w-full py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add Setting to DB
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
