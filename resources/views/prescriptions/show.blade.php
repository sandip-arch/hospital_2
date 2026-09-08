@extends('layouts.app')

@section('title', 'Prescription #Rx-' . $prescription->id)
@section('header_title', 'Prescription #Rx-' . $prescription->id)
@section('header_subtitle', 'Patient: ' . $prescription->patient->full_name . ' • Doctor: ' . $prescription->doctor->full_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('prescriptions.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Prescriptions
        </a>
        <div class="flex items-center gap-2">
            @if($prescription->status === 'active' && (Auth::user()->isStaff() || Auth::user()->isAdmin()))
            <form action="{{ route('prescriptions.dispense', $prescription->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-check-double"></i> Dispense from Pharmacy
                </button>
            </form>
            @endif
            <a href="{{ route('prescriptions.print', $prescription->id) }}" target="_blank"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print Rx Slip
            </a>
        </div>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">Official Medical Prescription</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">Rx #{{ $prescription->id }}</h3>
                <p class="text-xs text-slate-500 mt-1">Prescribed on {{ $prescription->prescribed_date->format('l, F d, Y') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $prescription->status_badge }}">
                    {{ ucfirst($prescription->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Patient</span>
                <p class="font-bold text-slate-900 text-sm">{{ $prescription->patient->full_name }}</p>
                <p class="text-slate-500">UPI: <span class="font-mono font-bold">{{ $prescription->patient->patient_code }}</span> &bull; Age: {{ $prescription->patient->age }} yrs &bull; Gender: {{ $prescription->patient->gender }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Prescribing Doctor</span>
                <p class="font-bold text-slate-900 text-sm">{{ $prescription->doctor->full_name }}</p>
                <p class="text-slate-500">{{ $prescription->doctor->specialization }} &bull; License: {{ $prescription->doctor->license_number }}</p>
            </div>
        </div>

        <!-- Prescribed Drugs Table -->
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Itemized Medication Regimen</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Medicine & Generic Name</th>
                            <th class="py-3 px-4">Dosage</th>
                            <th class="py-3 px-4">Frequency</th>
                            <th class="py-3 px-4">Duration</th>
                            <th class="py-3 px-4">Qty</th>
                            <th class="py-3 px-4">Instructions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($prescription->items as $idx => $item)
                        <tr class="hover:bg-slate-50/60">
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-slate-900">{{ $item->medicine->name }}</span>
                                <span class="text-[11px] text-slate-500 block">({{ $item->medicine->generic_name ?? 'Generic' }})</span>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-cyan-800">{{ $item->dosage }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-700">{{ $item->frequency }}</td>
                            <td class="py-3 px-4">{{ $item->duration_days }} Days</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->quantity_prescribed }}</td>
                            <td class="py-3 px-4 text-slate-600">{{ $item->instructions ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($prescription->notes)
        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
            <strong class="text-slate-900 block mb-1">Doctor Dietary / Lifestyle Notes:</strong>
            <p class="text-slate-700 leading-relaxed">{{ $prescription->notes }}</p>
        </div>
        @endif

    </div>

</div>
@endsection
