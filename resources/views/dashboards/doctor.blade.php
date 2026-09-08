@extends('layouts.app')

@section('title', 'Doctor Clinical Workspace')
@section('header_title', 'Doctor Clinical Dashboard')
@section('header_subtitle', 'Daily patient queue, EMR consultation notes, digital prescriptions, and lab orders')

@section('content')
<div class="space-y-8">

    <!-- Doctor Profile & Stats Header -->
    <div class="bg-gradient-to-r from-blue-900 via-slate-900 to-cyan-950 rounded-3xl p-6 lg:p-8 text-white shadow-xl border border-blue-800/40 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-3xl bg-blue-600/30 border border-blue-400/40 text-cyan-300 font-black text-3xl flex items-center justify-center shadow-inner shrink-0">
                <i class="fa-solid fa-user-doctor"></i>
            </div>
            <div>
                <div class="flex items-center gap-3">
                    <h3 class="text-2xl font-black">{{ $doctor->full_name }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">
                        {{ $doctor->department->name ?? 'Specialist' }}
                    </span>
                </div>
                <p class="text-xs text-blue-200 mt-1">{{ $doctor->specialization }} &bull; License: <span class="font-mono text-cyan-400 font-bold">{{ $doctor->license_number }}</span></p>
                <p class="text-[11px] text-slate-400 mt-0.5">Consultation Fee: <span class="text-emerald-400 font-bold">${{ number_format($doctor->consultation_fee, 2) }}</span> &bull; Direct Phone: {{ $doctor->phone }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('medical-records.create', ['doctor_id' => $doctor->id]) }}" class="px-5 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs rounded-2xl shadow-lg shadow-cyan-500/30 transition flex items-center gap-2">
                <i class="fa-solid fa-file-waveform"></i> New Clinical Note (EMR)
            </a>
            <a href="{{ route('prescriptions.create', ['doctor_id' => $doctor->id]) }}" class="px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-2xl shadow-lg shadow-blue-600/30 transition flex items-center gap-2">
                <i class="fa-solid fa-prescription"></i> Write e-Prescription
            </a>
            <a href="{{ route('doctor.schedule') }}" class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-2xl border border-slate-700 transition flex items-center gap-2">
                <i class="fa-solid fa-calendar-days text-cyan-400"></i> My Schedule
            </a>
        </div>
    </div>

    <!-- Doctor Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Today's Visits</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $doctorStats['today_count'] }}</h4>
                <p class="text-[11px] text-blue-600 font-semibold mt-1">{{ $doctorStats['completed_today'] }} Completed</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unique Patients</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $doctorStats['total_patients'] }}</h4>
                <p class="text-[11px] text-purple-600 font-semibold mt-1">Under Direct Care</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Prescriptions Issued</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $doctorStats['total_prescriptions'] }}</h4>
                <p class="text-[11px] text-emerald-600 font-semibold mt-1">Pharmacy Integrated</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-pills"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pending Diagnostics</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $pendingLabReports->count() }}</h4>
                <p class="text-[11px] text-amber-600 font-semibold mt-1">Awaiting Lab Tech</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-flask"></i>
            </div>
        </div>
    </div>

    <!-- Today's Queue & Patient Workflow -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Main Today's Appointments Queue -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-clipboard-list text-cyan-600"></i> Today's Consultation Queue
                    </h3>
                    <p class="text-xs text-slate-500">{{ today()->format('l, F d, Y') }}</p>
                </div>
                <span class="px-3 py-1 bg-cyan-50 text-cyan-700 text-xs font-bold rounded-full border border-cyan-200">
                    {{ $todayAppointments->count() }} Patient(s)
                </span>
            </div>

            <div class="space-y-4">
                @forelse($todayAppointments as $apt)
                <div class="p-5 rounded-2xl border border-slate-200/80 hover:border-cyan-500/50 hover:shadow-md transition flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 {{ $apt->status === 'completed' ? 'bg-slate-50/70 opacity-80' : 'bg-white' }}">
                    <div class="flex items-start gap-4 min-w-0">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-100 to-blue-100 text-cyan-800 font-extrabold text-sm flex items-center justify-center shrink-0">
                            {{ $apt->time_slot }}
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 text-sm">{{ $apt->patient->full_name }}</h4>
                                <span class="font-mono text-[11px] text-slate-400 font-semibold">{{ $apt->patient->patient_code }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge }}">
                                    {{ ucfirst($apt->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">Reason: <span class="font-medium text-slate-800">{{ $apt->reason ?? 'General Consultation' }}</span></p>
                            <p class="text-[11px] text-slate-400 mt-0.5">Age: {{ $apt->patient->age }} yrs &bull; Blood: {{ $apt->patient->blood_type ?? 'N/A' }} &bull; Phone: {{ $apt->patient->phone }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <a href="{{ route('patients.show', $apt->patient_id) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Patient Dossier">
                            <i class="fa-solid fa-id-card"></i>
                        </a>

                        @if($apt->status !== 'completed')
                        <a href="{{ route('medical-records.create', ['patient_id' => $apt->patient_id, 'appointment_id' => $apt->id, 'doctor_id' => $doctor->id]) }}" 
                           class="px-3.5 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i class="fa-solid fa-stethoscope"></i> Start Consultation
                        </a>
                        @else
                        <span class="px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 flex items-center gap-1">
                            <i class="fa-solid fa-check-double"></i> Finished
                        </span>
                        @endif
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-400">
                    <i class="fa-solid fa-calendar-check text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-semibold text-slate-600">No appointments scheduled for today.</p>
                    <p class="text-xs text-slate-400 mt-1">Enjoy your day or review past clinical notes.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right Side: Pending Lab Tests & Upcoming Consults -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Pending Diagnostic Reports -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-flask-vial text-purple-600"></i> Ordered Lab Reports
                    </h4>
                    <a href="{{ route('lab.requests') }}" class="text-xs font-bold text-purple-600 hover:underline">All Tests</a>
                </div>

                <div class="space-y-3">
                    @forelse($pendingLabReports as $lab)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-slate-800">{{ $lab->test->test_name }}</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lab->status_badge }}">
                                {{ ucfirst($lab->status) }}
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">Patient: <span class="font-semibold text-slate-700">{{ $lab->patient->full_name }}</span></p>
                        <p class="text-[10px] text-slate-400 mt-0.5">Ordered {{ $lab->created_at->diffForHumans() }}</p>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-3 text-center">No pending lab test reports.</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Clinical Notes -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-file-lines text-cyan-600"></i> Recent Consultations
                    </h4>
                    <a href="{{ route('medical-records.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">History</a>
                </div>

                <div class="space-y-3 text-xs">
                    @foreach($recentRecords as $rec)
                    <div class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition border border-slate-100 flex items-center justify-between">
                        <div class="min-w-0">
                            <p class="font-bold text-slate-800 truncate">{{ $rec->patient->full_name }}</p>
                            <p class="text-[11px] text-slate-500 truncate">{{ $rec->diagnosis }}</p>
                            <span class="text-[10px] text-slate-400">{{ $rec->visit_date->format('M d, Y') }}</span>
                        </div>
                        <a href="{{ route('medical-records.show', $rec->id) }}" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-cyan-600 font-bold hover:bg-cyan-50 text-[11px]">
                            View
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
