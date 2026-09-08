@extends('layouts.app')

@section('title', 'Appointment #' . $appointment->id)
@section('header_title', 'Appointment Encounter #' . $appointment->id)
@section('header_subtitle', 'Patient visit details, status, linked clinical encounters, and billing breakdown')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Appointments
        </a>
    </div>

    <!-- Appointment Overview Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-3">
                    <h3 class="text-xl font-black text-slate-900">Encounter #APT-{{ $appointment->id }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $appointment->status_badge }}">
                        {{ ucfirst($appointment->status) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">Booked on {{ $appointment->created_at->format('M d, Y - h:i A') }}</p>
            </div>

            <!-- Start Consultation Action -->
            @if($appointment->status !== 'completed' && (Auth::user()->isDoctor() || Auth::user()->isAdmin()))
            <a href="{{ route('medical-records.create', ['patient_id' => $appointment->patient_id, 'appointment_id' => $appointment->id, 'doctor_id' => $appointment->doctor_id]) }}"
               class="px-5 py-2.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-stethoscope"></i> Start Consultation
            </a>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            
            <!-- Patient Box -->
            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Patient Information</span>
                <p class="text-sm font-bold text-slate-900">{{ $appointment->patient->full_name }}</p>
                <p><strong class="text-slate-600">UPI:</strong> {{ $appointment->patient->patient_code }}</p>
                <p><strong class="text-slate-600">Phone:</strong> {{ $appointment->patient->phone }}</p>
                <p><strong class="text-slate-600">Age / Gender:</strong> {{ $appointment->patient->age }} yrs / {{ $appointment->patient->gender }}</p>
                <a href="{{ route('patients.show', $appointment->patient_id) }}" class="inline-block mt-2 text-cyan-600 font-bold hover:underline">
                    View Full 360° Dossier &rarr;
                </a>
            </div>

            <!-- Doctor Box -->
            <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Attending Physician</span>
                <p class="text-sm font-bold text-slate-900">{{ $appointment->doctor->full_name }}</p>
                <p><strong class="text-slate-600">Department:</strong> {{ $appointment->department->name ?? 'General' }}</p>
                <p><strong class="text-slate-600">Specialization:</strong> {{ $appointment->doctor->specialization }}</p>
                <p><strong class="text-slate-600">Consultation Fee:</strong> ${{ number_format($appointment->doctor->consultation_fee, 2) }}</p>
            </div>

        </div>

        <!-- Schedule & Reason -->
        <div class="p-5 bg-slate-50 rounded-2xl border border-slate-100 text-xs space-y-3">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Consultation Date</span>
                    <span class="font-bold text-slate-800 text-sm">{{ $appointment->appointment_date->format('l, F d, Y') }}</span>
                </div>
                <div>
                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Time Slot</span>
                    <span class="font-bold text-cyan-700 text-sm font-mono">{{ $appointment->time_slot }}</span>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-200/60">
                <strong class="text-slate-700 block mb-1">Reason for Consultation:</strong>
                <p class="text-slate-600 leading-relaxed">{{ $appointment->reason ?? 'None specified.' }}</p>
            </div>

            @if($appointment->doctor_notes)
            <div class="pt-2 border-t border-slate-200/60">
                <strong class="text-slate-700 block mb-1">Doctor Notes:</strong>
                <p class="text-slate-600 leading-relaxed">{{ $appointment->doctor_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Linked Invoice -->
        @if($appointment->invoice)
        <div class="p-5 bg-emerald-50/50 rounded-2xl border border-emerald-200 flex items-center justify-between text-xs">
            <div>
                <span class="text-[10px] font-bold uppercase text-emerald-800">Generated Invoice</span>
                <p class="font-mono font-bold text-slate-900 mt-0.5">{{ $appointment->invoice->invoice_number }} &bull; ${{ number_format($appointment->invoice->net_amount, 2) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded-full font-bold border {{ $appointment->invoice->status_badge }}">
                    {{ ucfirst($appointment->invoice->status) }}
                </span>
                <a href="{{ route('billing.show', $appointment->invoice->id) }}" class="px-3 py-1.5 bg-slate-900 text-white font-bold rounded-lg hover:bg-slate-800">
                    View Bill
                </a>
            </div>
        </div>
        @endif

    </div>

</div>
@endsection
