@extends('layouts.app')

@section('title', 'Bed Tracker & Ward Management')
@section('header_title', 'Live Facility & Bed Tracker')
@section('header_subtitle', 'Real-time visibility of ICU, Private, Semi-Private, and General Ward bed occupancy')

@section('content')
<div class="space-y-6" x-data="{ 
    admitModal: false, 
    statusModal: false, 
    roomModal: false, 
    dischargeModal: false,
    selectedBedId: null, 
    selectedBedNum: '', 
    selectedRoomNum: '',
    selectedAdmissionId: null,
    selectedPatientName: ''
}">

    <!-- Stats Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Beds</span>
            <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_beds'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Available</span>
            <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['available_beds'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-rose-600 uppercase flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Occupied</span>
            <h4 class="text-2xl font-black text-rose-600 mt-1">{{ $stats['occupied_beds'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-amber-600 uppercase flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Cleaning</span>
            <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['cleaning_beds'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm col-span-2 sm:col-span-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase">Maintenance</span>
            <h4 class="text-2xl font-black text-slate-600 mt-1">{{ $stats['maintenance_beds'] }}</h4>
        </div>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('facilities.bed-tracker') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <select name="department_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="">All Clinical Departments</option>
                @foreach($departments as $d)
                <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                @endforeach
            </select>

            <select name="room_type" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="">All Room Types</option>
                <option value="ICU" {{ request('room_type') == 'ICU' ? 'selected' : '' }}>ICU Suites</option>
                <option value="Private" {{ request('room_type') == 'Private' ? 'selected' : '' }}>Private Rooms</option>
                <option value="Semi-Private" {{ request('room_type') == 'Semi-Private' ? 'selected' : '' }}>Semi-Private Rooms</option>
                <option value="General Ward" {{ request('room_type') == 'General Ward' ? 'selected' : '' }}>General Wards</option>
                <option value="Operating Theater" {{ request('room_type') == 'Operating Theater' ? 'selected' : '' }}>Operating Theaters</option>
            </select>
        </form>

        <div class="flex items-center gap-3">
            <a href="{{ route('facilities.admissions') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-list-check mr-1"></i> Admissions Log
            </a>
            @if(Auth::user()->isAdmin())
            <button @click="roomModal = true" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Add Room
            </button>
            @endif
        </div>
    </div>

    <!-- Visual Interactive Room & Bed Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($rooms as $room)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            
            <!-- Room Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-black text-slate-900 text-base">Room {{ $room->room_number }}</h4>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $room->room_type === 'ICU' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-cyan-50 text-cyan-700 border border-cyan-200' }}">
                            {{ $room->room_type }}
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $room->department->name ?? 'General' }} &bull; ${{ number_format($room->daily_rate, 2) }}/day</p>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ $room->available_beds_count }} / {{ $room->beds->count() }} Avail</span>
            </div>

            <!-- Beds Grid in this Room -->
            <div class="grid grid-cols-2 gap-3">
                @foreach($room->beds as $bed)
                <div class="p-3.5 rounded-2xl border transition relative {{ $bed->status === 'available' ? 'bg-emerald-50/50 border-emerald-200 hover:bg-emerald-100/50' : ($bed->status === 'occupied' ? 'bg-rose-50/50 border-rose-200' : 'bg-amber-50/50 border-amber-200') }}">
                    
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-slate-900 text-xs">Bed #{{ $bed->bed_number }}</span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $bed->status === 'available' ? 'bg-emerald-500' : ($bed->status === 'occupied' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                    </div>

                    @if($bed->status === 'occupied')
                        @if(Auth::user()->isPatient())
                        <div class="mt-2 space-y-1 text-[11px]">
                            <p class="font-bold text-slate-700">Currently Occupied</p>
                            <p class="text-slate-400 text-[10px]">Inpatient Care Active</p>
                            <div class="pt-2">
                                <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md text-[10px] font-bold">Occupied</span>
                            </div>
                        </div>
                        @elseif($bed->currentAdmission)
                        <div class="mt-2 space-y-0.5 text-[11px]">
                            <p class="font-bold text-slate-900 truncate">{{ $bed->currentAdmission->patient->full_name }}</p>
                            <p class="text-slate-500 font-mono text-[10px]">{{ $bed->currentAdmission->patient->patient_code }}</p>
                            <p class="text-slate-600 text-[10px]">Dr. {{ $bed->currentAdmission->doctor->user->name ?? 'Doctor' }}</p>
                            
                            <div class="pt-2 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400 font-semibold">{{ $bed->currentAdmission->stay_days }}d Stay</span>
                                <button @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; dischargeModal = true"
                                        class="px-2 py-0.5 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-[10px] font-bold shadow-xs">
                                    Discharge
                                </button>
                            </div>
                        </div>
                        @endif
                    @elseif($bed->status === 'available')
                    <div class="mt-4 text-center">
                        <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                class="w-full py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-1">
                            <i class="fa-solid fa-plus text-[10px]"></i> {{ Auth::user()->isPatient() ? 'Request Bed' : 'Admit Patient' }}
                        </button>
                    </div>
                    @else
                    <div class="mt-3 text-center">
                        <span class="text-[10px] font-bold text-amber-700 block uppercase mb-1">{{ $bed->status }}</span>
                        @if(!Auth::user()->isPatient())
                        <form action="{{ route('facilities.beds.updateStatus', $bed->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="available">
                            <button type="submit" class="px-2 py-1 bg-white border border-amber-300 text-amber-800 rounded-lg text-[10px] font-bold hover:bg-amber-100">
                                Mark Ready
                            </button>
                        </form>
                        @else
                        <span class="text-[10px] text-slate-400 font-semibold">Under Sanitization</span>
                        @endif
                    </div>
                    @endif

                </div>
                @endforeach
            </div>

        </div>
        @empty
        <div class="col-span-3 bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            No rooms matching the filter.
        </div>
        @endforelse
    </div>

    <!-- Modal 1: Inpatient Admission -->
    <div x-show="admitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="admitModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-bed text-emerald-600"></i> {{ Auth::user()->isPatient() ? 'Request Inpatient Bed' : 'Admit to Room' }} <span x-text="selectedRoomNum"></span> (Bed <span x-text="selectedBedNum"></span>)
            </h4>

            <form action="{{ route('facilities.admit') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="bed_id" :value="selectedBedId">

                @if(Auth::user()->isPatient())
                <input type="hidden" name="patient_id" value="{{ Auth::user()->patient?->id }}">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <span class="text-[10px] font-bold text-slate-400 uppercase block">Patient</span>
                    <strong class="text-slate-900 text-xs">{{ Auth::user()->name }}</strong>
                    <span class="text-[11px] text-slate-500 font-mono ml-2">({{ Auth::user()->patient?->patient_code ?? 'UPI-'.Auth::id() }})</span>
                </div>
                @else
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Patient *</label>
                    <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Choose Patient from Directory --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Attending Physician / Doctor *</label>
                    <select name="doctor_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->full_name }} ({{ $doc->department->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Admission Date & Time *</label>
                    <input type="datetime-local" name="admission_date" value="{{ date('Y-m-d\TH:i') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Admission / Initial Assessment</label>
                    <textarea name="admission_reason" rows="2" placeholder="e.g. Post-operative care, continuous monitoring, physician recommendation" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="admitModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md">{{ Auth::user()->isPatient() ? 'Submit Request' : 'Confirm Admission' }}</button>
                </div>
            </form>
        </div>
    </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Discharge Patient -->
    <div x-show="dischargeModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="dischargeModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-person-walking-arrow-right text-rose-600"></i> Discharge <span x-text="selectedPatientName"></span>
            </h4>

            <form :action="'{{ url('facilities/discharge') }}/' + selectedAdmissionId" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Discharge Clinical Notes *</label>
                    <textarea name="discharge_notes" rows="3" required placeholder="Patient recovered, vitals stable, discharge medications issued..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <div>
                    <label class="flex items-center gap-2 text-xs text-slate-700 cursor-pointer">
                        <input type="checkbox" name="generate_invoice" value="1" checked class="rounded text-rose-600 focus:ring-rose-500">
                        <span class="font-semibold">Generate itemized hospital stay invoice in billing</span>
                    </label>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="dischargeModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md">Complete Discharge</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 3: Add Room & Beds (Admin) -->
    <div x-show="roomModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="roomModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hospital text-cyan-600"></i> Add Room & Bed Unit
            </h4>

            <form action="{{ route('facilities.rooms.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Room Number *</label>
                        <input type="text" name="room_number" required placeholder="e.g. ICU-103"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Room Type *</label>
                        <select name="room_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="ICU">ICU</option>
                            <option value="Private">Private</option>
                            <option value="Semi-Private">Semi-Private</option>
                            <option value="General Ward">General Ward</option>
                            <option value="Operating Theater">Operating Theater</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Department *</label>
                        <select name="department_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Daily Rate ($) *</label>
                        <input type="number" step="0.01" name="daily_rate" required placeholder="250.00"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Number of Beds to Provision *</label>
                    <input type="number" name="beds_count" min="1" max="20" value="2" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="roomModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md">Provision Room</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
