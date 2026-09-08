@extends('layouts.app')

@section('title', 'Book Appointment')
@section('header_title', 'Schedule Consultation Appointment')
@section('header_subtitle', 'Select patient, attending physician, clinical department, and time slot')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="appointmentScheduler()">

    <div class="flex items-center justify-between">
        <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Appointments
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
        
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Patient Selection -->
            @if(Auth::user()->isPatient() && Auth::user()->patient)
            <div class="p-4 bg-cyan-50/80 border border-cyan-200 rounded-2xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div class="text-xs">
                    <p class="font-bold text-slate-900">Booking for: {{ Auth::user()->patient->full_name }}</p>
                    <p class="text-slate-500 font-medium">UPI: <span class="font-mono font-bold text-cyan-800">{{ Auth::user()->patient->patient_code }}</span> &bull; Phone: {{ Auth::user()->patient->phone }}</p>
                </div>
            </div>
            @else
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Patient *</label>
                <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">-- Choose Patient from Directory --</option>
                    @foreach($patients as $p)
                    <option value="{{ $p->id }}" {{ (old('patient_id', $selectedPatientId) == $p->id) ? 'selected' : '' }}>
                        {{ $p->full_name }} ({{ $p->patient_code }}) - Phone: {{ $p->phone }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Department & Doctor Selection with dynamic filtering -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Clinical Department *</label>
                    <select name="department_id" x-model="selectedDept" @change="filterDoctors()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- All Clinical Departments --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Attending Physician / Specialist *</label>
                    <select name="doctor_id" x-model="selectedDoctor" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Select Specialist --</option>
                        <template x-for="doc in filteredDoctors" :key="doc.id">
                            <option :value="doc.id" x-text="doc.name + ' - ' + doc.dept + ' ($' + doc.fee + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Date & Time Slot -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Appointment Date *</label>
                    <input type="date" name="appointment_date" min="{{ date('Y-m-d') }}" value="{{ old('appointment_date', date('Y-m-d')) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Time Slot *</label>
                    <select name="time_slot" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="09:00 AM">09:00 AM</option>
                        <option value="09:30 AM">09:30 AM</option>
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="10:30 AM">10:30 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="11:30 AM">11:30 AM</option>
                        <option value="02:00 PM">02:00 PM</option>
                        <option value="02:30 PM">02:30 PM</option>
                        <option value="03:00 PM">03:00 PM</option>
                        <option value="03:30 PM">03:30 PM</option>
                        <option value="04:00 PM">04:00 PM</option>
                        <option value="04:30 PM">04:30 PM</option>
                    </select>
                </div>
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5">Reason for Consultation / Symptoms</label>
                <textarea name="reason" rows="3" placeholder="e.g. Routine follow-up, acute chest pain, migraine recurrence, blood pressure check"
                          class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">{{ old('reason') }}</textarea>
            </div>

            @if(!Auth::user()->isPatient())
            <div class="pt-2">
                <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer font-medium">
                    <input type="checkbox" name="generate_invoice" value="1" checked class="rounded text-cyan-600 focus:ring-cyan-500">
                    <span>Automatically generate consultation invoice in billing desk</span>
                </label>
            </div>
            @endif

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('appointments.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check"></i> Confirm Appointment
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    function appointmentScheduler() {
        const allDocs = [
            @foreach($doctors as $doc)
            {
                id: '{{ $doc->id }}',
                deptId: '{{ $doc->department_id }}',
                name: '{{ addslashes($doc->full_name) }}',
                dept: '{{ addslashes($doc->department->name ?? "General") }}',
                fee: '{{ number_format($doc->consultation_fee, 2) }}'
            },
            @endforeach
        ];

        return {
            allDoctors: allDocs,
            filteredDoctors: allDocs,
            selectedDept: '',
            selectedDoctor: '',
            init() {
                if (this.allDoctors.length > 0) {
                    this.selectedDoctor = this.allDoctors[0].id;
                }
            },
            filterDoctors() {
                if (!this.selectedDept) {
                    this.filteredDoctors = this.allDoctors;
                } else {
                    this.filteredDoctors = this.allDoctors.filter(d => d.deptId == this.selectedDept);
                }
                if (this.filteredDoctors.length > 0) {
                    this.selectedDoctor = this.filteredDoctors[0].id;
                } else {
                    this.selectedDoctor = '';
                }
            }
        }
    }
</script>
@endsection
