@extends('layouts.app')

@section('title', 'Ambulance Fleet Operations & Management')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-cyan-500/10 text-cyan-700 border border-cyan-200 text-[11px] font-extrabold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-shield-halved"></i> Superadmin & Admin Control Center
            </div>
            <h1 class="text-2xl font-heading font-extrabold text-slate-900">
                Ambulance Fleet Management
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Register, monitor telemetry, assign drivers, and maintain the hospital emergency ambulance fleet.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ambulances.drivers') }}" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <i class="fa-solid fa-id-card-clip text-cyan-600"></i>
                <span>Driver Roster ({{ $stats['total_drivers'] }})</span>
            </a>
            <a href="{{ route('admin.ambulances.create') }}" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white font-extrabold text-xs shadow-md shadow-cyan-600/20 flex items-center gap-2 transition transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus"></i>
                <span>Add Ambulance</span>
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-circle-exclamation text-base"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Fleet Status KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-6 gap-3">
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Units</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft">
            <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Available
            </div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['available'] }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft">
            <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span> Dispatched
            </div>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['dispatched'] }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft">
            <div class="text-[11px] font-bold text-cyan-600 uppercase tracking-wider flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-cyan-500"></span> In Transit
            </div>
            <div class="text-2xl font-extrabold text-cyan-600 mt-1">{{ $stats['in_transit'] }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Maintenance</div>
            <div class="text-2xl font-extrabold text-slate-700 mt-1">{{ $stats['maintenance'] }}</div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-soft col-span-2 sm:col-span-1">
            <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Active Bookings</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['active_bookings'] }}</div>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-3xl shadow-soft border border-slate-200 overflow-hidden">
        
        <!-- Table Toolbar -->
        <div class="p-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.ambulances.index') }}" class="flex flex-wrap items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Statuses</option>
                    <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>In Transit</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>

                <select name="type" onchange="this.form.submit()" class="text-xs font-semibold bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Types</option>
                    <option value="Advanced_Life_Support" {{ request('type') == 'Advanced_Life_Support' ? 'selected' : '' }}>Advanced Life Support</option>
                    <option value="Basic" {{ request('type') == 'Basic' ? 'selected' : '' }}>Basic Life Support</option>
                    <option value="Patient_Transport" {{ request('type') == 'Patient_Transport' ? 'selected' : '' }}>Patient Transport</option>
                </select>

                @if(request('status') || request('type'))
                <a href="{{ route('admin.ambulances.index') }}" class="text-xs text-rose-600 hover:underline font-bold px-2 py-1">Reset</a>
                @endif
            </form>

            <a href="{{ route('ambulance.index') }}" target="_blank" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1">
                <span>View Public Radar Map</span>
                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
            </a>
        </div>

        <!-- Ambulances Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Vehicle Plate</th>
                        <th class="py-4 px-6">Model & Specs</th>
                        <th class="py-4 px-6">Equipment Type</th>
                        <th class="py-4 px-6">Assigned Driver</th>
                        <th class="py-4 px-6">Current Status</th>
                        <th class="py-4 px-6">GPS Coordinates</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($ambulances as $amb)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-4 px-6">
                            <span class="font-extrabold text-sm text-slate-900 font-mono">{{ $amb->vehicle_number }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-800">{{ $amb->model }}</div>
                            <div class="text-[11px] text-slate-400">Added: {{ $amb->created_at ? $amb->created_at->format('M d, Y') : 'N/A' }}</div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $amb->typeBadge() }}">
                                {{ $amb->typeDisplay() }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            @if($amb->currentDriver)
                            <div class="font-bold text-slate-800">{{ $amb->currentDriver->user?->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $amb->currentDriver->contact_number }}</div>
                            @else
                            <span class="text-slate-400 italic text-[11px]">Unassigned</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-xl text-[11px] font-bold {{ $amb->statusBadge() }} shadow-sm">
                                {{ $amb->statusLabel() }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-mono text-[11px] text-slate-500">
                            @if($amb->current_latitude && $amb->current_longitude)
                            <div>{{ number_format($amb->current_latitude, 4) }}, {{ number_format($amb->current_longitude, 4) }}</div>
                            <div class="text-[10px] text-slate-400">Updated: {{ $amb->last_location_update ? $amb->last_location_update->diffForHumans() : 'N/A' }}</div>
                            @else
                            <span class="text-slate-400">No GPS Signal</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap space-x-1">
                            <a href="{{ route('admin.ambulances.edit', $amb->id) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-cyan-50 text-slate-600 hover:text-cyan-700 transition inline-block" title="Edit Ambulance">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <form action="{{ route('admin.ambulances.destroy', $amb->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to decommission this ambulance?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition" title="Decommission">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-truck-medical text-3xl mb-2 text-slate-300"></i>
                            <div class="text-sm font-semibold">No ambulances registered yet.</div>
                            <a href="{{ route('admin.ambulances.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-bold text-cyan-600 hover:underline">
                                <i class="fa-solid fa-plus"></i> Add first ambulance
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($ambulances->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $ambulances->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
