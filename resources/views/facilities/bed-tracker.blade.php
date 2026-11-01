@extends('layouts.app')

@section('title', 'Bed Tracker & Ward Management')
@section('header_title', 'Live Facility & Bed Tracker')
@section('header_subtitle', 'Real-time visibility of ICU, Private, Semi-Private, and General Ward bed occupancy')

@section('content')
<div class="space-y-6" x-data="{ 
    admitModal: false, 
    statusModal: false, 
    dischargeModal: false,
    switchModal: false,
    renameModal: false,
    addBedModal: false,
    selectedBedId: null, 
    selectedBedNum: '', 
    selectedRoomNum: '',
    selectedAdmissionId: '',
    selectedPatientName: '',
    currentBedDesc: '',
    renameRoomId: '',
    renameRoomNumber: '',
    addBedRoomId: '',
    addBedNumber: ''
}">

    <!-- Patient Emergency Policy Banner -->
    @if(Auth::user()->isPatient() && ($myNonEmer = Auth::user()->currentNonEmergencyAdmission()))
    <div class="bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent border-l-4 border-amber-500 p-4 rounded-2xl flex items-start gap-3 shadow-xs">
        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid fa-bed text-base"></i>
        </div>
        <div>
            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span>Active Inpatient Bed Assigned</span>
                <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-amber-200 text-amber-900">Bed #{{ $myNonEmer->bed->bed_number }}</span>
            </h5>
            <p class="text-xs text-slate-700 mt-1 leading-relaxed">
                You already have a bed booked in <strong>Bed #{{ $myNonEmer->bed->bed_number }} (Room {{ $myNonEmer->bed->room->room_number }})</strong>. Emergency bed reservations are disabled while you are admitted.
            </p>
        </div>
    </div>
    @elseif(Auth::user()->isPatient())
    <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border-l-4 border-amber-500 p-4 rounded-2xl flex items-start gap-3 shadow-xs">
        <div class="w-10 h-10 rounded-2xl bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid fa-truck-medical text-base"></i>
        </div>
        <div>
            <h5 class="text-xs font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <span>Patient Inpatient Policy: Emergency Beds Only</span>
                <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold bg-amber-200 text-amber-800">Self-Booking</span>
            </h5>
            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                As a patient, you can directly book available <strong>Emergency Beds</strong> for acute triage and immediate care. Beds in <strong>ICU Suites</strong>, <strong>Private Rooms</strong>, <strong>Semi-Private</strong>, and <strong>General Wards</strong> are clinically managed and require admission by an attending doctor or hospital administrator.
            </p>
        </div>
    </div>
    @endif

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
                <option value="Emergency" {{ request('room_type') == 'Emergency' ? 'selected' : '' }}>Emergency Triage</option>
                <option value="ICU" {{ request('room_type') == 'ICU' ? 'selected' : '' }}>ICU Suites</option>
                <option value="Private" {{ request('room_type') == 'Private' ? 'selected' : '' }}>Private Rooms</option>
                <option value="Semi-Private" {{ request('room_type') == 'Semi-Private' ? 'selected' : '' }}>Semi-Private Rooms</option>
                <option value="General Ward" {{ request('room_type') == 'General Ward' ? 'selected' : '' }}>General Wards</option>
                <option value="Operating Theater" {{ request('room_type') == 'Operating Theater' ? 'selected' : '' }}>Operating Theaters</option>
            </select>
        </form>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('facilities.admissions') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-list-check"></i> Admissions Log
            </a>

            @if(!Auth::user()->isPatient())
            <!-- Bed Switch Action Button -->
            <button type="button" @click="selectedAdmissionId = ''; selectedPatientName = ''; currentBedDesc = ''; switchModal = true" 
                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md shadow-indigo-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-arrows-rotate"></i> Switch Bed
            </button>

            <!-- Room Rename Action Button -->
            @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
            <button type="button" @click="renameRoomId = '{{ $rooms->first()?->id ?? '' }}'; renameRoomNumber = '{{ addslashes($rooms->first()?->room_number ?? '') }}'; renameModal = true" 
                    class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i> Rename Room
            </button>
            @endif

            <!-- Add Bed Action Button -->
            <button type="button" @click="addBedRoomId = '{{ $rooms->first()?->id ?? '' }}'; addBedNumber = ''; addBedModal = true" 
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Add Bed
            </button>
            @endif
        </div>
    </div>

    <!-- Visual Interactive Room & Bed Matrix (8 Fixed Rooms) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" data-live-results>
        @forelse($rooms as $room)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4 flex flex-col justify-between">
            
            <!-- Room Header -->
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="min-w-0 flex-1 pr-2">
                    <div class="flex flex-wrap items-center gap-1.5">
                        <h4 class="font-black text-slate-900 text-base truncate" title="Room {{ $room->room_number }}">
                            Room {{ $room->room_number }}
                        </h4>
                        <span class="px-2 py-0.5 rounded-full text-[9.5px] font-bold {{ $room->isEmergency() ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($room->room_type === 'ICU' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-cyan-50 text-cyan-700 border border-cyan-200') }}">
                            {{ $room->room_type }}
                        </span>
                        @if($room->isEmergency())
                        <span class="px-1.5 py-0.5 rounded-md text-[8.5px] font-black bg-rose-600 text-white uppercase tracking-wider flex items-center gap-0.5">
                            <i class="fa-solid fa-bolt-lightning text-[7.5px]"></i> ER
                        </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $room->department->name ?? 'General' }} &bull; ${{ number_format($room->daily_rate, 2) }}/day</p>
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    <span class="text-[11px] font-bold text-slate-400 mr-1">{{ $room->available_beds_count }}/{{ $room->beds->count() }}</span>
                    @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
                    <button type="button" @click="renameRoomId = {{ $room->id }}; renameRoomNumber = '{{ addslashes($room->room_number) }}'; renameModal = true" 
                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs transition" title="Rename Room">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    @endif
                    @if(Auth::user()->isAdmin() || Auth::user()->isStaff() || Auth::user()->isDoctor())
                    <button type="button" @click="addBedRoomId = {{ $room->id }}; addBedNumber = 'B{{ $room->beds->count() + 1 }}'; addBedModal = true" 
                            class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center justify-center text-xs transition" title="Add Bed to this Room">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Beds Container (Shows up to 4 beds initially with smooth scrollbar for overflow) -->
            <div class="max-h-[340px] overflow-y-auto pr-1 space-y-3 custom-scrollbar flex-1">
                <div class="grid grid-cols-2 gap-3">
                    @forelse($room->beds as $bed)
                    @php
                        $isAvail = $bed->status === 'available';
                        $isOccupied = $bed->status === 'occupied';
                        $isEmer = $bed->isEmergency();
                        $isMyBed = Auth::user()->isPatient() && $bed->currentAdmission && (int) ($bed->currentAdmission->patient_id ?? 0) === (int) (Auth::user()->patient?->id ?? -1);
                        
                        $cardClass = 'bg-amber-50/50 border-amber-200';
                        if ($isMyBed) {
                            $cardClass = 'bg-gradient-to-b from-cyan-50/90 via-sky-50/40 to-white border-2 border-cyan-500 shadow-md shadow-cyan-500/10 ring-2 ring-cyan-400/20';
                        } elseif ($isOccupied) {
                            $cardClass = 'bg-rose-50/50 border-rose-200';
                        } elseif ($isAvail) {
                            $cardClass = $isEmer 
                                ? 'bg-gradient-to-b from-rose-50/30 to-rose-50/70 border-rose-200/90 hover:border-rose-300' 
                                : 'bg-emerald-50/40 border-emerald-200/80 hover:bg-emerald-50/70 hover:border-emerald-300';
                        }
                    @endphp
                    <div class="p-3 rounded-2xl border transition relative flex flex-col justify-between min-h-[145px] {{ $cardClass }}">
                        
                        <div>
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1 min-w-0">
                                    <span class="font-bold text-slate-900 text-xs whitespace-nowrap">Bed #{{ $bed->bed_number }}</span>
                                    @if($isMyBed)
                                    <span class="px-1 py-0.5 rounded text-[8px] font-black bg-cyan-600 text-white uppercase tracking-wider flex items-center gap-0.5 shadow-xs shrink-0">
                                        <i class="fa-solid fa-user-check text-[7px]"></i> Mine
                                    </span>
                                    @elseif($isEmer && $isAvail)
                                    <span class="px-1 py-0.5 rounded text-[8px] font-black bg-rose-100 text-rose-700 uppercase tracking-wider shrink-0">ER</span>
                                    @endif
                                </div>
                                <span class="w-2.5 h-2.5 rounded-full shrink-0 {{ $isMyBed ? 'bg-cyan-500 ring-4 ring-cyan-200 animate-pulse' : ($isAvail ? ($isEmer ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500') : ($isOccupied ? 'bg-rose-500' : 'bg-amber-500')) }}"></span>
                            </div>

                            @if($bed->status === 'occupied')
                                @if(Auth::user()->isPatient())
                                    @if($isMyBed)
                                    <div class="mt-2 space-y-1 text-[11px]">
                                        <div class="p-1.5 bg-cyan-100/60 border border-cyan-200 rounded-lg">
                                            <div class="flex items-center gap-1 text-cyan-900 font-extrabold text-[11px]">
                                                <i class="fa-solid fa-circle-check text-cyan-600 text-[10px]"></i>
                                                <span>Your Bed</span>
                                            </div>
                                            <p class="text-[9.5px] text-cyan-800/90 font-medium">Room {{ $room->room_number }}</p>
                                        </div>
                                        <p class="text-[9.5px] text-slate-600 truncate"><span class="text-slate-400">Dr:</span> {{ Str::startsWith($bed->currentAdmission->doctor?->user?->name, 'Dr.') ? $bed->currentAdmission->doctor->user->name : 'Dr. ' . ($bed->currentAdmission->doctor?->user?->name ?? 'Specialist') }}</p>
                                    </div>
                                    @else
                                    <div class="mt-2 space-y-1 text-[11px]">
                                        <p class="font-bold text-slate-700 text-xs">Occupied</p>
                                        <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md text-[9px] font-bold inline-block">Active Care</span>
                                    </div>
                                    @endif
                                @elseif($bed->currentAdmission)
                                <div class="mt-2 space-y-0.5 text-[11px]">
                                    <p class="font-bold text-slate-900 truncate text-xs">{{ $bed->currentAdmission->patient->full_name }}</p>
                                    <p class="text-slate-500 font-mono text-[9px] truncate">{{ $bed->currentAdmission->patient->patient_code }}</p>
                                    <p class="text-slate-600 text-[9.5px] truncate">{{ Str::startsWith($bed->currentAdmission->doctor?->user?->name, 'Dr.') ? $bed->currentAdmission->doctor->user->name : 'Dr. ' . ($bed->currentAdmission->doctor?->user?->name ?? 'Doctor') }}</p>
                                </div>
                                @else
                                <div class="mt-2 space-y-0.5 text-[11px]">
                                    <p class="font-bold text-rose-700 text-xs">Occupied</p>
                                    <p class="text-slate-400 text-[9px]">Bed Held</p>
                                </div>
                                @endif
                            @elseif($bed->status === 'available')
                            <div class="mt-2 text-[10px] text-emerald-700 font-semibold">
                                Ready for Patient
                            </div>
                            @else
                            <div class="mt-2 text-[10px] text-amber-700 font-semibold uppercase">
                                {{ $bed->status }}
                            </div>
                            @endif
                        </div>

                        <!-- Bed Bottom Actions -->
                        <div class="mt-2 pt-2 border-t border-slate-200/60">
                            @if($bed->status === 'occupied')
                                @if(!Auth::user()->isPatient() && $bed->currentAdmission)
                                    @php
                                        $canSwitch = Auth::user()->canSwitchBed($bed->currentAdmission);
                                        $canDischarge = Auth::user()->canDischargeAdmission($bed->currentAdmission);
                                    @endphp

                                    @if($canSwitch && $canDischarge)
                                    <div class="flex flex-col gap-1 w-full">
                                        <button type="button" 
                                                @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; currentBedDesc = 'Bed #{{ $bed->bed_number }} (Room {{ $room->room_number }})'; switchModal = true"
                                                class="w-full py-1 px-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                                title="Transfer patient to another available bed">
                                            <i class="fa-solid fa-arrows-rotate text-[8.5px]"></i> Switch Bed
                                        </button>
                                        <button type="button" @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; dischargeModal = true"
                                                class="w-full py-1 px-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                                title="Discharge patient">
                                            <i class="fa-solid fa-arrow-right-from-bracket text-[8.5px] text-rose-600"></i> Discharge
                                        </button>
                                    </div>
                                    @elseif($canSwitch)
                                    <button type="button" 
                                            @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; currentBedDesc = 'Bed #{{ $bed->bed_number }} (Room {{ $room->room_number }})'; switchModal = true"
                                            class="w-full py-1 px-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                            title="Transfer patient to another available bed">
                                        <i class="fa-solid fa-arrows-rotate text-[8.5px]"></i> Switch Bed
                                    </button>
                                    @elseif($canDischarge)
                                    <button type="button" @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; dischargeModal = true"
                                            class="w-full py-1 px-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                            title="Discharge patient">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-[8.5px] text-rose-600"></i> Discharge
                                    </button>
                                    @endif
                                @elseif(!Auth::user()->isPatient() && Auth::user()->canReleaseBed($bed))
                                <form action="{{ route('facilities.discharge', $bed->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-[9.5px] font-bold shadow-xs">
                                        Release Bed
                                    </button>
                                </form>
                                @endif
                            @elseif($bed->status === 'available')
                                @if($bed->canBeBookedBy(Auth::user()))
                                    @if(Auth::user()->isPatient())
                                    <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                            class="w-full py-1.5 px-2 bg-gradient-to-r from-rose-600 to-red-600 text-white rounded-xl text-[10.5px] font-black shadow-xs hover:shadow-md transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-truck-medical text-[9px]"></i> Request
                                    </button>
                                    @else
                                    <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                            class="w-full py-1.5 px-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl text-[10.5px] font-bold shadow-xs hover:shadow-md transition flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-plus text-[9px]"></i> Admit
                                    </button>
                                    @endif
                                @else
                                <div class="w-full py-1 bg-slate-100 rounded-lg text-[9px] font-semibold text-slate-400 text-center select-none">
                                    <i class="fa-solid fa-lock text-[8px]"></i> Referral Only
                                </div>
                                @endif
                            @else
                                @if(!Auth::user()->isPatient())
                                <form action="{{ route('facilities.beds.updateStatus', $bed->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="w-full py-1 bg-white border border-amber-300 text-amber-800 rounded-lg text-[9.5px] font-bold hover:bg-amber-100 transition">
                                        Mark Ready
                                    </button>
                                </form>
                                @else
                                <span class="text-[9px] text-slate-400 font-semibold block text-center">Sanitizing</span>
                                @endif
                            @endif
                        </div>

                    </div>
                    @empty
                    <div class="col-span-2 py-6 text-center text-xs text-slate-400 italic">
                        No beds configured in this room yet.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
        @empty
        <div class="col-span-4 bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            No rooms matching the filter.
        </div>
        @endforelse
    </div>

    <!-- Modal 1: Inpatient Admission -->
    <div x-show="admitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="admitModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-bed text-emerald-600"></i> {{ Auth::user()->isPatient() ? 'Request Emergency Bed' : 'Admit Patient to Room' }} <span x-text="selectedRoomNum"></span> (Bed <span x-text="selectedBedNum"></span>)
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
                    <button type="button" @click="admitModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">Cancel</button>
                    <button type="submit" class="px-5 py-2.5 bg-gradient-to-r {{ Auth::user()->isPatient() ? 'from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 shadow-rose-600/20' : 'from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-emerald-600/20' }} text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-1.5">
                        <i class="fa-solid {{ Auth::user()->isPatient() ? 'fa-paper-plane' : 'fa-check' }} text-[10px]"></i>
                        {{ Auth::user()->isPatient() ? 'Submit Emergency Request' : 'Confirm Admission' }}
                    </button>
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
<br>
    <!-- Modal 3: Switch Bed / Inpatient Transfer -->
    <div x-show="switchModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="switchModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate text-indigo-600"></i> Switch Inpatient Bed
            </h4>

            <!-- Patient details banner (when opened for specific patient) -->
            <template x-if="selectedPatientName">
                <div class="mb-4 p-3.5 bg-indigo-50/80 rounded-2xl border border-indigo-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Patient</span>
                        <strong class="text-slate-900 text-xs" x-text="selectedPatientName"></strong>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider block">Current Bed</span>
                        <span class="text-xs font-mono font-bold text-slate-700" x-text="currentBedDesc"></span>
                    </div>
                </div>
            </template>

            <!-- Patient selector (when opened from top Switch Bed action) -->
            <template x-if="!selectedPatientName">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Admitted Inpatient *</label>
                    <select x-model="selectedAdmissionId" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose Inpatient to Transfer --</option>
                        @foreach($activeAdmissions as $adm)
                        <option value="{{ $adm->id }}">
                            {{ $adm->patient->full_name }} ({{ $adm->patient->patient_code }}) &bull; Bed #{{ $adm->bed->bed_number }} (Room {{ $adm->bed->room->room_number }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </template>

            <form :action="'{{ url('facilities/admissions') }}/' + selectedAdmissionId + '/switch-bed'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Destination Bed *</label>
                    <select name="target_bed_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Choose an Available Bed --</option>
                        @foreach($availableBeds as $ab)
                        <option value="{{ $ab->id }}">
                            Room {{ $ab->room->room_number }} ({{ $ab->room->room_type }}) &bull; Bed #{{ $ab->bed_number }} &bull; ${{ number_format($ab->room->daily_rate, 2) }}/day &bull; {{ $ab->room->department->name ?? 'General' }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Bed Transfer / Notes</label>
                    <textarea name="switch_reason" rows="2" placeholder="e.g. Clinical condition upgrade, ICU step-down, patient request, isolation protocol" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2.5">
                    <i class="fa-solid fa-bell text-indigo-500 mt-0.5 shrink-0"></i>
                    <span class="leading-snug">The patient will automatically receive a system notification in their patient portal with their new bed & room assignment. The previous bed will immediately enter sanitization.</span>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="switchModal = false" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">Cancel</button>
                    <button type="submit" :disabled="!selectedAdmissionId" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Confirm Bed Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 4: Rename Room (Admin & Staff) -->
    <div x-show="renameModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="renameModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-pen-to-square text-slate-700"></i> Rename Room
            </h4>

            <form action="{{ route('facilities.rooms.rename') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Room to Rename *</label>
                    <select name="room_id" x-model="renameRoomId" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Choose Room --</option>
                        @foreach($rooms as $r)
                        <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->room_type }} &bull; {{ $r->department->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Room Number / Name *</label>
                    <input type="text" name="room_number" x-model="renameRoomNumber" required placeholder="e.g. ICU-101-A, Suite 201"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="renameModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 5: Add Bed (Admin, Staff & Doctors) -->
    <div x-show="addBedModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="addBedModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-bed text-emerald-600"></i> Add Bed to Room
            </h4>

            <form action="{{ route('facilities.beds.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Room *</label>
                    <select name="room_id" x-model="addBedRoomId" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Choose Target Room --</option>
                        @foreach($rooms as $r)
                        <option value="{{ $r->id }}">Room {{ $r->room_number }} ({{ $r->room_type }} &bull; {{ $r->beds->count() }} beds currently)</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Bed Number / Name *</label>
                    <input type="text" name="bed_number" x-model="addBedNumber" required placeholder="e.g. B5, Bed 5, ICU-B3"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Initial Bed Status</label>
                    <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="available">Available (Ready for Admission)</option>
                        <option value="cleaning">Cleaning (Sanitization Required)</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="addBedModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md">Add Bed</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
