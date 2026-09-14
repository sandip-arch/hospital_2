@extends('layouts.app')

@section('title', 'System Settings & Parameters')
@section('header_title', 'Master System Settings')
@section('header_subtitle', 'Global hospital branding, contact parameters, dynamic geographic coordinates, and clinical defaults')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
    .hospital-custom-marker {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: #dc2626;
        border: 3px solid #ffffff;
        border-radius: 50%;
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(220, 38, 38, 0.45);
        font-size: 16px;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 1rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        padding: 4px;
    }
</style>
@endpush

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
                    <div class="flex gap-2">
                        <input type="text" id="hospital_address_input" name="hospital_address" value="{{ old('hospital_address', $settings['hospital_address'] ?? '') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 transition">
                        <button type="button" onclick="geocodeAddressField()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl whitespace-nowrap transition flex items-center gap-1.5" title="Pin address on map">
                            <i class="fa-solid fa-map-pin text-purple-600"></i>
                            <span class="hidden sm:inline">Pin Address</span>
                        </button>
                    </div>
                </div>

                <!-- 2. Dynamic Hospital Geographic Location & Interactive Map Pinning -->
                <div class="mt-6 p-5 rounded-3xl bg-slate-50/80 border border-slate-200/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-800 text-[10px] font-extrabold uppercase tracking-wider mb-1">
                                <i class="fa-solid fa-satellite-dish"></i> GPS Telemetry & Ambulance Dispatch Anchor
                            </div>
                            <h5 class="text-sm font-black text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-location-dot text-rose-600"></i> Dynamic Facility Coordinates & Map Pinning
                            </h5>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                Set hospital geographic anchor dynamically using your device location or click/drag the pin anywhere on the map.
                            </p>
                        </div>

                        <!-- Device Location Button -->
                        <div class="flex items-center gap-2">
                            <button type="button" id="btn-device-location" onclick="detectDeviceLocation()"
                                    class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white text-xs font-extrabold rounded-2xl shadow-md shadow-cyan-600/20 flex items-center gap-2 transition active:scale-95">
                                <i class="fa-solid fa-crosshairs text-sm animate-pulse"></i>
                                <span>Assign By My Device Location</span>
                            </button>
                        </div>
                    </div>

                    <!-- Coordinates Readout & Manual Override -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Latitude (°N) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" step="0.0000001" id="hospital_latitude" name="hospital_latitude"
                                       value="{{ old('hospital_latitude', $settings['hospital_latitude'] ?? '42.3375000') }}" required
                                       class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <i class="fa-solid fa-arrows-up-down absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">
                                Longitude (°E) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" step="0.0000001" id="hospital_longitude" name="hospital_longitude"
                                       value="{{ old('hospital_longitude', $settings['hospital_longitude'] ?? '-71.1065000') }}" required
                                       class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <i class="fa-solid fa-arrows-left-right absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                            </div>
                        </div>

                        <div class="sm:col-span-2 lg:col-span-1 flex flex-col justify-end">
                            <div id="location-status-badge" class="px-3 py-2 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-semibold flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                <span id="location-status-text">Pin is synchronized with live inputs</span>
                            </div>
                        </div>
                    </div>

                    <!-- Map Search and Interactive Canvas -->
                    <div class="space-y-2">
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                <input type="text" id="map-search-input" placeholder="Search a city, neighborhood, or landmark to pin..."
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap(); }"
                                       class="w-full pl-8 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-purple-500">
                            </div>
                            <button type="button" onclick="searchLocationOnMap()" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <span class="hidden sm:inline">Search</span>
                            </button>
                        </div>

                        <!-- Leaflet Map Container -->
                        <div class="relative rounded-2xl border border-slate-200 overflow-hidden shadow-sm bg-slate-100">
                            <div id="hospital-leaflet-map" style="height: 340px; width: 100%; z-index: 1;"></div>
                            <div class="absolute bottom-2 left-2 z-[1000] bg-white/95 backdrop-blur-sm border border-slate-200 px-3 py-1 rounded-xl text-[10px] font-mono font-bold text-slate-700 shadow-sm flex items-center gap-2">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span id="coordinates-readout">Click map or drag red marker to reposition</span>
                            </div>
                        </div>
                    </div>

                    <!-- Reverse Geocode Suggestion Alert -->
                    <div id="reverse-geocode-alert" class="hidden p-3 rounded-2xl bg-cyan-50 border border-cyan-200 text-cyan-900 text-xs flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-wand-magic-sparkles text-cyan-600"></i>
                            <span>Detected Address: <strong id="reverse-geocode-address"></strong></span>
                        </div>
                        <button type="button" onclick="applyDetectedAddress()" class="px-3 py-1 bg-cyan-600 hover:bg-cyan-500 text-white text-[11px] font-bold rounded-lg shadow-xs transition">
                            Apply to Facility Address
                        </button>
                    </div>

                    <!-- 3NF Database Notice -->
                    <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono">
                        <i class="fa-solid fa-database text-purple-500"></i>
                        <span>3NF Architecture: Coordinates persist atomically in `system_settings` (`hospital_latitude`, `hospital_longitude`) eliminating transitive functional dependencies.</span>
                    </div>
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let hospitalMap = null;
    let hospitalMarker = null;
    let detectedAddressText = '';

    function getHospitalIcon() {
        return L.divIcon({
            className: 'hospital-marker-wrapper',
            html: '<div class="hospital-custom-marker"><i class="fa-solid fa-hospital text-white"></i></div>',
            iconSize: [38, 38],
            iconAnchor: [19, 19],
            popupAnchor: [0, -20]
        });
    }

    function updateCoordinates(lat, lng, fetchAddress = false) {
        lat = parseFloat(lat).toFixed(7);
        lng = parseFloat(lng).toFixed(7);

        const latInput = document.getElementById('hospital_latitude');
        const lngInput = document.getElementById('hospital_longitude');
        const readout = document.getElementById('coordinates-readout');

        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (readout) readout.textContent = `Lat: ${lat}, Lng: ${lng}`;

        if (hospitalMarker) {
            hospitalMarker.setLatLng([lat, lng]);
            hospitalMarker.bindPopup(`
                <div class="text-xs p-1 space-y-1">
                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                        <i class="fa-solid fa-hospital text-rose-600"></i> Hospital Location Anchor
                    </div>
                    <div class="font-mono text-[10px] text-slate-500">${lat}, ${lng}</div>
                </div>
            `).openPopup();
        }

        if (fetchAddress) {
            reverseGeocode(lat, lng);
        }
    }

    function reverseGeocode(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
        
        fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    detectedAddressText = data.display_name;
                    const alertEl = document.getElementById('reverse-geocode-alert');
                    const textEl = document.getElementById('reverse-geocode-address');
                    if (alertEl && textEl) {
                        textEl.textContent = detectedAddressText;
                        alertEl.classList.remove('hidden');
                    }
                }
            })
            .catch(() => {
                // Silently fallback if offline / rate limited
            });
    }

    function applyDetectedAddress() {
        if (!detectedAddressText) return;
        const addrInput = document.getElementById('hospital_address_input');
        if (addrInput) {
            addrInput.value = detectedAddressText;
            addrInput.classList.add('ring-2', 'ring-emerald-500');
            setTimeout(() => addrInput.classList.remove('ring-2', 'ring-emerald-500'), 2000);
        }
        document.getElementById('reverse-geocode-alert')?.classList.add('hidden');
    }

    function detectDeviceLocation() {
        const btn = document.getElementById('btn-device-location');
        const statusBadge = document.getElementById('location-status-badge');
        const statusText = document.getElementById('location-status-text');

        if (!navigator.geolocation) {
            alert('HTML5 Geolocation is not supported by your current browser.');
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-sm"></i><span>Acquiring Device GPS...</span>`;
        btn.disabled = true;

        if (statusBadge) {
            statusBadge.className = 'px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold flex items-center gap-2';
            statusText.textContent = 'Contacting device GPS satellites...';
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = Math.round(position.coords.accuracy || 10);

                updateCoordinates(lat, lng, true);

                if (hospitalMap) {
                    hospitalMap.flyTo([lat, lng], 16, { duration: 1.2 });
                }

                btn.innerHTML = `<i class="fa-solid fa-circle-check text-sm text-emerald-300"></i><span>GPS Location Locked!</span>`;
                btn.disabled = false;

                if (statusBadge) {
                    statusBadge.className = 'px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold flex items-center gap-2';
                    statusText.textContent = `Locked to device GPS (Accuracy: ±${accuracy}m)`;
                }

                setTimeout(() => {
                    btn.innerHTML = originalBtnHtml;
                }, 3500);
            },
            function(error) {
                btn.innerHTML = originalBtnHtml;
                btn.disabled = false;

                let msg = 'Unable to retrieve your device location.';
                if (error.code === error.PERMISSION_DENIED) {
                    msg = 'Location permission was denied. Please allow location access or click the map directly to pin.';
                } else if (error.code === error.POSITION_UNAVAILABLE) {
                    msg = 'Device location is currently unavailable.';
                } else if (error.code === error.TIMEOUT) {
                    msg = 'Location request timed out. Please try again or pin on the map.';
                }

                if (statusBadge) {
                    statusBadge.className = 'px-3 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-[11px] font-semibold flex items-center gap-2';
                    statusText.textContent = msg;
                }
                alert(msg);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function searchLocationOnMap() {
        const input = document.getElementById('map-search-input');
        const query = input?.value?.trim();
        if (!query) return;

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`;

        fetch(url, { headers: { 'Accept-Language': 'en' } })
            .then(res => res.json())
            .then(data => {
                if (data && data.length > 0) {
                    const lat = parseFloat(data[0].lat);
                    const lng = parseFloat(data[0].lon);
                    updateCoordinates(lat, lng, false);
                    if (hospitalMap) {
                        hospitalMap.flyTo([lat, lng], 15, { duration: 1.2 });
                    }
                    detectedAddressText = data[0].display_name;
                    const alertEl = document.getElementById('reverse-geocode-alert');
                    const textEl = document.getElementById('reverse-geocode-address');
                    if (alertEl && textEl) {
                        textEl.textContent = detectedAddressText;
                        alertEl.classList.remove('hidden');
                    }
                } else {
                    alert('No location found matching "' + query + '". Try a city or landmark name.');
                }
            })
            .catch(() => {
                alert('Geocoding service unavailable. You can click on the map to pin manually.');
            });
    }

    function geocodeAddressField() {
        const addr = document.getElementById('hospital_address_input')?.value?.trim();
        if (!addr) {
            alert('Please enter a physical facility address first.');
            return;
        }
        document.getElementById('map-search-input').value = addr;
        searchLocationOnMap();
    }

    document.addEventListener('DOMContentLoaded', function() {
        const mapEl = document.getElementById('hospital-leaflet-map');
        if (!mapEl) return;

        const latInput = document.getElementById('hospital_latitude');
        const lngInput = document.getElementById('hospital_longitude');

        let initialLat = parseFloat(latInput?.value) || 42.3375000;
        let initialLng = parseFloat(lngInput?.value) || -71.1065000;

        hospitalMap = L.map('hospital-leaflet-map', {
            zoomControl: true
        }).setView([initialLat, initialLng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(hospitalMap);

        hospitalMarker = L.marker([initialLat, initialLng], {
            icon: getHospitalIcon(),
            draggable: true
        }).addTo(hospitalMap);

        updateCoordinates(initialLat, initialLng, false);

        // Marker drag handler
        hospitalMarker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            updateCoordinates(pos.lat, pos.lng, true);
        });

        // Map click handler to pin location
        hospitalMap.on('click', function(e) {
            updateCoordinates(e.latlng.lat, e.latlng.lng, true);
        });

        // Manual coordinate input changes
        [latInput, lngInput].forEach(inp => {
            inp?.addEventListener('input', function() {
                const lat = parseFloat(latInput.value);
                const lng = parseFloat(lngInput.value);
                if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    if (hospitalMarker) hospitalMarker.setLatLng([lat, lng]);
                    if (hospitalMap) hospitalMap.panTo([lat, lng]);
                    document.getElementById('coordinates-readout').textContent = `Lat: ${lat.toFixed(7)}, Lng: ${lng.toFixed(7)}`;
                }
            });
        });

        // Invalidate map size after DOM settles (prevents gray tiles)
        setTimeout(() => {
            hospitalMap.invalidateSize();
        }, 300);
    });
</script>
@endpush
