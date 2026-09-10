@extends('layouts.app')

@section('title', 'Ambulance Driver & Health Portal')
@section('header_title', 'Driver Operations & Health Portal')
@section('header_subtitle', 'Vehicle defect tracking, assigned ambulance telemetry, and personal medical health records')

@section('content')
<div class="space-y-8" x-data="{ showComplaintModal: false }">

    <!-- Virtual Driver & Patient Identity Card -->
    <div class="bg-gradient-to-tr from-slate-900 via-slate-950 to-cyan-950 rounded-3xl p-6 lg:p-8 text-white shadow-2xl border border-cyan-500/20 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-0">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-cyan-500 to-teal-600 flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-cyan-500/30 shrink-0">
                    <i class="fa-solid fa-truck-medical"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-2xl font-black">{{ Auth::user()->name }}</h3>
                        <span class="px-3 py-1 bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 rounded-full text-xs font-mono font-bold flex items-center gap-1.5">
                            <i class="fa-solid fa-id-card"></i> CDL: {{ $driver->license_number }}
                        </span>
                        <span class="px-3 py-1 {{ $driver->isOnDuty() ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-500/20 text-slate-300 border-slate-500/40' }} border rounded-full text-xs font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $driver->isOnDuty() ? 'bg-emerald-400 animate-pulse' : 'bg-slate-400' }}"></span>
                            {{ $driver->isOnDuty() ? 'On Duty' : 'Off Duty' }}
                        </span>
                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 rounded-full text-xs font-mono font-bold flex items-center gap-1.5" title="Use this username to sign in">
                            <i class="fa-solid fa-at text-cyan-400 text-[11px]"></i>Sign-in Username: <strong class="text-white font-mono font-bold">{{ Auth::user()->username }}</strong>
                        </span>
                        <span class="px-3 py-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full text-xs font-mono font-bold">
                            Patient UPI: {{ $patient->patient_code }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 mt-2">
                        <span><i class="fa-solid fa-phone text-cyan-400 mr-1"></i> {{ $driver->contact_number }}</span>
                        <span><i class="fa-solid fa-envelope text-cyan-400 mr-1"></i> {{ Auth::user()->email }}</span>
                        <span><i class="fa-solid fa-truck-front text-cyan-400 mr-1"></i> Assigned Unit: <strong class="text-white">{{ $assignedAmbulance ? $assignedAmbulance->vehicle_number : 'Unassigned' }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button @click="showComplaintModal = true" class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-amber-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation"></i> Report Ambulance Issue
                </button>
                <a href="{{ route('appointments.create') }}" class="px-5 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-cyan-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-calendar-plus"></i> Book Consultation
                </a>
            </div>
        </div>
    </div>

    <!-- EXTRA MODULE: Ambulance Telemetry & Maintenance Complaints -->
    <div class="space-y-6">
        
        <!-- Section Title -->
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-wrench text-amber-600"></i> Ambulance Operations & Defect Reporting Module
                </h3>
                <p class="text-xs text-slate-500">Track assigned vehicle diagnostics, report mechanical or medical equipment faults, and view repair statuses</p>
            </div>
            <button @click="showComplaintModal = true" class="text-xs font-bold text-amber-600 hover:text-amber-700 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-plus-circle"></i> Submit New Complaint
            </button>
        </div>

        <!-- Assigned Ambulance Unit & Stats Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Assigned Vehicle Telemetry Card -->
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 to-slate-950 rounded-3xl p-6 text-white border border-slate-800 shadow-md flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                            Current Emergency Vehicle
                        </span>
                        @if($assignedAmbulance)
                        <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $assignedAmbulance->statusBadge() }}">
                            {{ $assignedAmbulance->statusLabel() }}
                        </span>
                        @endif
                    </div>

                    @if($assignedAmbulance)
                    <div class="space-y-3">
                        <div class="flex items-baseline gap-3">
                            <h4 class="text-2xl font-extrabold font-mono text-cyan-400">{{ $assignedAmbulance->vehicle_number }}</h4>
                            <span class="text-xs text-slate-300">{{ $assignedAmbulance->model }}</span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-800/60 border border-slate-700/60 space-y-2 text-xs">
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400">Classification:</span>
                                <span class="font-bold text-white">{{ $assignedAmbulance->typeDisplay() }}</span>
                            </div>
                            <div class="flex items-center justify-between text-slate-300">
                                <span class="text-slate-400">Attending Doctor:</span>
                                <span class="font-bold text-cyan-300">
                                    {{ $assignedAmbulance->assignedDoctor ? 'Dr. ' . $assignedAmbulance->assignedDoctor->user?->name : 'None Assigned' }}
                                </span>
                            </div>
                            @if($assignedAmbulance->assignedDoctor)
                            <div class="flex items-center justify-between text-slate-400 text-[11px]">
                                <span>Specialization:</span>
                                <span>{{ $assignedAmbulance->assignedDoctor->specialization }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="py-6 text-center text-slate-400">
                        <i class="fa-solid fa-truck-ramp-box text-3xl mb-2 text-slate-600"></i>
                        <p class="text-xs font-semibold text-slate-300">No Dedicated Ambulance Assigned Yet</p>
                        <p class="text-[11px] text-slate-500 mt-1">Contact hospital fleet dispatch or select any fleet vehicle when filing maintenance reports.</p>
                    </div>
                    @endif
                </div>

                <div class="pt-4 mt-4 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-satellite-dish text-emerald-400 animate-pulse"></i> Telemetry Active
                    </span>
                    <a href="{{ route('ambulance.index') }}" class="text-cyan-400 hover:underline font-bold text-[11px] flex items-center gap-1">
                        Fleet Radar Map <i class="fa-solid fa-arrow-right text-[9px]"></i>
                    </a>
                </div>
            </div>

            <!-- Complaints Metrics -->
            <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-base mb-3">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-slate-900">{{ $complaintStats['total'] }}</div>
                        <div class="text-[11px] font-bold text-slate-500 mt-0.5">Total Reports</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base mb-3">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-amber-600">{{ $complaintStats['pending'] }}</div>
                        <div class="text-[11px] font-bold text-slate-500 mt-0.5">Pending Review</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-base mb-3">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-purple-600">{{ $complaintStats['in_maintenance'] }}</div>
                        <div class="text-[11px] font-bold text-slate-500 mt-0.5">In Repair</div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base mb-3">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="text-2xl font-black text-emerald-600">{{ $complaintStats['resolved'] }}</div>
                        <div class="text-[11px] font-bold text-slate-500 mt-0.5">Resolved</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Complaints Table -->
        <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-clock-rotate-left text-amber-600"></i> My Reported Ambulance Complaints
                    </h4>
                    <p class="text-xs text-slate-500">Live progress tracking of reported mechanical and clinical cabin issues</p>
                </div>
                <a href="{{ route('ambulance.complaints.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">
                    View Full Complaints Register &rarr;
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-4">Vehicle</th>
                            <th class="py-3 px-4">Complaint / Fault</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Priority</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Admin Resolution / Notes</th>
                            <th class="py-3 px-4 text-right">Filed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($recentComplaints as $comp)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                {{ $comp->ambulance?->vehicle_number ?? 'Fleet Unit' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-800 block">{{ $comp->title }}</span>
                                <span class="text-[11px] text-slate-500 line-clamp-1">{{ $comp->description }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-[11px] text-slate-700 font-semibold">{{ $comp->categoryLabel() }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold border {{ $comp->priorityBadge() }}">
                                    {{ $comp->priority }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $comp->statusBadge() }}">
                                    {{ $comp->statusLabel() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[11px]">
                                @if($comp->admin_notes)
                                    <span class="text-slate-800 font-semibold italic">"{{ $comp->admin_notes }}"</span>
                                    @if($comp->resolver)
                                    <span class="text-[10px] text-slate-400 block">&mdash; {{ $comp->resolver->name }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Pending inspection</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right text-[11px] text-slate-400 whitespace-nowrap">
                                {{ $comp->created_at->diffForHumans() }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <i class="fa-solid fa-shield-check text-3xl mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-600">No active complaints filed.</p>
                                <p class="text-[11px] text-slate-400 mt-0.5">Click "Report Ambulance Issue" if your emergency unit needs maintenance.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal to Report Ambulance Issue / Defect -->
    <div x-show="showComplaintModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200" @click.outside="showComplaintModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-wrench"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">Report Ambulance Issue</h4>
                        <p class="text-xs text-slate-500">File an urgent mechanical, electrical, or medical gear defect</p>
                    </div>
                </div>
                <button type="button" @click="showComplaintModal = false" class="text-slate-400 hover:text-slate-600 p-2 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('ambulance.complaints.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Vehicle Unit *</label>
                        <select name="ambulance_id" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            @foreach($fleetAmbulances as $amb)
                            <option value="{{ $amb->id }}" {{ $assignedAmbulance && $assignedAmbulance->id === $amb->id ? 'selected' : '' }}>
                                {{ $amb->vehicle_number }} - {{ $amb->model }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Issue Category *</label>
                        <select name="category" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="mechanical">Mechanical Engine / Transmission</option>
                            <option value="electrical">Electrical / Battery / Emergency Lights</option>
                            <option value="medical_equipment">Onboard Medical Gear / Defibrillator / O2</option>
                            <option value="tyres_brakes">Tyres, Suspension & Braking</option>
                            <option value="air_conditioning">HVAC / Patient Cabin Climate</option>
                            <option value="fuel_oil">Fuel, Coolant & Lubrication</option>
                            <option value="cleanliness">Cabin Sterility & Sanitation</option>
                            <option value="other">Other Operational Issue</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Urgency / Priority *</label>
                        <select name="priority" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="medium">Medium - Scheduled Repair Needed</option>
                            <option value="high">High - Immediate Fleet Attention</option>
                            <option value="critical">Critical - Unit Grounded / Unsafe</option>
                            <option value="low">Low - Minor Non-urgent Cosmetic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Odometer Reading (km)</label>
                        <input type="number" name="odometer_reading" placeholder="e.g. 48520" min="0"
                               class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Complaint Title / Summary *</label>
                    <input type="text" name="title" required placeholder="e.g. Oxygen pressure regulator fluctuating rapidly in patient bay"
                           class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Detailed Defect Description *</label>
                    <textarea name="description" rows="3" required placeholder="Describe specific symptoms, warning lights, unusual sounds, or equipment failure..."
                              class="w-full text-xs px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <button type="button" @click="showComplaintModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Submit Complaint
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PATIENT HEALTH PORTAL MODULES (Identical to Patient Dashboard) -->
    <div class="pt-4 border-t border-slate-200">
        <div class="mb-6">
            <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-heart-pulse text-rose-500"></i> Personal Patient Health Records & Care Center
            </h3>
            <p class="text-xs text-slate-500">Your personal outpatient clinical records, upcoming appointments, prescriptions, and billing receipts</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            <!-- Left: Upcoming Appointments & Active Prescriptions -->
            <div class="lg:col-span-8 space-y-8">

                <!-- Upcoming Appointments -->
                <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                                <i class="fa-solid fa-calendar-check text-cyan-600"></i> My Upcoming Consultations
                            </h4>
                            <p class="text-xs text-slate-500">Scheduled clinical appointments</p>
                        </div>
                        <a href="{{ route('appointments.create') }}" class="text-xs font-bold text-cyan-600 hover:underline">+ New Visit</a>
                    </div>

                    <div class="space-y-4">
                        @forelse($upcomingAppointments as $apt)
                        <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-800 font-black text-sm flex items-center justify-center shrink-0">
                                    {{ $apt->appointment_date->format('d M') }}
                                </div>
                                <div>
                                    <h5 class="font-bold text-slate-900 text-sm">{{ $apt->doctor->full_name }}</h5>
                                    <p class="text-xs text-cyan-600 font-semibold mt-0.5">{{ $apt->department->name ?? 'Specialist' }} &bull; Slot: {{ $apt->time_slot }}</p>
                                    <p class="text-xs text-slate-500 mt-1">Reason: {{ $apt->reason ?? 'General Medical Visit' }}</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $apt->status_badge }}">
                                {{ ucfirst($apt->status) }}
                            </span>
                        </div>
                        @empty
                        <div class="p-8 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-xmark text-3xl mb-2 text-slate-300"></i>
                            <p class="text-xs font-semibold text-slate-600">You have no upcoming appointments.</p>
                            <a href="{{ route('appointments.create') }}" class="mt-2 inline-block text-xs font-bold text-cyan-600 hover:underline">Schedule one now &rarr;</a>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Active e-Prescriptions -->
                <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                        <div>
                            <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                                <i class="fa-solid fa-prescription text-emerald-600"></i> My Medical Prescriptions
                            </h4>
                            <p class="text-xs text-slate-500">Doctor issued medication guidelines</p>
                        </div>
                        <a href="{{ route('prescriptions.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">All Prescriptions</a>
                    </div>

                    <div class="space-y-4">
                        @forelse($prescriptions as $rx)
                        <div class="p-5 rounded-2xl border border-slate-200/80 bg-white hover:border-emerald-500/50 transition">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900 text-sm">Rx #{{ $rx->id }}</span>
                                    <span class="text-xs text-slate-400">by {{ $rx->doctor->full_name }} ({{ $rx->prescribed_date->format('M d, Y') }})</span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $rx->status_badge }}">
                                    {{ ucfirst($rx->status) }}
                                </span>
                            </div>

                            <div class="divide-y divide-slate-100 text-xs bg-slate-50 rounded-xl p-3">
                                @foreach($rx->items as $item)
                                <div class="py-2 flex items-center justify-between">
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $item->medicine->name ?? 'Medicine' }}</span>
                                        <span class="text-slate-500 font-mono ml-2">({{ $item->dosage }})</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $item->frequency }} &bull; {{ $item->duration_days }} Days &bull; {{ $item->instructions }}</p>
                                    </div>
                                    <span class="font-bold text-slate-700">Qty: {{ $item->quantity_prescribed }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 py-6 text-center">No prescriptions on record.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Right: Published Lab Results & Invoices / Online Pay -->
            <div class="lg:col-span-4 space-y-6">

                <!-- Published Diagnostics -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-flask-vial text-purple-600"></i> My Diagnostic Reports
                        </h4>
                        <a href="{{ route('lab.requests') }}" class="text-xs font-bold text-purple-600 hover:underline">All Tests</a>
                    </div>

                    <div class="space-y-3 text-xs">
                        @forelse($labReports as $lab)
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-slate-800">{{ $lab->test->test_name }}</p>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lab->status_badge }}">
                                    {{ ucfirst($lab->status) }}
                                </span>
                            </div>
                            @if($lab->status === 'completed')
                            <p class="text-[11px] text-slate-600 mt-1 line-clamp-2">{{ $lab->result_summary }}</p>
                            <div class="mt-2 text-right">
                                <a href="{{ route('lab.reports.show', $lab->id) }}" class="text-xs font-bold text-purple-600 hover:underline">
                                    View Official Report &rarr;
                                </a>
                            </div>
                            @else
                            <p class="text-[10px] text-amber-600 mt-1 font-semibold">Processing sample in laboratory...</p>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No diagnostic reports on record.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Billing & Direct Payment -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-cyan-600"></i> Invoices & Payments
                        </h4>
                        <a href="{{ route('billing.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">All Bills</a>
                    </div>

                    <div class="space-y-3 text-xs">
                        @forelse($invoices as $inv)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-500">
                                <span>Total: ${{ number_format($inv->net_amount, 2) }}</span>
                                <span>Due: <strong class="text-rose-600">${{ number_format($inv->balance_due, 2) }}</strong></span>
                            </div>

                            <div class="pt-1 flex items-center justify-end gap-2">
                                <a href="{{ route('billing.show', $inv->id) }}" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                                    View
                                </a>
                                @if($inv->balance_due > 0)
                                <a href="{{ route('billing.show', $inv->id) }}" class="px-3 py-1 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-[11px] font-bold shadow-sm">
                                    Pay Online
                                </a>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400 py-4 text-center">No invoices issued.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
