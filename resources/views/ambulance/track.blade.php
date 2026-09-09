@extends(Auth::check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Live Ambulance Tracking - Booking #' . $booking->id)

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Top Breadcrumb & Status Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('ambulance.index') }}" class="w-9 h-9 rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-cyan-600 flex items-center justify-center transition shadow-sm">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-heading font-extrabold text-slate-900">
                        Live Ambulance Dispatch #{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}
                    </h1>
                    <span id="badgeLive" class="px-2.5 py-0.5 rounded-full bg-rose-500/10 text-rose-600 border border-rose-200 text-[10px] font-extrabold uppercase tracking-widest flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-ping"></span>
                        Live Radar
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Booked {{ $booking->booking_time ? $booking->booking_time->diffForHumans() : 'Recently' }}</p>
            </div>
        </div>

        <!-- Simulation Control Bar for Demo / Realism -->
        <div class="flex items-center gap-2 bg-slate-900 text-white px-4 py-2 rounded-2xl shadow-md border border-slate-800 text-xs">
            <span class="text-slate-400 font-medium hidden md:inline">Zomato-Style Live Movement:</span>
            <button id="btnAutoSimulate" class="px-3 py-1.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold transition flex items-center gap-1.5">
                <i class="fa-solid fa-play text-xs"></i>
                <span id="simBtnText">Start Live Movement</span>
            </button>
            <button id="btnManualStep" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-semibold transition flex items-center gap-1">
                <span>Step Next</span>
                <i class="fa-solid fa-forward-step text-xs"></i>
            </button>
        </div>
    </div>

    <!-- Zomato-Style Status Progression Bar -->
    <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-200">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center text-xl shadow-sm">
                    <i class="fa-solid fa-truck-medical animate-pulse"></i>
                </div>
                <div>
                    <span id="txtEtaSub" class="text-xs font-bold uppercase tracking-wider text-rose-600">Ambulance En Route</span>
                    <h2 id="txtEtaMain" class="text-2xl font-heading font-extrabold text-slate-900">
                        Calculating arrival time...
                    </h2>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="flex items-center gap-2.5 bg-slate-50 p-2.5 rounded-2xl border border-slate-200/80">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-base shadow-sm">
                    <i class="fa-solid fa-phone"></i>
                </div>
                <div>
                    <div class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Driver Direct Line</div>
                    <a id="lnkDriverPhone" href="tel:{{ $booking->driver?->contact_number ?? $booking->ambulance?->currentDriver?->contact_number ?? '911' }}" class="text-xs font-bold text-slate-800 hover:text-cyan-600 transition">
                        {{ $booking->driver?->contact_number ?? $booking->ambulance?->currentDriver?->contact_number ?? '+1 (555) 911-0000' }}
                    </a>
                </div>
            </div>
        </div>

        <!-- 5-Step Order Progress Line (Zomato Style) -->
        <div class="relative mt-4">
            <div class="absolute top-1/2 left-0 right-0 h-1 bg-slate-100 -translate-y-1/2 z-0"></div>
            <div id="progressFill" class="absolute top-1/2 left-0 h-1 bg-gradient-to-r from-cyan-500 to-rose-500 -translate-y-1/2 z-0 transition-all duration-700" style="width: 40%;"></div>

            <div class="grid grid-cols-5 relative z-10 text-center">
                <!-- Step 1: Requested -->
                <div class="flex flex-col items-center">
                    <div id="stepDot-1" class="w-8 h-8 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-cyan-100 shadow">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-800 mt-2">Requested</span>
                </div>

                <!-- Step 2: Assigned -->
                <div class="flex flex-col items-center">
                    <div id="stepDot-2" class="w-8 h-8 rounded-full bg-cyan-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-cyan-100 shadow">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="text-[11px] font-bold text-slate-800 mt-2">Assigned</span>
                </div>

                <!-- Step 3: En Route -->
                <div class="flex flex-col items-center">
                    <div id="stepDot-3" class="w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-rose-100 shadow animate-pulse">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <span class="text-[11px] font-bold text-rose-600 mt-2">En Route</span>
                </div>

                <!-- Step 4: Arrived -->
                <div class="flex flex-col items-center">
                    <div id="stepDot-4" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold ring-4 ring-slate-100">
                        <i class="fa-solid fa-location-dot"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400 mt-2">Arrived</span>
                </div>

                <!-- Step 5: Completed -->
                <div class="flex flex-col items-center">
                    <div id="stepDot-5" class="w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold ring-4 ring-slate-100">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </div>
                    <span class="text-[11px] font-medium text-slate-400 mt-2">Completed</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Live Map & Dispatch Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Full-Height Live Tracking Map (8 cols) -->
        <div class="lg:col-span-8 flex flex-col space-y-4">
            <div class="bg-white rounded-3xl p-4 shadow-soft border border-slate-200 relative">
                <div class="flex items-center justify-between mb-3 px-2">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Live Satellite Navigation Radar</h3>
                    </div>
                    <div class="text-[11px] text-slate-400 font-mono">
                        GPS Polling: <span id="pollingStatus" class="text-emerald-600 font-bold">Active (3s)</span>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="liveTrackMap" class="w-full h-[540px] rounded-2xl overflow-hidden border border-slate-200 z-10"></div>

                <!-- Quick floating metrics overlay on map -->
                <div class="absolute bottom-8 left-8 z-20 bg-slate-950/85 backdrop-blur-md text-white rounded-2xl p-4 border border-slate-800 shadow-2xl space-y-1">
                    <div class="text-[10px] text-cyan-400 uppercase tracking-widest font-extrabold">Live Dispatch Telemetry</div>
                    <div class="flex items-center gap-4 text-xs font-mono pt-1">
                        <div>
                            <span class="text-slate-400">Distance:</span>
                            <span id="valDistance" class="font-bold text-white text-sm">-- km</span>
                        </div>
                        <div class="w-px h-6 bg-slate-700"></div>
                        <div>
                            <span class="text-slate-400">Est. Time:</span>
                            <span id="valEta" class="font-bold text-emerald-400 text-sm">-- mins</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Driver, Vehicle & Trip Information (4 cols) -->
        <div class="lg:col-span-4 space-y-4">

            <!-- Driver & Responder Card -->
            <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-200 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">
                        Assigned Medical Responder
                    </h3>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        On Duty
                    </span>
                </div>

                <div class="flex items-center gap-3.5 pt-1">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-700 text-white flex items-center justify-center font-extrabold text-xl shadow-md border-2 border-white ring-2 ring-cyan-200 shrink-0">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div class="min-w-0">
                        <h4 id="driverName" class="font-extrabold text-slate-900 text-sm truncate">
                            {{ $booking->driver?->user?->name ?? $booking->ambulance?->currentDriver?->user?->name ?? 'Marcus Vance' }}
                        </h4>
                        <p class="text-xs text-slate-500 font-medium">Licensed Emergency Paramedic</p>
                        <p id="driverLicense" class="text-[11px] text-cyan-600 font-mono font-bold mt-0.5">
                            Lic: {{ $booking->driver?->license_number ?? $booking->ambulance?->currentDriver?->license_number ?? 'MA-CDL-894021' }}
                        </p>
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                    <a id="btnCallDriver" href="tel:{{ $booking->driver?->contact_number ?? $booking->ambulance?->currentDriver?->contact_number ?? '+15553014411' }}" 
                       class="flex-1 py-3 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center justify-center gap-2 shadow-sm transition">
                        <i class="fa-solid fa-phone"></i>
                        <span>Call Driver</span>
                    </a>
                    <a href="tel:911" class="py-3 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs border border-rose-200 flex items-center justify-center gap-1.5 transition">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>911</span>
                    </a>
                </div>
            </div>

            <!-- Vehicle Details Card -->
            <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-200 space-y-3">
                <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">
                    Vehicle Specifications
                </h3>

                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-slate-500">Plate Number</div>
                        <div class="text-base font-extrabold text-slate-900 font-mono">{{ $booking->ambulance->vehicle_number }}</div>
                    </div>
                    <span class="px-2.5 py-1 text-[11px] font-bold rounded-full border {{ $booking->ambulance->typeBadge() }}">
                        {{ $booking->ambulance->typeDisplay() }}
                    </span>
                </div>

                <div class="pt-2 border-t border-slate-100 text-xs space-y-1.5">
                    <div class="flex justify-between text-slate-600">
                        <span>Model:</span>
                        <span class="font-bold text-slate-800">{{ $booking->ambulance->model }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Equipment:</span>
                        <span class="font-bold text-emerald-600">Defibrillator, $O_2$, Telemetry</span>
                    </div>
                </div>
            </div>

            <!-- Destination Hospital Facility Card -->
            <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-200 space-y-3">
                <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">
                    Destination Facility
                </h3>

                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center text-sm shrink-0 mt-0.5">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-900">{{ $hospital['name'] }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">{{ $hospital['address'] }}</div>
                        <div class="inline-block mt-2 px-2.5 py-0.5 rounded-lg bg-cyan-50 text-cyan-700 border border-cyan-200 text-[11px] font-bold">
                            Receiving: {{ $booking->destinationDepartment?->name ?? 'Emergency Medicine' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pickup Address Card -->
            <div class="bg-white rounded-3xl p-6 shadow-soft border border-slate-200 space-y-2">
                <h3 class="text-xs font-extrabold uppercase tracking-widest text-slate-400">
                    Pickup Location
                </h3>
                <div class="flex items-start gap-2 text-xs font-medium text-slate-700">
                    <i class="fa-solid fa-location-dot text-rose-500 mt-0.5"></i>
                    <span>{{ $booking->pickup_address }}</span>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const bookingId = {{ $booking->id }};
        const pickupCoords = [{{ $booking->pickup_latitude }}, {{ $booking->pickup_longitude }}];
        const hospitalCoords = [{{ $hospital['lat'] }}, {{ $hospital['lng'] }}];
        let ambulanceCoords = [
            {{ $booking->ambulance->current_latitude ?? $hospital['lat'] }}, 
            {{ $booking->ambulance->current_longitude ?? $hospital['lng'] }}
        ];

        // 1. Initialize Leaflet Map
        const map = L.map('liveTrackMap', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView(ambulanceCoords, 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // 2. Custom Markers
        // Hospital Marker
        const hospitalIcon = L.divIcon({
            className: 'custom-hosp-marker',
            html: `
                <div class="w-8 h-8 rounded-xl bg-slate-900 text-white shadow-lg flex items-center justify-center border-2 border-white transform -translate-x-1/2 -translate-y-1/2">
                    <i class="fa-solid fa-hospital text-xs text-rose-400"></i>
                </div>
            `,
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });
        L.marker(hospitalCoords, { icon: hospitalIcon }).addTo(map).bindPopup('<b>{{ $hospital['name'] }}</b><br>Receiving Facility');

        // Patient Pickup Marker
        const pickupIcon = L.divIcon({
            className: 'custom-pickup-marker',
            html: `
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white shadow-xl flex items-center justify-center border-2 border-white ring-4 ring-rose-400/40 transform -translate-x-1/2 -translate-y-1/2 animate-pulse">
                    <i class="fa-solid fa-person-falling-burst text-sm"></i>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
        const pickupMarker = L.marker(pickupCoords, { icon: pickupIcon }).addTo(map)
            .bindPopup('<b>Your Pickup Spot</b><br>{{ addslashes($booking->pickup_address) }}');

        // Ambulance Moving Marker (with dynamic rotation/beacon)
        const ambulanceIcon = L.divIcon({
            className: 'custom-live-amb-marker',
            html: `
                <div id="ambMarkerDiv" class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-rose-600 to-red-500 text-white shadow-2xl flex items-center justify-center border-2 border-white ring-4 ring-rose-500/50 transform -translate-x-1/2 -translate-y-1/2 transition-transform duration-700 ease-linear">
                    <i class="fa-solid fa-truck-medical text-lg animate-pulse"></i>
                </div>
            `,
            iconSize: [48, 48],
            iconAnchor: [24, 24]
        });
        let ambulanceMarker = L.marker(ambulanceCoords, { icon: ambulanceIcon, zIndexOffset: 1000 }).addTo(map);

        // Route Polyline (Zomato-style dotted route)
        let routePolyline = L.polyline([ambulanceCoords, pickupCoords], {
            color: '#0284c7',
            weight: 5,
            opacity: 0.85,
            dashArray: '8, 8',
            lineCap: 'round'
        }).addTo(map);

        // Fit bounds to show both ambulance and pickup
        map.fitBounds(L.latLngBounds([ambulanceCoords, pickupCoords]), { padding: [60, 60] });

        // Update UI step progression
        function updateProgress(stepIndex, statusDisplay, etaText) {
            const steps = [1, 2, 3, 4, 5];
            const fillWidth = ((stepIndex - 1) / 4) * 100;
            document.getElementById('progressFill').style.width = fillWidth + '%';

            steps.forEach(s => {
                const dot = document.getElementById('stepDot-' + s);
                if (!dot) return;
                if (s < stepIndex) {
                    dot.className = 'w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold ring-4 ring-emerald-100 shadow';
                    dot.innerHTML = '<i class="fa-solid fa-check"></i>';
                } else if (s === stepIndex) {
                    dot.className = 'w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center text-xs font-bold ring-4 ring-rose-100 shadow animate-pulse';
                } else {
                    dot.className = 'w-8 h-8 rounded-full bg-slate-200 text-slate-500 flex items-center justify-center text-xs font-bold ring-4 ring-slate-100';
                }
            });

            document.getElementById('txtEtaSub').innerText = statusDisplay;
            document.getElementById('txtEtaMain').innerText = etaText;
        }

        // Live Polling Function
        function pollTrackingData() {
            fetch(`{{ url('/ambulance/api/track') }}/${bookingId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.ambulance && data.ambulance.lat && data.ambulance.lng) {
                        const newCoords = [data.ambulance.lat, data.ambulance.lng];
                        ambulanceMarker.setLatLng(newCoords);
                        ambulanceCoords = newCoords;

                        // Update route line
                        if (data.status === 'arrived' || data.status === 'completed') {
                            routePolyline.setLatLngs([newCoords, hospitalCoords]);
                        } else {
                            routePolyline.setLatLngs([newCoords, pickupCoords]);
                        }

                        // Update Telemetry & UI
                        document.getElementById('valDistance').innerText = data.metrics.distance_km + ' km';
                        document.getElementById('valEta').innerText = data.metrics.eta_minutes + ' mins';
                        updateProgress(data.step_index, data.status_display, data.metrics.eta_text);

                        if (data.driver) {
                            document.getElementById('driverName').innerText = data.driver.name;
                            document.getElementById('driverLicense').innerText = 'Lic: ' + data.driver.license;
                            document.getElementById('btnCallDriver').href = 'tel:' + data.driver.phone;
                            document.getElementById('lnkDriverPhone').href = 'tel:' + data.driver.phone;
                            document.getElementById('lnkDriverPhone').innerText = data.driver.phone;
                        }
                    }
                })
                .catch(err => console.error('Tracking fetch error:', err));
        }

        // Initial fetch
        pollTrackingData();

        // 3.5s background polling
        const pollInterval = setInterval(pollTrackingData, 3500);

        // Simulation Controller
        let autoSimInterval = null;

        function stepSimulation() {
            fetch(`{{ url('/ambulance/api/simulate-step') }}/${bookingId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                pollTrackingData();
                if (data.status === 'completed') {
                    clearInterval(autoSimInterval);
                    autoSimInterval = null;
                    document.getElementById('simBtnText').innerText = 'Trip Finished';
                    document.getElementById('btnAutoSimulate').classList.replace('bg-rose-500', 'bg-cyan-500');
                }
            })
            .catch(err => console.error('Sim error:', err));
        }

        document.getElementById('btnManualStep').addEventListener('click', function () {
            stepSimulation();
        });

        document.getElementById('btnAutoSimulate').addEventListener('click', function () {
            if (autoSimInterval) {
                clearInterval(autoSimInterval);
                autoSimInterval = null;
                document.getElementById('simBtnText').innerText = 'Start Live Movement';
                this.classList.replace('bg-rose-500', 'bg-cyan-500');
            } else {
                stepSimulation();
                autoSimInterval = setInterval(stepSimulation, 3000);
                document.getElementById('simBtnText').innerText = 'Pause Live Movement';
                this.classList.replace('bg-cyan-500', 'bg-rose-500');
            }
        });
    });
</script>
@endsection
