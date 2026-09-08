@extends('layouts.app')

@section('title', 'New EMR Clinical Note')
@section('header_title', 'Clinical Consultation Encounter')
@section('header_subtitle', 'Record diagnosis, symptoms, clinical SOAP observations, and vital signs')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('medical-records.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to EMR List
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('medical-records.store') }}" method="POST" class="space-y-6">
            @csrf

            @if($selectedAppointmentId)
            <input type="hidden" name="appointment_id" value="{{ $selectedAppointmentId }}">
            @endif

            <!-- Patient & Doctor -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patient *</label>
                    <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Choose Patient --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatientId) == $p->id) ? 'selected' : '' }}>
                            {{ $p->full_name }} ({{ $p->patient_code }}) - Age: {{ $p->age }} yrs
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Attending Physician *</label>
                    <select name="doctor_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" {{ (old('doctor_id', $selectedDoctorId) == $doc->id) ? 'selected' : '' }}>
                            {{ $doc->full_name }} ({{ $doc->department->name ?? 'General' }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Visit Date & Time *</label>
                <input type="datetime-local" name="visit_date" value="{{ old('visit_date', date('Y-m-d\TH:i')) }}" required
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>

            <!-- Diagnosis & Symptoms -->
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Diagnosis *</label>
                    <input type="text" name="diagnosis" value="{{ old('diagnosis') }}" required placeholder="e.g. Essential Hypertension Stage 1 / Stable Angina"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold text-slate-900">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Presenting Symptoms / Chief Complaint</label>
                    <textarea name="symptoms" rows="2" placeholder="e.g. Mild headache, chest tightness on exertion for 2 days"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">{{ old('symptoms') }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Findings & Assessment (SOAP Notes)</label>
                    <textarea name="notes" rows="4" placeholder="Subjective, Objective, Assessment, and Treatment Plan details..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- Vital Signs Grid -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-bold text-slate-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-rose-600"></i> Vital Signs & Physical Measurements
                </h4>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Blood Pressure (BP)</label>
                        <input type="text" name="vital_bp" value="{{ old('vital_bp', '120/80 mmHg') }}" placeholder="120/80 mmHg"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Heart Rate (HR)</label>
                        <input type="text" name="vital_hr" value="{{ old('vital_hr', '72 bpm') }}" placeholder="72 bpm"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Oxygen Saturation (SpO2)</label>
                        <input type="text" name="vital_spo2" value="{{ old('vital_spo2', '98%') }}" placeholder="98%"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Body Temperature</label>
                        <input type="text" name="vital_temp" value="{{ old('vital_temp', '98.6 °F') }}" placeholder="98.6 °F"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Respiratory Rate</label>
                        <input type="text" name="vital_resp" value="{{ old('vital_resp', '16 /min') }}" placeholder="16 /min"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Body Weight (kg)</label>
                        <input type="text" name="vital_weight" value="{{ old('vital_weight') }}" placeholder="e.g. 75 kg"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('medical-records.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Record Consultation EMR
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
