@extends('layouts.app')

@section('title', 'Inpatient Admissions Registry')
@section('header_title', 'Inpatient Admissions (ADT)')
@section('header_subtitle', 'Patient hospital admissions, ward bed allocations, length of stay, and discharge summaries')

@section('content')
<div class="space-y-6">

    <!-- Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('facilities.admissions') }}" method="GET" class="flex items-center gap-3">
            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="">All Admission Statuses</option>
                <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Currently Inpatient (Admitted)</option>
                <option value="discharged" {{ request('status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                <option value="transferred" {{ request('status') == 'transferred' ? 'selected' : '' }}>Transferred</option>
            </select>
        </form>

        <a href="{{ route('facilities.bed-tracker') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-bed"></i> Live Bed Board
        </a>
    </div>

    <!-- Admissions Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Encounter #</th>
                        <th class="py-4 px-6">Patient</th>
                        <th class="py-4 px-6">Room & Bed</th>
                        <th class="py-4 px-6">Admission Date</th>
                        <th class="py-4 px-6">Attending Physician</th>
                        <th class="py-4 px-6">Length of Stay</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($admissions as $adm)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 font-mono font-bold text-slate-900">#ADM-{{ $adm->id }}</td>
                        <td class="py-4 px-6">
                            <a href="{{ route('patients.show', $adm->patient_id) }}" class="font-bold text-slate-900 hover:text-cyan-600 text-sm block">
                                {{ $adm->patient->full_name }}
                            </a>
                            <span class="font-mono text-[10px] text-slate-400 font-semibold">{{ $adm->patient->patient_code }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-800 block">Room {{ $adm->bed->room->room_number }} ({{ $adm->bed->room->room_type }})</span>
                            <span class="text-[10px] text-slate-500">Bed #{{ $adm->bed->bed_number }} &bull; {{ $adm->bed->room->department->name ?? 'General' }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">
                            {{ $adm->admission_date->format('M d, Y - h:i A') }}
                            @if($adm->discharge_date)
                            <span class="text-[10px] text-slate-400 block">Discharged: {{ $adm->discharge_date->format('M d, Y') }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">{{ $adm->doctor->full_name }}</td>
                        <td class="py-4 px-6 font-bold text-slate-900">{{ $adm->stay_days }} Day(s)</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $adm->status_badge }}">
                                {{ ucfirst($adm->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right">
                            @if($adm->status === 'admitted' && Auth::user()->canDischargeAdmission($adm))
                            <form action="{{ route('facilities.discharge', $adm->id) }}" method="POST" onsubmit="return confirm('Confirm discharge for {{ addslashes($adm->patient->full_name) }}?');" class="inline-block">
                                @csrf
                                <input type="hidden" name="discharge_notes" value="Discharged from inpatient registry.">
                                <input type="hidden" name="generate_invoice" value="1">
                                <button type="submit" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-xl text-[11px] font-bold shadow-xs transition inline-flex items-center gap-1">
                                    <i class="fa-solid fa-person-walking-arrow-right text-[10px]"></i> Discharge
                                </button>
                            </form>
                            @elseif($adm->discharge_date)
                            <span class="text-[11px] text-slate-400 font-semibold">Completed</span>
                            @else
                            <span class="text-[11px] text-slate-400">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-bed-pulse text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No inpatient admission records found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admissions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $admissions->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
