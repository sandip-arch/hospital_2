@extends('layouts.app')

@section('title', 'Clinical Encounter - ' . $record->diagnosis)
@section('header_title', 'Clinical Encounter Record')
@section('header_subtitle', 'Patient: ' . $record->patient->full_name . ' • Date: ' . $record->visit_date->format('M d, Y'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('medical-records.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to EMR List
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('prescriptions.create', ['patient_id' => $record->patient_id, 'medical_record_id' => $record->id, 'doctor_id' => $record->doctor_id]) }}"
               class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-prescription"></i> Issue Rx for this Visit
            </a>
            <a href="{{ route('medical-records.print', $record->id) }}" target="_blank"
               class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print Summary
            </a>
        </div>
    </div>

    <!-- Encounter Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] uppercase font-bold text-cyan-600 tracking-wider">Clinical Encounter Dossier</span>
                <h3 class="text-2xl font-black text-slate-900 mt-1">{{ $record->diagnosis }}</h3>
                <p class="text-xs text-slate-500 mt-1">Visit Conducted on {{ $record->visit_date->format('l, F d, Y - h:i A') }}</p>
            </div>
            <div class="text-right">
                <span class="font-mono text-xs font-bold px-3 py-1 bg-slate-100 text-slate-700 rounded-full">
                    UPI: {{ $record->patient->patient_code }}
                </span>
            </div>
        </div>

        <!-- Participants -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Patient</span>
                <p class="font-bold text-slate-900 text-sm">{{ $record->patient->full_name }}</p>
                <p class="text-slate-500">Age: {{ $record->patient->age }} yrs &bull; Gender: {{ $record->patient->gender }} &bull; Blood: {{ $record->patient->blood_type ?? 'N/A' }}</p>
                <p class="text-slate-500">Phone: {{ $record->patient->phone }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Attending Physician</span>
                <p class="font-bold text-slate-900 text-sm">{{ $record->doctor->full_name }}</p>
                <p class="text-slate-500">{{ $record->doctor->specialization }} &bull; Dept: {{ $record->doctor->department->name ?? 'General' }}</p>
                <p class="text-slate-500">License: {{ $record->doctor->license_number }}</p>
            </div>
        </div>

        <!-- Symptoms & Notes -->
        <div class="space-y-4 text-xs">
            @if($record->symptoms)
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <strong class="text-slate-900 block mb-1">Chief Presenting Symptoms:</strong>
                <p class="text-slate-700 leading-relaxed">{{ $record->symptoms }}</p>
            </div>
            @endif

            @if($record->notes)
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                <strong class="text-slate-900 block mb-1">SOAP Clinical Notes & Assessment:</strong>
                <p class="text-slate-700 leading-relaxed whitespace-pre-line">{{ $record->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Vital Signs Grid -->
        @if($record->details->isNotEmpty())
        <div class="pt-2">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Recorded Vital Signs</h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                @foreach($record->details as $vital)
                <div class="p-3.5 bg-cyan-50/50 rounded-2xl border border-cyan-100">
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ $vital->vital_sign_name }}</span>
                    <span class="text-sm font-black text-slate-900 mt-0.5 block">{{ $vital->vital_sign_value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Linked Prescriptions -->
        @if($record->prescriptions->isNotEmpty())
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Prescribed Medications</h4>
            @foreach($record->prescriptions as $rx)
            <div class="p-4 bg-emerald-50/40 rounded-2xl border border-emerald-200 text-xs space-y-2 mb-3">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-900">Rx #{{ $rx->id }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $rx->status_badge }}">
                        {{ ucfirst($rx->status) }}
                    </span>
                </div>
                <div class="divide-y divide-emerald-100">
                    @foreach($rx->items as $item)
                    <div class="py-1.5 flex justify-between">
                        <div>
                            <span class="font-bold text-slate-800">{{ $item->medicine->name }}</span> ({{ $item->dosage }}) - {{ $item->frequency }} &bull; {{ $item->duration_days }} Days
                            <p class="text-[11px] text-slate-500">{{ $item->instructions }}</p>
                        </div>
                        <span class="font-bold text-slate-700">Qty: {{ $item->quantity_prescribed }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>

</div>
@endsection
