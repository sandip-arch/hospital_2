@extends('layouts.app')

@section('title', 'Book Appointment')
@section('header_title', 'Schedule Consultation Appointment')
@section('header_subtitle', 'Select physician, available consultation date, and verified time slot')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="appointmentScheduler()">

    <div class="flex items-center justify-between">
        <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Appointments
        </a>
        <span class="text-xs text-slate-400 font-medium">Step-by-step verified scheduling</span>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
        
        <form action="{{ route('appointments.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Patient Information (Auto-detected from Auth / Session, no manual patient select dropdown) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Patient Information</span>
                    <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i> Auto-detected by Auth Session
                    </span>
                </label>
                <div class="p-4 bg-gradient-to-r from-cyan-50/90 via-sky-50/40 to-white border border-cyan-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white flex items-center justify-center font-bold text-base shadow-sm shrink-0">
                            <i class="fa-solid fa-user-check"></i>
                        </div>
                        <div class="text-xs">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-extrabold text-slate-900 text-sm">{{ $patient->full_name }}</p>
                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
                                    {{ $patient->gender ? ucfirst($patient->gender) : 'Patient' }}
                                </span>
                            </div>
                            <p class="text-slate-500 font-medium mt-0.5">
                                UPI / Code: <span class="font-mono font-bold text-cyan-800">{{ $patient->patient_code }}</span>
                                @if($patient->phone && $patient->phone !== 'N/A')
                                &bull; Phone: <span class="text-slate-700 font-semibold">{{ $patient->phone }}</span>
                                @endif
                                @if($patient->email)
                                &bull; Email: <span class="text-slate-700 font-semibold">{{ $patient->email }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                </div>
            </div>

            <!-- Department & Doctor Selection with dynamic filtering -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Clinical Department *</label>
                    <select name="department_id" x-model="selectedDept" @change="onDeptChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- All Clinical Departments --</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Attending Physician / Specialist *</label>
                    <select name="doctor_id" x-model="selectedDoctor" @change="onDoctorChange()" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Select Specialist --</option>
                        <template x-for="doc in filteredDoctors" :key="doc.id">
                            <option :value="doc.id" x-text="doc.name + ' - ' + doc.dept + ' ($' + doc.fee + ')'"></option>
                        </template>
                    </select>
                </div>
            </div>

            <!-- Doctor Shift & Working Days Summary Banner -->
            <template x-if="getSelectedDoctorObj()">
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2 flex-wrap">
                        <i class="fa-solid fa-clock-rotate-left text-cyan-600"></i>
                        <span class="font-bold text-slate-700" x-text="getSelectedDoctorObj().name + ' Duty Days:'"></span>
                        <span class="text-cyan-700 font-semibold" x-text="getDoctorWorkingDaysSummary(getSelectedDoctorObj())"></span>
                    </div>
                    <span class="text-slate-600 font-bold shrink-0" x-text="'Fee: $' + getSelectedDoctorObj().fee"></span>
                </div>
            </template>

            <!-- Appointment Date & Time Slot (Filtered strictly by Doctor's Schedule) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Appointment Date: Only shows dates when the doctor is on duty -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Appointment Date *</span>
                        <span class="text-[10px] text-cyan-600 font-bold">Duty dates only</span>
                    </label>
                    <select name="appointment_date" x-model="selectedDate" @change="fetchSlots()" required
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <template x-if="availableDates.length === 0">
                            <option value="">No working schedule found for this doctor</option>
                        </template>
                        <template x-for="d in availableDates" :key="d.date">
                            <option :value="d.date" x-text="d.label + ' &bull; ' + d.subtext"></option>
                        </template>
                    </select>
                    <p class="text-[10.5px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="fa-regular fa-calendar-check text-cyan-600"></i>
                        <span>Filtered automatically based on doctor's weekly roster.</span>
                    </p>
                </div>

                <!-- Time Slot: Generated dynamically based on start/end hours and subtracts booked appointments -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Available Time Slot *</span>
                        <template x-if="loadingSlots">
                            <span class="text-[10px] text-cyan-600 font-bold flex items-center gap-1">
                                <i class="fa-solid fa-spinner fa-spin"></i> Checking slots...
                            </span>
                        </template>
                    </label>
                    <select name="time_slot" x-model="selectedSlot" required :disabled="loadingSlots || availableSlots.length === 0"
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 disabled:opacity-60 disabled:cursor-not-allowed">
                        <template x-if="loadingSlots">
                            <option value="">Checking doctor availability...</option>
                        </template>
                        <template x-if="!loadingSlots && availableSlots.length === 0">
                            <option value="">No consultation slots available</option>
                        </template>
                        <template x-for="slot in availableSlots" :key="slot.time_slot">
                            <option :value="slot.time_slot" :disabled="!slot.is_available"
                                    x-text="slot.time_slot + (slot.is_available ? ' - Available' : ' - Booked')"></option>
                        </template>
                    </select>
                    
                    <template x-if="slotMessage">
                        <p class="text-[11px] text-rose-600 font-semibold mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span x-text="slotMessage"></span>
                        </p>
                    </template>
                    <template x-if="!slotMessage && !loadingSlots && availableSlots.length > 0">
                        <p class="text-[10.5px] text-emerald-700 font-semibold mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-check"></i>
                            <span x-text="availableCount + ' available consultation slots on this date'"></span>
                        </p>
                    </template>
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
                <button type="submit" :disabled="loadingSlots || !selectedSlot" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
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
                fee: '{{ number_format($doc->consultation_fee, 2) }}',
                availabilities: [
                    @foreach($doc->availabilities as $av)
                    {
                        day: '{{ $av->day_of_week }}',
                        isAvailable: {{ $av->is_available ? 'true' : 'false' }},
                        start: '{{ substr($av->start_time, 0, 5) }}',
                        end: '{{ substr($av->end_time, 0, 5) }}'
                    },
                    @endforeach
                ]
            },
            @endforeach
        ];

        const initialDocId = '{{ old('doctor_id', $selectedDoctorId ?? '') }}';

        return {
            allDoctors: allDocs,
            filteredDoctors: allDocs,
            selectedDept: '',
            selectedDoctor: '',
            selectedDate: '',
            selectedSlot: '',
            availableDates: [],
            availableSlots: [],
            availableCount: 0,
            loadingSlots: false,
            slotMessage: '',

            init() {
                if (initialDocId && this.allDoctors.some(d => d.id == initialDocId)) {
                    this.selectedDoctor = initialDocId;
                    const docObj = this.allDoctors.find(d => d.id == initialDocId);
                    if (docObj) {
                        this.selectedDept = docObj.deptId;
                        this.filterDoctors(false);
                    }
                } else if (this.allDoctors.length > 0) {
                    this.selectedDoctor = this.allDoctors[0].id;
                }
                this.updateAvailableDates();
            },

            filterDoctors(resetSelected = true) {
                if (!this.selectedDept) {
                    this.filteredDoctors = this.allDoctors;
                } else {
                    this.filteredDoctors = this.allDoctors.filter(d => d.deptId == this.selectedDept);
                }

                if (resetSelected) {
                    if (this.filteredDoctors.length > 0) {
                        this.selectedDoctor = this.filteredDoctors[0].id;
                    } else {
                        this.selectedDoctor = '';
                    }
                    this.updateAvailableDates();
                }
            },

            onDeptChange() {
                this.filterDoctors(true);
            },

            onDoctorChange() {
                const doc = this.getSelectedDoctorObj();
                if (doc && doc.deptId) {
                    this.selectedDept = doc.deptId;
                }
                this.updateAvailableDates();
            },

            getSelectedDoctorObj() {
                return this.allDoctors.find(d => d.id == this.selectedDoctor);
            },

            getDoctorWorkingDays(doc) {
                if (!doc) return [];
                if (!doc.availabilities || doc.availabilities.length === 0) {
                    return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                }
                const active = doc.availabilities.filter(a => a.isAvailable).map(a => a.day);
                return active.length > 0 ? active : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            },

            getDoctorWorkingDaysSummary(doc) {
                const days = this.getDoctorWorkingDays(doc);
                return days.join(', ');
            },

            getDayScheduleInfo(doc, dayName) {
                if (!doc || !doc.availabilities) return null;
                const match = doc.availabilities.find(a => a.day === dayName && a.isAvailable);
                return match ? { start: match.start, end: match.end } : null;
            },

            updateAvailableDates() {
                const doc = this.getSelectedDoctorObj();
                if (!doc) {
                    this.availableDates = [];
                    this.selectedDate = '';
                    this.availableSlots = [];
                    this.selectedSlot = '';
                    return;
                }

                const workingDays = this.getDoctorWorkingDays(doc);
                const dates = [];
                const now = new Date();

                for (let i = 0; i < 35; i++) {
                    const d = new Date(now);
                    d.setDate(now.getDate() + i);

                    const dayName = d.toLocaleDateString('en-US', { weekday: 'long' });

                    if (workingDays.includes(dayName)) {
                        const yyyy = d.getFullYear();
                        const mm = String(d.getMonth() + 1).padStart(2, '0');
                        const dd = String(d.getDate()).padStart(2, '0');
                        const dateStr = `${yyyy}-${mm}-${dd}`;

                        const formatted = d.toLocaleDateString('en-US', {
                            weekday: 'short',
                            month: 'short',
                            day: 'numeric'
                        });

                        const sched = this.getDayScheduleInfo(doc, dayName);
                        const timeInfo = sched ? `${sched.start} - ${sched.end}` : '09:00 - 17:00';

                        dates.push({
                            date: dateStr,
                            label: `${formatted}`,
                            subtext: timeInfo
                        });
                    }
                }

                this.availableDates = dates;

                if (dates.length > 0) {
                    this.selectedDate = dates[0].date;
                    this.fetchSlots();
                } else {
                    this.selectedDate = '';
                    this.availableSlots = [];
                    this.selectedSlot = '';
                }
            },

            async fetchSlots() {
                if (!this.selectedDoctor || !this.selectedDate) {
                    this.availableSlots = [];
                    this.selectedSlot = '';
                    this.availableCount = 0;
                    return;
                }

                this.loadingSlots = true;
                this.slotMessage = '';

                try {
                    const res = await fetch(`{{ route('appointments.available-slots') }}?doctor_id=${encodeURIComponent(this.selectedDoctor)}&date=${encodeURIComponent(this.selectedDate)}`);
                    const data = await res.json();

                    if (data.status === 'success' && data.is_available) {
                        this.availableSlots = data.slots || [];
                        this.availableCount = data.available_count || 0;

                        const firstAvail = this.availableSlots.find(s => s.is_available);
                        if (firstAvail) {
                            this.selectedSlot = firstAvail.time_slot;
                        } else {
                            this.selectedSlot = '';
                            this.slotMessage = 'All consultation slots are booked on this date. Please select another date.';
                        }
                    } else {
                        this.availableSlots = [];
                        this.selectedSlot = '';
                        this.availableCount = 0;
                        this.slotMessage = data.message || 'Doctor is not available on this date.';
                    }
                } catch (e) {
                    console.error('Failed to load slots', e);
                    this.slotMessage = 'Could not load time slots. Please try again.';
                } finally {
                    this.loadingSlots = false;
                }
            }
        };
    }
</script>
@endsection
