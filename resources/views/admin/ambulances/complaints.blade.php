@extends('layouts.app')

@section('title', 'Ambulance Complaints & Defect Reports')
@section('header_title', 'Ambulance Maintenance & Defect Reports')
@section('header_subtitle', 'Fleet issue tracking, mechanical fault reports, and maintenance resolution audits')

@section('content')
<div class="space-y-6" x-data="{ showSubmitModal: false, activeEditComplaint: null }">

    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2 text-xs">
            <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-cyan-600 font-bold transition flex items-center gap-1">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
            @if(Auth::user()->isAdmin())
            <span class="text-slate-300">/</span>
            <a href="{{ route('admin.ambulances.index') }}" class="text-slate-500 hover:text-cyan-600 font-bold transition">
                Ambulance Fleet
            </a>
            @endif
        </div>

        <button @click="showSubmitModal = true" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs rounded-2xl shadow-md transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-triangle-exclamation"></i> File New Ambulance Complaint
        </button>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm mb-2">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="text-2xl font-black text-slate-900">{{ $stats['total'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Total Reports</div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm mb-2">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="text-2xl font-black text-amber-600">{{ $stats['pending'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Pending Review</div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-sm mb-2">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <div class="text-2xl font-black text-cyan-600">{{ $stats['in_investigation'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Under Investigation</div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs">
            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm mb-2">
                <i class="fa-solid fa-screwdriver-wrench"></i>
            </div>
            <div class="text-2xl font-black text-purple-600">{{ $stats['in_maintenance'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">In Repair</div>
        </div>

        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-xs col-span-2 sm:col-span-1">
            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm mb-2">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="text-2xl font-black text-emerald-600">{{ $stats['resolved'] }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Resolved</div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl p-4 lg:p-6 border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('ambulance.complaints.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-xs">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vehicle or keyword..."
                   class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold">

            <select name="status" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold">
                <option value="">All Statuses</option>
                <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Pending Review</option>
                <option value="under_investigation" {{ request('status') === 'under_investigation' ? 'selected' : '' }}>Under Investigation</option>
                <option value="in_maintenance" {{ request('status') === 'in_maintenance' ? 'selected' : '' }}>In Maintenance</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <select name="priority" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold">
                <option value="">All Priorities</option>
                <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
            </select>

            <select name="category" onchange="this.form.submit()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold">
                <option value="">All Categories</option>
                <option value="mechanical" {{ request('category') === 'mechanical' ? 'selected' : '' }}>Mechanical</option>
                <option value="electrical" {{ request('category') === 'electrical' ? 'selected' : '' }}>Electrical</option>
                <option value="medical_equipment" {{ request('category') === 'medical_equipment' ? 'selected' : '' }}>Medical Gear</option>
                <option value="tyres_brakes" {{ request('category') === 'tyres_brakes' ? 'selected' : '' }}>Tyres & Brakes</option>
                <option value="air_conditioning" {{ request('category') === 'air_conditioning' ? 'selected' : '' }}>HVAC Climate</option>
                <option value="cleanliness" {{ request('category') === 'cleanliness' ? 'selected' : '' }}>Sanitation</option>
                <option value="other" {{ request('category') === 'other' ? 'selected' : '' }}>Other</option>
            </select>

            <div class="flex items-center gap-2">
                <button type="submit" class="w-full py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl transition">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status', 'priority', 'category', 'ambulance_id']))
                <a href="{{ route('ambulance.complaints.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Complaints Register Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <i class="fa-solid fa-list-check text-cyan-600"></i>
                <span>Complaints Register</span>
                <span class="text-xs text-slate-400 font-normal">({{ $complaints->total() }} reports)</span>
            </h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Ambulance</th>
                        <th class="py-4 px-6">Reported By</th>
                        <th class="py-4 px-6">Complaint & Details</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Priority</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Resolution Notes</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($complaints as $comp)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold font-mono text-slate-900 text-sm block">{{ $comp->ambulance?->vehicle_number ?? 'N/A' }}</span>
                            <span class="text-[11px] text-slate-500">{{ $comp->ambulance?->model }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-800 block">{{ $comp->driver?->user?->name ?? 'Driver' }}</span>
                            <span class="text-[10px] text-slate-400 font-mono block">Lic: {{ $comp->driver?->license_number }}</span>
                        </td>
                        <td class="py-4 px-6 max-w-xs">
                            <div class="font-bold text-slate-900">{{ $comp->title }}</div>
                            <div class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $comp->description }}</div>
                            @if($comp->odometer_reading)
                            <div class="text-[10px] text-slate-400 font-mono mt-1">Odo: {{ number_format($comp->odometer_reading) }} km</div>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-semibold text-slate-700">
                            {{ $comp->categoryLabel() }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] uppercase font-bold border {{ $comp->priorityBadge() }}">
                                {{ $comp->priority }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $comp->statusBadge() }}">
                                {{ $comp->statusLabel() }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-[11px] max-w-xs">
                            @if($comp->admin_notes)
                                <div class="text-slate-800 font-medium italic">"{{ $comp->admin_notes }}"</div>
                                @if($comp->resolver)
                                <div class="text-[10px] text-slate-400 mt-0.5">&mdash; {{ $comp->resolver->name }} ({{ $comp->resolved_at ? $comp->resolved_at->format('M d') : '' }})</div>
                                @endif
                            @else
                                <span class="text-slate-400 italic">No notes recorded</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            @if(Auth::user()->isAdmin())
                            <button type="button" @click="activeEditComplaint = {{ json_encode($comp) }}" class="px-2.5 py-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs transition inline-flex items-center gap-1">
                                <i class="fa-solid fa-pen-to-square"></i> Review / Update
                            </button>
                            @endif

                            @if(Auth::user()->isAdmin() || ($comp->driver?->user_id === Auth::id() && $comp->status === 'submitted'))
                            <form action="{{ route('ambulance.complaints.destroy', $comp->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this complaint?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-xl hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition" title="Delete">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-clipboard-check text-4xl mb-3 text-slate-300"></i>
                            <p class="text-xs font-bold text-slate-700">No ambulance complaints found.</p>
                            <p class="text-[11px] text-slate-400 mt-1">No reported vehicle faults match your query criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($complaints->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $complaints->links() }}
        </div>
        @endif
    </div>

    <!-- Modal: File New Complaint -->
    <div x-show="showSubmitModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200" @click.outside="showSubmitModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-wrench"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">File Ambulance Complaint</h4>
                        <p class="text-xs text-slate-500">Report vehicle defect, gear failure or maintenance issue</p>
                    </div>
                </div>
                <button type="button" @click="showSubmitModal = false" class="text-slate-400 hover:text-slate-600 p-2 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('ambulance.complaints.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Emergency Vehicle *</label>
                        <select name="ambulance_id" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            @foreach($fleetAmbulances as $amb)
                            <option value="{{ $amb->id }}" {{ $assignedAmbulance && $assignedAmbulance->id === $amb->id ? 'selected' : '' }}>
                                {{ $amb->vehicle_number }} - {{ $amb->model }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Category *</label>
                        <select name="category" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="mechanical">Mechanical Engine / Transmission</option>
                            <option value="electrical">Electrical / Battery / Siren Lights</option>
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
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Priority / Urgency *</label>
                        <select name="priority" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="medium">Medium - Scheduled Repair Needed</option>
                            <option value="high">High - Immediate Fleet Attention</option>
                            <option value="critical">Critical - Unit Grounded / Safety Risk</option>
                            <option value="low">Low - Minor Non-urgent Cosmetic</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">Odometer (km)</label>
                        <input type="number" name="odometer_reading" placeholder="e.g. 52400" min="0"
                               class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Summary / Title *</label>
                    <input type="text" name="title" required placeholder="e.g. Brake warning light illuminated during patient transfer"
                           class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Defect Description *</label>
                    <textarea name="description" rows="3" required placeholder="Detail specific symptoms, operating conditions, or failed equipment..."
                              class="w-full text-xs px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-amber-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <button type="button" @click="showSubmitModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-paper-plane"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Admin Review & Status Update -->
    <div x-show="activeEditComplaint !== null" class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-6 shadow-2xl border border-slate-200" @click.outside="activeEditComplaint = null">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-base">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">Update Complaint Status</h4>
                        <p class="text-xs text-slate-500" x-text="activeEditComplaint ? activeEditComplaint.title : ''"></p>
                    </div>
                </div>
                <button type="button" @click="activeEditComplaint = null" class="text-slate-400 hover:text-slate-600 p-2 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form :action="'{{ url('ambulance-complaints') }}/' + (activeEditComplaint ? activeEditComplaint.id : '')" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Workflow Status *</label>
                    <select name="status" x-model="activeEditComplaint.status" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500">
                        <option value="submitted">Pending Review (Submitted)</option>
                        <option value="under_investigation">Under Investigation</option>
                        <option value="in_maintenance">In Maintenance / Workshop</option>
                        <option value="resolved">Resolved & Cleared for Dispatch</option>
                        <option value="closed">Closed / Dismissed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Maintenance & Resolution Notes</label>
                    <textarea name="admin_notes" rows="4" x-model="activeEditComplaint.admin_notes" placeholder="Describe workshop actions, parts replaced, technician sign-off, or operational clearance..."
                              class="w-full text-xs px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div class="pt-3 flex items-center justify-end gap-3">
                    <button type="button" @click="activeEditComplaint = null" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk"></i> Save & Notify Driver
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
