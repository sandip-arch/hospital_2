@extends(Auth::check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Book Emergency Ambulance Dispatch')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto space-y-6">

    <!-- Back Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('ambulance.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-cyan-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Ambulance Radar Map</span>
        </a>
        <div class="flex items-center gap-1.5 text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200">
            <i class="fa-solid fa-phone-volume animate-pulse"></i>
            <span>Direct Emergency Hotline: 911 / +1 (555) 911-0000</span>
        </div>
    </div>

    <!-- Booking Card -->
    <div class="bg-white rounded-3xl shadow-soft border border-slate-200 overflow-hidden">
        
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-rose-950 to-slate-900 px-6 sm:px-8 py-6 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-[11px] font-bold uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                    Rapid Dispatch Service
                </div>
                <h1 class="text-xl sm:text-2xl font-heading font-extrabold tracking-tight">
                    Book Ambulance Dispatch
                </h1>
                <p class="text-xs text-slate-300 mt-1">
                    Enter pickup coordinates or select on map. Nearest medical team will be dispatched immediately.
                </p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-600/30 border border-rose-500/40 flex items-center justify-center text-rose-400 text-2xl shadow-inner shrink-0">
                <i class="fa-solid fa-truck-medical"></i>
            </div>
        </div>

        @if(session('error'))
        <div class="mx-6 sm:mx-8 mt-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Form Body -->
        <form action="{{ route('ambulance.book.submit') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <!-- Patient & Contact Information -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-user-injured text-cyan-600"></i>
                    <span>1. Patient & Caller Details</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Patient / Caller Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="patient_name" 
                               value="{{ old('patient_name', $patient ? $patient->full_name : (Auth::user()->name ?? '')) }}"
                               placeholder="e.g. John Doe" required
                               class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Emergency Contact Phone <span class="text-rose-500">*</span>
                        </label>
                        <input type="tel" name="contact_phone" 
                               value="{{ old('contact_phone', $patient ? $patient->phone : '') }}"
                               placeholder="e.g. +1 (555) 019-2834" required
                               class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Pickup Location & Map Pinpoint -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-2">
                        <i class="fa-solid fa-location-dot text-rose-600"></i>
                        <span>2. Pickup Location & GPS Coordinates</span>
                    </h3>
                    <button type="button" id="btnGeolocate" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1.5 bg-cyan-50 hover:bg-cyan-100 px-3 py-1.5 rounded-xl border border-cyan-200 transition">
                        <i class="fa-solid fa-crosshairs"></i>
                        <span id="btnGeolocateTxt">Detect My GPS</span>
                    </button>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Pickup Street Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" id="pickupAddress" name="pickup_address" 
                               value="{{ old('pickup_address', $prefillAddress ?? '') }}"
                               placeholder="Acquiring your device GPS location or type address..." required
                               class="w-full text-xs font-semibold pl-10 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                        <i class="fa-solid fa-location-dot absolute left-3.5 top-3.5 text-rose-500 text-xs"></i>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">We automatically detect your device GPS. You can also drag the pin or click on the map below to fine-tune the exact pickup point.</p>
                </div>

                <!-- Interactive Pin-Drop Leaflet Map -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden relative shadow-inner">
                    <div id="pinMap" class="w-full h-72 z-10"></div>
                    <div class="absolute bottom-3 left-3 z-20 bg-slate-900/80 backdrop-blur-md text-white text-[11px] px-3 py-1.5 rounded-xl font-mono shadow">
                        Lat: <span id="lblLat">{{ old('pickup_latitude', $prefillLat ?? '--') }}</span> | Lng: <span id="lblLng">{{ old('pickup_longitude', $prefillLng ?? '--') }}</span>
                    </div>
                </div>

                <!-- Hidden Latitude & Longitude Inputs -->
                <input type="hidden" id="pickupLat" name="pickup_latitude" value="{{ old('pickup_latitude', $prefillLat ?? '') }}">
                <input type="hidden" id="pickupLng" name="pickup_longitude" value="{{ old('pickup_longitude', $prefillLng ?? '') }}">
            </div>

            <hr class="border-slate-100">

            <!-- Ambulance Selection & Hospital Department -->
            <div class="space-y-4">
                <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400 flex items-center gap-2">
                    <i class="fa-solid fa-hospital-user text-indigo-600"></i>
                    <span>3. Ambulance & Hospital Routing</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Ambulance Assignment
                        </label>
                        <select name="ambulance_id" class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            <option value="">⚡ Auto-Assign Nearest Available (Fastest)</option>
                            @foreach($availableAmbulances as $amb)
                            <option value="{{ $amb->id }}" {{ (old('ambulance_id') == $amb->id || ($selectedAmbulance && $selectedAmbulance->id == $amb->id)) ? 'selected' : '' }}>
                                {{ $amb->vehicle_number }} — {{ $amb->model }} ({{ $amb->typeDisplay() }})
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">If unselected, the system automatically dispatches the nearest operational vehicle.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Receiving Hospital Department
                        </label>
                        <select name="destination_hospital_department_id" class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                            @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ ($dept->code === 'EMER' || old('destination_hospital_department_id') == $dept->id) ? 'selected' : '' }}>
                                {{ $dept->name }} {{ $dept->code === 'EMER' ? '(Emergency Care)' : '' }}
                            </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1">Destination hospital facility: {{ $hospital['name'] }}.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Emergency Medical Notes / Symptoms (Optional)
                    </label>
                    <textarea name="notes" rows="2" placeholder="e.g., Unconscious patient, severe chest pain, oxygen required, 3rd floor apartment..."
                              class="w-full text-xs font-medium px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-emerald-500"></i>
                    <span>Triage medical responders are notified immediately upon dispatch.</span>
                </div>
                <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 hover:from-rose-500 hover:to-red-500 text-white font-extrabold text-sm shadow-xl shadow-rose-600/30 flex items-center justify-center gap-2.5 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-bolt text-amber-300"></i>
                    <span>Confirm & Dispatch Ambulance</span>
                </button>
            </div>
        </form>

    </div>

</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const hospitalCoords = [{{ $hospital['lat'] }}, {{ $hospital['lng'] }}];
        let currentLat = parseFloat(document.getElementById('pickupLat').value);
        let currentLng = parseFloat(document.getElementById('pickupLng').value);

        let initialLat = !isNaN(currentLat) ? currentLat : hospitalCoords[0];
        let initialLng = !isNaN(currentLng) ? currentLng : hospitalCoords[1];

        const map = L.map('pinMap', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([initialLat, initialLng], isNaN(currentLat) ? 12 : 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Custom draggable patient pickup pin
        const pinIcon = L.divIcon({
            className: 'custom-pickup-pin',
            html: `
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white shadow-xl flex items-center justify-center border-2 border-white ring-4 ring-rose-400/40 transform -translate-x-1/2 -translate-y-1/2 animate-pulse">
                    <i class="fa-solid fa-person-falling-burst text-base"></i>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        let marker = L.marker([initialLat, initialLng], {
            draggable: true,
            icon: pinIcon
        }).addTo(map);

        function reverseGeocode(lat, lng) {
            const addrInput = document.getElementById('pickupAddress');
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        addrInput.value = data.display_name;
                    }
                })
                .catch(() => {
                    if (!addrInput.value) {
                        addrInput.value = `GPS Location: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    }
                });
        }

        function updateCoords(lat, lng, doReverseGeocode = true) {
            document.getElementById('pickupLat').value = lat.toFixed(7);
            document.getElementById('pickupLng').value = lng.toFixed(7);
            document.getElementById('lblLat').innerText = lat.toFixed(4);
            document.getElementById('lblLng').innerText = lng.toFixed(4);

            if (doReverseGeocode) {
                reverseGeocode(lat, lng);
            }
        }

        marker.on('dragend', function (e) {
            const position = marker.getLatLng();
            updateCoords(position.lat, position.lng, true);
        });

        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateCoords(e.latlng.lat, e.latlng.lng, true);
        });

        // Geolocate user's device
        function getDeviceLocation() {
            const btn = document.getElementById('btnGeolocate');
            const txt = document.getElementById('btnGeolocateTxt');

            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            txt.textContent = 'Acquiring GPS...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const userLat = pos.coords.latitude;
                    const userLng = pos.coords.longitude;
                    map.flyTo([userLat, userLng], 16, { animate: true });
                    marker.setLatLng([userLat, userLng]);
                    updateCoords(userLat, userLng, true);
                    txt.textContent = 'GPS Detected ✓';
                    btn.disabled = false;
                },
                function (err) {
                    console.warn('GPS detection notice:', err.message);
                    txt.textContent = 'Detect My GPS';
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        }

        document.getElementById('btnGeolocate').addEventListener('click', getDeviceLocation);

        // If no coordinates passed, auto-detect device location on load
        if (isNaN(currentLat) || isNaN(currentLng)) {
            getDeviceLocation();
        } else if (!document.getElementById('pickupAddress').value) {
            reverseGeocode(currentLat, currentLng);
        }
    });
</script>
@endsection
