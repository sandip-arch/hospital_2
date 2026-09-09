@extends('layouts.app')

@section('title', 'Ambulance Driver Roster & Shifts')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-cyan-500/10 text-cyan-700 border border-cyan-200 text-[11px] font-extrabold uppercase tracking-wider mb-1">
                <i class="fa-solid fa-id-card"></i> Personnel Management
            </div>
            <h1 class="text-2xl font-heading font-extrabold text-slate-900">
                Ambulance Driver Roster
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage certified ambulance operators, emergency response licenses, and shift statuses.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ambulances.index') }}" class="px-4 py-2.5 rounded-2xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs shadow-sm flex items-center gap-2 transition">
                <i class="fa-solid fa-truck-medical text-cyan-600"></i>
                <span>Back to Fleet</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-sm">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Drivers Table (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl shadow-soft border border-slate-200 overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
                    Active Drivers ({{ $drivers->total() }})
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                        <tr>
                            <th class="py-4 px-6">Driver Name</th>
                            <th class="py-4 px-6">License Number</th>
                            <th class="py-4 px-6">Contact Number</th>
                            <th class="py-4 px-6">Assigned Vehicle</th>
                            <th class="py-4 px-6">Duty Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @forelse($drivers as $driver)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold text-xs">
                                        {{ substr($driver->user?->name ?? 'D', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900">{{ $driver->user?->name ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $driver->user?->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-6 font-mono font-semibold text-slate-700">
                                {{ $driver->license_number }}
                            </td>
                            <td class="py-4 px-6 font-mono text-slate-700">
                                {{ $driver->contact_number }}
                            </td>
                            <td class="py-4 px-6">
                                @if($driver->ambulance)
                                <span class="font-bold text-slate-800">{{ $driver->ambulance->vehicle_number }}</span>
                                <div class="text-[10px] text-slate-400">{{ $driver->ambulance->model }}</div>
                                @else
                                <span class="text-slate-400 italic text-[11px]">Unassigned</span>
                                @endif
                            </td>
                            <td class="py-4 px-6">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $driver->statusBadge() }}">
                                    {{ $driver->status === 'on_duty' ? 'On Duty' : 'Off Duty' }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No drivers registered.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($drivers->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $drivers->links() }}
            </div>
            @endif
        </div>

        <!-- Add Driver Card (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl shadow-soft border border-slate-200 p-6 space-y-4">
            <div>
                <h3 class="text-sm font-extrabold text-slate-900">Register Driver</h3>
                <p class="text-xs text-slate-500 mt-0.5">Link an existing staff/user account to a certified ambulance driver license.</p>
            </div>

            <form action="{{ route('admin.ambulances.drivers.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Select User Account <span class="text-rose-500">*</span>
                    </label>
                    <select name="user_id" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Choose User --</option>
                        @foreach($candidateUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Emergency CDL / License Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="license_number" placeholder="e.g. MA-CDL-984120" required
                           class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 uppercase font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Contact Dispatch Phone <span class="text-rose-500">*</span>
                    </label>
                    <input type="tel" name="contact_number" placeholder="e.g. +1 (555) 301-8899" required
                           class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Shift Status <span class="text-rose-500">*</span>
                    </label>
                    <select name="status" required class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="on_duty">On Duty</option>
                        <option value="off_duty">Off Duty</option>
                    </select>
                </div>

                <button type="submit" class="w-full py-3 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white font-extrabold text-xs shadow-md shadow-cyan-600/20 transition">
                    Register Driver
                </button>
            </form>
        </div>

    </div>

</div>
@endsection
