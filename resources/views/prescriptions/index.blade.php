@extends('layouts.app')

@section('title', Auth::user()->isPatient() ? 'My Electronic Prescriptions (e-Rx)' : 'Electronic Prescriptions (e-Rx)')
@section('header_title', Auth::user()->isPatient() ? 'My Medical Prescriptions' : 'e-Prescriptions Directory')
@section('header_subtitle', Auth::user()->isPatient() ? 'Your doctor-issued medication regimens, dosage schedules, and dispensing status' : 'Digital medicine orders, dosage regimens, duration, and pharmacy fulfillment')

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('prescriptions.index') }}" method="GET" class="flex items-center gap-3">
            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="">All Prescription Statuses</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active (Pending Dispense)</option>
                <option value="dispensed" {{ request('status') == 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </form>

        @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
        <a href="{{ route('prescriptions.create') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-prescription"></i> Issue e-Prescription
        </a>
        @endif
    </div>

    <!-- Prescriptions Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($prescriptions as $rx)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-500/50 transition flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-bold text-slate-900 bg-slate-100 px-2.5 py-0.5 rounded-lg">
                        Rx #{{ $rx->id }}
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rx->status_badge }}">
                        {{ ucfirst($rx->status) }}
                    </span>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 text-base">
                        <a href="{{ route('prescriptions.show', $rx->id) }}" class="hover:text-emerald-600 transition">
                            @if(Auth::user()->isPatient())
                            Prescription from Dr. {{ $rx->doctor->full_name }}
                            @else
                            {{ $rx->patient->full_name }}
                            @endif
                        </a>
                    </h4>
                    @if(!Auth::user()->isPatient())
                    <p class="text-xs text-slate-500">UPI: <span class="font-mono font-bold">{{ $rx->patient->patient_code }}</span></p>
                    @else
                    <p class="text-xs text-slate-500">{{ $rx->doctor->department->name ?? 'Clinical Specialist' }}</p>
                    @endif
                </div>

                <p class="text-[11px] text-slate-600">Prescribed: <strong class="text-slate-800">{{ $rx->prescribed_date->format('M d, Y') }}</strong></p>

                <!-- Drug Items Summary -->
                <div class="bg-slate-50 rounded-2xl p-3 divide-y divide-slate-100 text-xs">
                    @foreach($rx->items as $item)
                    <div class="py-1.5 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800">{{ $item->medicine->name ?? 'Medicine' }}</span>
                            <span class="text-[11px] text-slate-500 ml-1">({{ $item->dosage }})</span>
                            <p class="text-[10px] text-slate-400">{{ $item->frequency }} &bull; {{ $item->duration_days }}d</p>
                        </div>
                        <span class="font-bold text-slate-700 text-xs">x{{ $item->quantity_prescribed }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('prescriptions.show', $rx->id) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    View Details &rarr;
                </a>
                <div class="flex items-center gap-2">
                    @if($rx->status === 'active' && (Auth::user()->isStaff() || Auth::user()->isAdmin()))
                    <form action="{{ route('prescriptions.dispense', $rx->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold hover:bg-emerald-500 shadow-xs">
                            Dispense
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('prescriptions.print', $rx->id) }}" target="_blank" class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs">
                        <i class="fa-solid fa-print"></i>
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="fa-solid fa-prescription text-4xl mb-3 text-slate-300"></i>
            <p class="font-semibold text-slate-600 text-sm">No electronic prescriptions found.</p>
        </div>
        @endforelse
    </div>

    @if($prescriptions->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
        {{ $prescriptions->links() }}
    </div>
    @endif

</div>
@endsection
