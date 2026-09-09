@extends('layouts.app')

@section('title', 'Register New Ambulance Unit')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.ambulances.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-cyan-600 transition">
            <i class="fa-solid fa-arrow-left"></i>
            <span>Back to Fleet List</span>
        </a>
    </div>

    <div class="bg-white rounded-3xl shadow-soft border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-slate-900 to-cyan-950 p-6 sm:p-8 text-white flex items-center justify-between">
            <div>
                <h1 class="text-xl sm:text-2xl font-heading font-extrabold">Register New Ambulance</h1>
                <p class="text-xs text-slate-300 mt-1">Add a certified emergency vehicle to the hospital rapid response fleet stationed at your current device coordinates or custom GPS location.</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-600/30 border border-cyan-400/30 flex items-center justify-center text-cyan-400 text-2xl">
                <i class="fa-solid fa-truck-medical"></i>
            </div>
        </div>

        @if($errors->any())
        <div class="m-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('admin.ambulances.store') }}" method="POST" class="p-6 sm:p-8 space-y-6">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Vehicle Number / Plate <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="e.g. AMB-106" required
                           class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition uppercase">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Vehicle Make & Model <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="model" value="{{ old('model') }}" placeholder="e.g. Mercedes-Benz Sprinter 3500 Mobile ICU" required
                           class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Equipment / Classification Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" required class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                        <option value="Advanced_Life_Support" {{ old('type') == 'Advanced_Life_Support' ? 'selected' : '' }}>Advanced Life Support (ALS / ICU)</option>
                        <option value="Basic" {{ old('type') == 'Basic' ? 'selected' : '' }}>Basic Life Support (BLS)</option>
                        <option value="Patient_Transport" {{ old('type') == 'Patient_Transport' ? 'selected' : '' }}>Patient Transport Service (PTS)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Initial Operational Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available & Ready</option>
                        <option value="dispatched" {{ old('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                        <option value="in_transit" {{ old('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                        <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>In Maintenance</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">
                        Assign Dedicated Driver (Optional)
                    </label>
                    <select name="current_driver_id" class="w-full text-xs font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                        <option value="">-- No Driver Assigned (Leave unassigned) --</option>
                        @foreach($availableDrivers as $drv)
                        <option value="{{ $drv->id }}" {{ old('current_driver_id') == $drv->id ? 'selected' : '' }}>
                            {{ $drv->user?->name }} (Lic: {{ $drv->license_number }} | Phone: {{ $drv->contact_number }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Station Base & Device Location Map Section -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                            <i class="fa-solid fa-location-crosshairs text-cyan-600"></i>
                            <span>Ambulance Station Base & Telemetry GPS</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Use your current device location or click/drag on the map to place this ambulance.</p>
                    </div>
                    <button type="button" id="btnDeviceGps" class="px-3.5 py-2 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 text-xs font-bold flex items-center gap-1.5 shadow-sm transition">
                        <i class="fa-solid fa-location-arrow"></i>
                        <span id="btnDeviceGpsTxt">Use My Device Location</span>
                    </button>
                </div>

                <!-- Reverse Geocoded Address Display -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 text-slate-700 truncate">
                        <i class="fa-solid fa-map-pin text-rose-500 shrink-0"></i>
                        <span id="stationAddressText" class="truncate font-semibold">Locating station base...</span>
                    </div>
                    <span id="gpsStatusPill" class="shrink-0 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">Map Pin</span>
                </div>

                <!-- Interactive Leaflet Map -->
                <div class="border border-slate-200 rounded-2xl overflow-hidden relative shadow-inner">
                    <div id="stationMap" class="w-full h-64 z-10"></div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Station Base Latitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="inputLat" name="current_latitude" value="{{ old('current_latitude') }}" required
                               class="w-full text-xs font-mono font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">
                            Station Base Longitude <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="inputLng" name="current_longitude" value="{{ old('current_longitude') }}" required
                               class="w-full text-xs font-mono font-semibold px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.ambulances.index') }}" class="px-5 py-3 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs shadow-md shadow-cyan-600/20 transition">
                    Register & Station Ambulance
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
        let latInput = document.getElementById('inputLat');
        let lngInput = document.getElementById('inputLng');

        let initLat = parseFloat(latInput.value);
        let initLng = parseFloat(lngInput.value);

        const hasInitial = !isNaN(initLat) && !isNaN(initLng);
        const mapCenter = hasInitial ? [initLat, initLng] : [20.5937, 78.9629]; // India / default center before GPS
        const zoomLevel = hasInitial ? 15 : 4;

        const map = L.map('stationMap', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView(mapCenter, zoomLevel);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const ambIcon = L.divIcon({
            className: 'custom-station-pin',
            html: `
                <div class="w-10 h-10 rounded-2xl bg-cyan-600 text-white shadow-xl flex items-center justify-center border-2 border-white ring-4 ring-cyan-400/40 transform -translate-x-1/2 -translate-y-1/2">
                    <i class="fa-solid fa-truck-medical text-base"></i>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        let marker = L.marker(mapCenter, {
            draggable: true,
            icon: ambIcon
        }).addTo(map);

        function reverseGeocode(lat, lng) {
            document.getElementById('stationAddressText').textContent = `Resolving address for ${lat.toFixed(4)}, ${lng.toFixed(4)}...`;
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('stationAddressText').textContent = data.display_name;
                    } else {
                        document.getElementById('stationAddressText').textContent = `Coordinates: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                    }
                })
                .catch(() => {
                    document.getElementById('stationAddressText').textContent = `Coordinates: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
                });
        }

        function setCoords(lat, lng, doGeocode = true) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
            marker.setLatLng([lat, lng]);
            if (doGeocode) reverseGeocode(lat, lng);
        }

        marker.on('dragend', function (e) {
            const pos = marker.getLatLng();
            setCoords(pos.lat, pos.lng, true);
        });

        map.on('click', function (e) {
            setCoords(e.latlng.lat, e.latlng.lng, true);
        });

        latInput.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.flyTo([lat, lng], 15);
                setCoords(lat, lng, true);
            }
        });

        lngInput.addEventListener('change', function () {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                map.flyTo([lat, lng], 15);
                setCoords(lat, lng, true);
            }
        });

        function acquireDeviceGps() {
            const btn = document.getElementById('btnDeviceGps');
            const txt = document.getElementById('btnDeviceGpsTxt');

            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            txt.textContent = 'Acquiring GPS...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    const uLat = pos.coords.latitude;
                    const uLng = pos.coords.longitude;
                    map.flyTo([uLat, uLng], 15, { animate: true });
                    setCoords(uLat, uLng, true);
                    txt.textContent = 'Device GPS Set ✓';
                    document.getElementById('gpsStatusPill').className = 'shrink-0 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800';
                    document.getElementById('gpsStatusPill').textContent = 'Device GPS Active';
                    btn.disabled = false;
                },
                function (err) {
                    console.warn('GPS Notice:', err.message);
                    txt.textContent = 'Use My Device Location';
                    btn.disabled = false;
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        }

        document.getElementById('btnDeviceGps').addEventListener('click', acquireDeviceGps);

        // Auto-detect device location on page load if no initial coordinates
        if (!hasInitial) {
            acquireDeviceGps();
        } else {
            reverseGeocode(initLat, initLng);
        }
    });
</script>
@endsection
