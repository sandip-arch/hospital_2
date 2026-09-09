@extends(Auth::check() ? 'layouts.app' : 'layouts.public')

@section('title', 'Emergency Ambulance Services & Live Map')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Top Hero / Header Section -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-cyan-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-cyan-400 text-9xl pointer-events-none">
            <i class="fa-solid fa-truck-medical"></i>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-rose-400 animate-ping"></span>
                    24/7 Rapid Emergency Response
                </div>
                <h1 class="text-2xl sm:text-3xl font-heading font-extrabold tracking-tight">
                    Emergency Ambulance Fleet & Live Map
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl">
                    Locate nearby rapid-response ambulances in real-time based on your physical device location, inspect onboard life-support capabilities, or request instant dispatch to your exact GPS coordinates.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a id="btnTopBook" href="{{ route('ambulance.book') }}" class="px-5 py-3 rounded-2xl bg-gradient-to-r from-rose-600 to-red-500 hover:from-rose-500 hover:to-red-400 text-white font-bold text-sm shadow-lg shadow-rose-600/30 flex items-center gap-2.5 transition transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-truck-medical text-base"></i>
                    <span>Book Ambulance Now</span>
                </a>
                <a href="{{ route('ambulance.my-bookings') }}" class="px-4 py-3 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-sm flex items-center gap-2 transition">
                    <i class="fa-solid fa-clock-rotate-left text-cyan-400"></i>
                    <span>My Bookings</span>
                </a>
                @if(Auth::check() && Auth::user()->isAdmin())
                <a href="{{ route('admin.ambulances.index') }}" class="px-4 py-3 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-semibold text-sm flex items-center gap-2 shadow-md transition">
                    <i class="fa-solid fa-gear"></i>
                    <span>Admin Fleet Manager</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Quick Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-6 pt-6 border-t border-slate-800">
            <div class="bg-slate-800/60 rounded-2xl p-3 border border-slate-700/60 backdrop-blur-sm">
                <div class="text-xs text-slate-400 font-medium">Total Fleet</div>
                <div class="text-xl font-extrabold text-white mt-0.5">{{ $stats['total'] }} Units</div>
            </div>
            <div class="bg-emerald-950/40 rounded-2xl p-3 border border-emerald-800/50 backdrop-blur-sm">
                <div class="text-xs text-emerald-400 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Available Now
                </div>
                <div class="text-xl font-extrabold text-emerald-300 mt-0.5">{{ $stats['available'] }} Ready</div>
            </div>
            <div class="bg-amber-950/40 rounded-2xl p-3 border border-amber-800/50 backdrop-blur-sm">
                <div class="text-xs text-amber-400 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span> Dispatched
                </div>
                <div class="text-xl font-extrabold text-amber-300 mt-0.5">{{ $stats['dispatched'] }} Active</div>
            </div>
            <div class="bg-cyan-950/40 rounded-2xl p-3 border border-cyan-800/50 backdrop-blur-sm">
                <div class="text-xs text-cyan-400 font-medium flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span> In Transit
                </div>
                <div class="text-xl font-extrabold text-cyan-300 mt-0.5">{{ $stats['in_transit'] }} On Trip</div>
            </div>
            <div class="bg-slate-900/60 rounded-2xl p-3 border border-slate-700/60 backdrop-blur-sm col-span-2 sm:col-span-1">
                <div class="text-xs text-slate-400 font-medium">Maintenance</div>
                <div class="text-xl font-extrabold text-slate-300 mt-0.5">{{ $stats['maintenance'] }} Units</div>
            </div>
        </div>
    </div>

    <!-- Live Device Location GPS & Fleet Dispatch Bar -->
    <div class="bg-white rounded-3xl p-4 sm:p-5 shadow-soft border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div id="gpsIconBox" class="w-11 h-11 rounded-2xl bg-cyan-50 border border-cyan-200 text-cyan-600 flex items-center justify-center text-lg shrink-0 shadow-sm">
                <i class="fa-solid fa-location-crosshairs"></i>
            </div>
            <div class="space-y-0.5">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Your Device Location</span>
                    <span id="gpsStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-600 border border-slate-200 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Initializing GPS...
                    </span>
                </div>
                <div id="gpsAddressText" class="text-xs sm:text-sm font-bold text-slate-800 truncate max-w-lg">
                    Detecting your physical location from browser...
                </div>
                <div id="gpsCoordsText" class="text-[11px] font-mono text-slate-400">
                    Lat: -- | Lng: --
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <button type="button" id="btnDetectGps" class="px-4 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <i class="fa-solid fa-location-arrow"></i>
                <span id="btnDetectGpsText">Detect My Location</span>
            </button>
            <button type="button" id="btnDeployNearby" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-100 font-bold text-xs shadow-sm border border-slate-700 flex items-center gap-2 transition" title="Reposition ambulances around your real location for testing/demo">
                <i class="fa-solid fa-satellite-dish text-cyan-400"></i>
                <span>Deploy Fleet Near Me</span>
            </button>
        </div>
    </div>

    <!-- Main Content: Interactive Map & Ambulance Fleet Directory -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left Column: Interactive Map (7 cols) -->
        <div class="lg:col-span-7 flex flex-col space-y-4">
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between mb-3 px-2">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-map-location-dot"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-slate-900">Live Ambulance Radar Map</h2>
                            <p id="mapSubTitle" class="text-[11px] text-slate-500">Real-time GPS positions relative to your device</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="btnCenterUser" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 flex items-center gap-1.5 transition" title="Center map on your device">
                            <i class="fa-solid fa-person-circle-check text-cyan-600"></i>
                            <span>My Location</span>
                        </button>
                        <button id="btnRecenter" class="text-xs font-semibold px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 flex items-center gap-1.5 transition" title="Center map on Hospital Base">
                            <i class="fa-solid fa-hospital text-rose-600"></i>
                            <span>Hospital Base</span>
                        </button>
                    </div>
                </div>

                <!-- Leaflet Map Container -->
                <div id="ambulanceMap" class="w-full h-[520px] rounded-2xl overflow-hidden border border-slate-200 z-10 relative"></div>

                <!-- Map Legend -->
                <div class="flex flex-wrap items-center justify-between gap-3 mt-3 pt-3 border-t border-slate-100 text-xs text-slate-600 px-2">
                    <div class="flex items-center gap-4 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-3 h-3 rounded-full bg-cyan-500 ring-2 ring-cyan-200 animate-ping"></span> You (Device)
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 ring-2 ring-emerald-200"></span> Available
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-3 h-3 rounded-full bg-amber-500 ring-2 ring-amber-200"></span> Dispatched
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-3 h-3 rounded-full bg-cyan-600 ring-2 ring-cyan-200"></span> In Transit
                        </span>
                        <span class="inline-flex items-center gap-1.5 font-medium">
                            <span class="w-3 h-3 rounded-full bg-slate-400 ring-2 ring-slate-200"></span> Maintenance
                        </span>
                    </div>
                    <div class="text-[11px] text-slate-400 flex items-center gap-1">
                        <i class="fa-solid fa-hospital text-rose-500"></i>
                        <span>Hospital Base</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Ambulances Cards & Quick Booking Selector (5 cols) -->
        <div class="lg:col-span-5 flex flex-col space-y-4">
            
            <!-- Filters & Proximity Search Bar -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-magnifying-glass text-cyan-600"></i>
                        <span>Search & Filter Ambulances</span>
                    </h3>
                    <button type="button" id="btnClearFilters" class="text-xs text-cyan-600 hover:underline font-semibold">
                        Reset
                    </button>
                </div>

                <!-- Text Search Input -->
                <div class="relative">
                    <input type="text" id="ambulanceSearch" placeholder="Search by plate, model, driver name..."
                           class="w-full text-xs font-semibold pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:bg-white transition">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <!-- Proximity Radius Filter -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Max Distance</label>
                        <select id="radiusFilter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="">Any Distance</option>
                            <option value="5">Within 5 km</option>
                            <option value="10">Within 10 km</option>
                            <option value="25">Within 25 km</option>
                            <option value="50">Within 50 km</option>
                            <option value="100">Within 100 km</option>
                        </select>
                    </div>

                    <!-- Sort Selection -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Sort Fleet</label>
                        <select id="sortFilter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="distance" selected>Nearest to Me (GPS)</option>
                            <option value="available">Available First</option>
                            <option value="type">By Equipment Type</option>
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Status</label>
                        <select id="statusFilter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="">All Statuses</option>
                            <option value="available">Available</option>
                            <option value="dispatched">Dispatched</option>
                            <option value="in_transit">In Transit</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1">Type</label>
                        <select id="typeFilter" class="w-full text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="">All Types</option>
                            <option value="Advanced_Life_Support">Advanced Life Support</option>
                            <option value="Basic">Basic Life Support</option>
                            <option value="Patient_Transport">Patient Transport</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Ambulances Scrollable List -->
            <div class="bg-white rounded-3xl p-4 shadow-sm border border-slate-200 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-3 px-2">
                    <h3 class="text-sm font-bold text-slate-900">
                        Ambulances in Fleet (<span id="txtAmbulanceCount">{{ $ambulances->count() }}</span>)
                    </h3>
                    <span class="text-xs text-slate-400">Click to locate on map</span>
                </div>

                <div id="ambulanceListContainer" class="space-y-3 max-h-[500px] overflow-y-auto pr-1">
                    @forelse($ambulances as $amb)
                    <div id="ambCard-{{ $amb->id }}" 
                         data-id="{{ $amb->id }}"
                         data-plate="{{ strtolower($amb->vehicle_number) }}"
                         data-model="{{ strtolower($amb->model) }}"
                         data-driver="{{ strtolower($amb->currentDriver?->user?->name ?? '') }}"
                         data-type="{{ $amb->type }}"
                         data-status="{{ $amb->status }}"
                         data-lat="{{ $amb->current_latitude }}"
                         data-lng="{{ $amb->current_longitude }}"
                         onclick="focusAmbulance({{ $amb->id }}, {{ $amb->current_latitude ?? $hospital['lat'] }}, {{ $amb->current_longitude ?? $hospital['lng'] }})"
                         class="ambulance-card p-4 rounded-2xl border border-slate-200 hover:border-cyan-400 hover:shadow-md transition cursor-pointer bg-slate-50/50 hover:bg-white group">
                        
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-extrabold text-sm text-slate-900 group-hover:text-cyan-600 transition">
                                        {{ $amb->vehicle_number }}
                                    </span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border {{ $amb->typeBadge() }}">
                                        {{ $amb->typeDisplay() }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 mt-1 font-medium">{{ $amb->model }}</p>
                            </div>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-xl {{ $amb->statusBadge() }} shadow-sm">
                                {{ $amb->statusLabel() }}
                            </span>
                        </div>

                        <!-- Distance to device pill badge -->
                        <div class="mt-2.5 flex items-center gap-2">
                            <span class="distance-pill px-2.5 py-1 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-800 text-[11px] font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-location-arrow text-[10px] text-cyan-600"></i>
                                <span class="dist-text">Calculating distance...</span>
                            </span>
                        </div>

                        <!-- Driver info and booking action -->
                        <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2 text-slate-600">
                                <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center text-[10px] font-bold">
                                    <i class="fa-solid fa-id-card"></i>
                                </div>
                                <span class="font-medium truncate max-w-[140px]">
                                    {{ $amb->currentDriver?->user?->name ?? 'No Driver Assigned' }}
                                </span>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($amb->isAvailable())
                                <a href="{{ route('ambulance.book', $amb->id) }}" 
                                   id="bookLink-{{ $amb->id }}"
                                   onclick="event.stopPropagation()"
                                   class="book-link px-3 py-1.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-500 hover:from-rose-500 hover:to-red-400 text-white font-bold text-xs shadow-sm flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-bolt text-[11px]"></i>
                                    <span>Book</span>
                                </a>
                                @else
                                <span class="text-[11px] font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg">
                                    Busy
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div id="noAmbulanceMsg" class="text-center py-10 text-slate-400">
                        <i class="fa-solid fa-truck-medical text-3xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-medium">No ambulances found matching criteria.</p>
                    </div>
                    @endforelse
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
        const hospitalCoords = [{{ $hospital['lat'] }}, {{ $hospital['lng'] }}];
        let userLocation = null; // { lat, lng, address }
        let userMarker = null;
        let ambulancesData = @json($ambulances);

        // Initialize Leaflet Map
        const map = L.map('ambulanceMap', {
            zoomControl: true,
            scrollWheelZoom: true
        }).setView(hospitalCoords, 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Custom Hospital Base Marker
        const hospitalIcon = L.divIcon({
            className: 'custom-hospital-marker',
            html: `
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white shadow-xl flex items-center justify-center border-2 border-white ring-4 ring-rose-400/30 transform -translate-x-1/2 -translate-y-1/2">
                    <i class="fa-solid fa-hospital text-lg"></i>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const hospitalMarker = L.marker(hospitalCoords, { icon: hospitalIcon }).addTo(map);
        hospitalMarker.bindPopup(`
            <div class="p-2 text-slate-800">
                <div class="font-extrabold text-sm text-rose-600">{{ $hospital['name'] }}</div>
                <div class="text-xs text-slate-600 mt-0.5">{{ $hospital['address'] }}</div>
                <div class="mt-2 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Hospital Command Center</div>
            </div>
        `);

        // Ambulance Markers Collection
        const ambulanceMarkers = {};

        function getStatusColor(status) {
            switch(status) {
                case 'available': return { bg: 'bg-emerald-600', ring: 'ring-emerald-400/40', text: 'text-emerald-600' };
                case 'dispatched': return { bg: 'bg-amber-600', ring: 'ring-amber-400/40', text: 'text-amber-600' };
                case 'in_transit': return { bg: 'bg-cyan-600', ring: 'ring-cyan-400/40', text: 'text-cyan-600' };
                default: return { bg: 'bg-slate-600', ring: 'ring-slate-400/40', text: 'text-slate-600' };
            }
        }

        function renderAmbulanceMarkers() {
            // Remove previous markers
            Object.values(ambulanceMarkers).forEach(m => map.removeLayer(m));

            ambulancesData.forEach(amb => {
                if (amb.current_latitude && amb.current_longitude) {
                    const color = getStatusColor(amb.status);

                    const ambIcon = L.divIcon({
                        className: 'custom-amb-marker',
                        html: `
                            <div class="w-9 h-9 rounded-2xl ${color.bg} text-white shadow-lg flex items-center justify-center border-2 border-white ring-4 ${color.ring} transform -translate-x-1/2 -translate-y-1/2 transition hover:scale-110">
                                <i class="fa-solid fa-truck-medical text-sm"></i>
                            </div>
                        `,
                        iconSize: [36, 36],
                        iconAnchor: [18, 18]
                    });

                    const marker = L.marker([amb.current_latitude, amb.current_longitude], { icon: ambIcon }).addTo(map);

                    let distHtml = '';
                    if (amb.distance_km !== null && amb.distance_km !== undefined) {
                        distHtml = `<div class="text-[11px] font-bold text-cyan-600 mt-1"><i class="fa-solid fa-location-arrow"></i> ${amb.distance_km} km from your device (ETA ~${amb.eta_minutes || 1} mins)</div>`;
                    }

                    let bookUrl = `{{ url('/ambulance/book') }}/${amb.id}`;
                    if (userLocation) {
                        bookUrl += `?pickup_lat=${userLocation.lat}&pickup_lng=${userLocation.lng}&pickup_address=${encodeURIComponent(userLocation.address || 'My Device Location')}`;
                    }

                    const bookBtnHtml = amb.status === 'available'
                        ? `<a href="${bookUrl}" class="mt-3 block w-full py-2 px-3 rounded-xl bg-gradient-to-r from-rose-600 to-red-500 text-white font-bold text-center text-xs shadow hover:from-rose-500 hover:to-red-400 transition">Book This Ambulance</a>`
                        : `<div class="mt-2 text-center text-xs font-bold text-slate-400 bg-slate-100 py-1.5 rounded-lg">Unit Currently Busy</div>`;

                    marker.bindPopup(`
                        <div class="p-2 min-w-[210px] text-slate-800">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-extrabold text-sm text-slate-900">${amb.vehicle_number}</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full ${color.bg} text-white">${amb.status}</span>
                            </div>
                            <p class="text-xs text-slate-600 font-medium mt-1">${amb.model}</p>
                            <p class="text-[11px] text-slate-500 mt-1">Type: <span class="font-semibold text-slate-700">${(amb.type || '').replace(/_/g, ' ')}</span></p>
                            <p class="text-[11px] text-slate-500">Driver: <span class="font-semibold text-slate-700">${amb.current_driver?.user?.name || 'Unassigned'}</span></p>
                            ${distHtml}
                            ${bookBtnHtml}
                        </div>
                    `);

                    ambulanceMarkers[amb.id] = marker;
                }
            });
        }

        renderAmbulanceMarkers();

        // Focus function from list click
        window.focusAmbulance = function (id, lat, lng) {
            map.flyTo([lat, lng], 15, { animate: true, duration: 1 });
            if (ambulanceMarkers[id]) {
                setTimeout(() => {
                    ambulanceMarkers[id].openPopup();
                }, 800);
            }
        };

        // Haversine distance in km
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return Math.round(R * c * 10) / 10;
        }

        // Update list with distances and re-order
        function updateAmbulanceDistances() {
            if (!userLocation) return;

            ambulancesData.forEach(amb => {
                if (amb.current_latitude && amb.current_longitude) {
                    const d = calculateDistance(userLocation.lat, userLocation.lng, amb.current_latitude, amb.current_longitude);
                    amb.distance_km = d;
                    amb.eta_minutes = Math.max(1, Math.round((d / 40) * 60));
                } else {
                    amb.distance_km = null;
                    amb.eta_minutes = null;
                }

                // Update card badge
                const card = document.getElementById(`ambCard-${amb.id}`);
                if (card) {
                    const distPill = card.querySelector('.dist-text');
                    if (distPill) {
                        if (amb.distance_km !== null) {
                            distPill.textContent = `${amb.distance_km} km away • ETA ~${amb.eta_minutes} mins`;
                        } else {
                            distPill.textContent = 'GPS Signal Pending';
                        }
                    }

                    // Update book link with GPS coordinates query params
                    const bookLink = document.getElementById(`bookLink-${amb.id}`);
                    if (bookLink) {
                        bookLink.href = `{{ url('/ambulance/book') }}/${amb.id}?pickup_lat=${userLocation.lat}&pickup_lng=${userLocation.lng}&pickup_address=${encodeURIComponent(userLocation.address || 'My Device Location')}`;
                    }
                }
            });

            // Update top Book button too
            const btnTopBook = document.getElementById('btnTopBook');
            if (btnTopBook) {
                btnTopBook.href = `{{ route('ambulance.book') }}?pickup_lat=${userLocation.lat}&pickup_lng=${userLocation.lng}&pickup_address=${encodeURIComponent(userLocation.address || 'My Device Location')}`;
            }

            renderAmbulanceMarkers();
            filterAndSortCards();
        }

        // Filter and Sort Cards in DOM
        function filterAndSortCards() {
            const searchTerm = (document.getElementById('ambulanceSearch').value || '').trim().toLowerCase();
            const radius = parseFloat(document.getElementById('radiusFilter').value) || null;
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;
            const sortMode = document.getElementById('sortFilter').value;

            const container = document.getElementById('ambulanceListContainer');
            const cards = Array.from(document.querySelectorAll('.ambulance-card'));

            let visibleCount = 0;

            cards.forEach(card => {
                const id = parseInt(card.dataset.id);
                const amb = ambulancesData.find(a => a.id === id);

                const plate = card.dataset.plate || '';
                const model = card.dataset.model || '';
                const driver = card.dataset.driver || '';
                const ambType = card.dataset.type || '';
                const ambStatus = card.dataset.status || '';
                const dist = amb ? amb.distance_km : null;

                let matchesSearch = !searchTerm || plate.includes(searchTerm) || model.includes(searchTerm) || driver.includes(searchTerm);
                let matchesStatus = !status || ambStatus === status;
                let matchesType = !type || ambType === type;
                let matchesRadius = !radius || (dist !== null && dist <= radius);

                if (matchesSearch && matchesStatus && matchesType && matchesRadius) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Sort cards in DOM
            cards.sort((a, b) => {
                const ambA = ambulancesData.find(x => x.id === parseInt(a.dataset.id));
                const ambB = ambulancesData.find(x => x.id === parseInt(b.dataset.id));

                if (sortMode === 'distance') {
                    const distA = ambA && ambA.distance_km !== null ? ambA.distance_km : 999999;
                    const distB = ambB && ambB.distance_km !== null ? ambB.distance_km : 999999;
                    return distA - distB;
                } else if (sortMode === 'available') {
                    return (ambA.status === 'available' ? 0 : 1) - (ambB.status === 'available' ? 0 : 1);
                } else if (sortMode === 'type') {
                    return (ambA.type || '').localeCompare(ambB.type || '');
                }
                return 0;
            });

            cards.forEach(c => container.appendChild(c));
            document.getElementById('txtAmbulanceCount').textContent = visibleCount;
        }

        // Set User Location on Map and UI
        function setUserLocation(lat, lng, accuracy = null) {
            userLocation = { lat, lng, address: `GPS: ${lat.toFixed(4)}, ${lng.toFixed(4)}` };

            // Update UI status bar
            document.getElementById('gpsCoordsText').textContent = `Lat: ${lat.toFixed(5)} | Lng: ${lng.toFixed(5)}${accuracy ? ` (±${Math.round(accuracy)}m)` : ''}`;
            document.getElementById('gpsStatusBadge').className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1';
            document.getElementById('gpsStatusBadge').innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Device GPS Active';
            document.getElementById('gpsIconBox').className = 'w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-lg shrink-0 shadow-sm';
            document.getElementById('mapSubTitle').textContent = `Real-time GPS distances calculated from your device (${lat.toFixed(4)}, ${lng.toFixed(4)})`;

            // Reverse-geocode to get human-readable street address via OpenStreetMap Nominatim
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.display_name) {
                        const parts = data.display_name.split(',');
                        const shortAddr = parts.slice(0, 3).join(',').trim();
                        userLocation.address = shortAddr;
                        document.getElementById('gpsAddressText').textContent = `📍 ${shortAddr}`;
                    }
                })
                .catch(() => {
                    document.getElementById('gpsAddressText').textContent = `📍 Device Location (${lat.toFixed(4)}, ${lng.toFixed(4)})`;
                })
                .finally(() => {
                    updateAmbulanceDistances();
                });

            // Add or move User Marker
            const userIcon = L.divIcon({
                className: 'custom-user-marker',
                html: `
                    <div class="relative flex items-center justify-center transform -translate-x-1/2 -translate-y-1/2">
                        <span class="animate-ping absolute inline-flex h-10 w-10 rounded-full bg-cyan-400 opacity-75"></span>
                        <div class="w-9 h-9 rounded-full bg-cyan-600 border-2 border-white shadow-xl flex items-center justify-center text-white text-sm font-bold ring-4 ring-cyan-400/30">
                            <i class="fa-solid fa-person text-base"></i>
                        </div>
                    </div>
                `,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });

            if (userMarker) {
                userMarker.setLatLng([lat, lng]);
            } else {
                userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                userMarker.bindPopup(`
                    <div class="p-2 text-slate-800 text-center">
                        <div class="font-extrabold text-sm text-cyan-600">Your Current Device Location</div>
                        <div class="text-xs text-slate-500 mt-1 font-mono">${lat.toFixed(5)}, ${lng.toFixed(5)}</div>
                        <div class="mt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Dispatches will navigate directly here</div>
                    </div>
                `);
            }

            // Fly to user position and fit bounds with closest ambulances
            const validPoints = [[lat, lng]];
            ambulancesData.forEach(amb => {
                if (amb.current_latitude && amb.current_longitude) {
                    validPoints.push([amb.current_latitude, amb.current_longitude]);
                }
            });

            if (validPoints.length > 1) {
                map.fitBounds(validPoints, { padding: [50, 50], maxZoom: 15 });
            } else {
                map.flyTo([lat, lng], 14);
            }

            updateAmbulanceDistances();
        }

        // Detect GPS Position function
        function detectDeviceLocation() {
            const btn = document.getElementById('btnDetectGps');
            const btnText = document.getElementById('btnDetectGpsText');

            if (!navigator.geolocation) {
                document.getElementById('gpsAddressText').textContent = 'Geolocation is not supported by your browser.';
                return;
            }

            btnText.textContent = 'Acquiring GPS...';
            btn.disabled = true;

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    setUserLocation(pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy);
                    btnText.textContent = 'Update Location';
                    btn.disabled = false;
                },
                function (err) {
                    console.warn('Geolocation warning:', err.message);
                    document.getElementById('gpsStatusBadge').className = 'px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider bg-amber-100 text-amber-800 border border-amber-300 flex items-center gap-1';
                    document.getElementById('gpsStatusBadge').innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> GPS Permission Needed';
                    document.getElementById('gpsAddressText').textContent = 'Please allow location permission or click map to set location.';
                    btnText.textContent = 'Retry Detect GPS';
                    btn.disabled = false;
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }

        // Automatic GPS detection on page load
        detectDeviceLocation();

        // Detect GPS Button click
        document.getElementById('btnDetectGps').addEventListener('click', detectDeviceLocation);

        // Click on map to set user location if desired
        map.on('click', function (e) {
            setUserLocation(e.latlng.lat, e.latlng.lng);
        });

        // Center on User button
        document.getElementById('btnCenterUser').addEventListener('click', function () {
            if (userLocation) {
                map.flyTo([userLocation.lat, userLocation.lng], 15, { animate: true });
                if (userMarker) userMarker.openPopup();
            } else {
                detectDeviceLocation();
            }
        });

        // Recenter Hospital button
        document.getElementById('btnRecenter').addEventListener('click', function () {
            map.flyTo(hospitalCoords, 13, { animate: true });
        });

        // Deploy Ambulances Near Me (Reposition API)
        document.getElementById('btnDeployNearby').addEventListener('click', function () {
            if (!userLocation) {
                alert('Please allow device location or click on the map to pinpoint your location first.');
                return;
            }

            const btn = this;
            const origHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-cyan-400"></i> Deploying...';
            btn.disabled = true;

            fetch('{{ route('ambulance.api.reposition') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    lat: userLocation.lat,
                    lng: userLocation.lng
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Refetch latest locations via API
                    fetch(`{{ route('ambulance.api.locations') }}?lat=${userLocation.lat}&lng=${userLocation.lng}`)
                        .then(r => r.json())
                        .then(locData => {
                            ambulancesData = locData.ambulances;
                            renderAmbulanceMarkers();
                            updateAmbulanceDistances();
                            alert(data.message);
                        });
                } else {
                    alert(data.message || 'Could not deploy ambulances.');
                }
            })
            .catch(err => {
                alert('Error deploying fleet: ' + err.message);
            })
            .finally(() => {
                btn.innerHTML = origHtml;
                btn.disabled = false;
            });
        });

        // Event Listeners for Filters & Search
        document.getElementById('ambulanceSearch').addEventListener('input', filterAndSortCards);
        document.getElementById('radiusFilter').addEventListener('change', filterAndSortCards);
        document.getElementById('sortFilter').addEventListener('change', filterAndSortCards);
        document.getElementById('statusFilter').addEventListener('change', filterAndSortCards);
        document.getElementById('typeFilter').addEventListener('change', filterAndSortCards);

        document.getElementById('btnClearFilters').addEventListener('click', function () {
            document.getElementById('ambulanceSearch').value = '';
            document.getElementById('radiusFilter').value = '';
            document.getElementById('sortFilter').value = 'distance';
            document.getElementById('statusFilter').value = '';
            document.getElementById('typeFilter').value = '';
            filterAndSortCards();
        });
    });
</script>
@endsection
