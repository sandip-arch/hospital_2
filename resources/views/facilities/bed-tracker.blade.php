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
    switchModal: false,
    selectedBedId: null, 
    selectedBedNum: '', 
    selectedRoomNum: '',
    selectedAdmissionId: null,
    selectedPatientName: '',
    currentBedDesc: ''
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
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $room->isEmergency() ? 'bg-rose-50 text-rose-700 border border-rose-200' : ($room->room_type === 'ICU' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-cyan-50 text-cyan-700 border border-cyan-200') }}">
                            {{ $room->room_type }}
                        </span>
                        @if($room->isEmergency())
                        <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-rose-600 text-white uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-bolt-lightning text-[8px]"></i> Emergency
                        </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $room->department->name ?? 'General' }} &bull; ${{ number_format($room->daily_rate, 2) }}/day</p>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ $room->available_beds_count }} / {{ $room->beds->count() }} Avail</span>
            </div>

            <!-- Beds Grid in this Room -->
            <div class="grid grid-cols-2 gap-3">
                @foreach($room->beds as $bed)
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
                <div class="p-3.5 rounded-2xl border transition relative {{ $cardClass }}">
                    
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold text-slate-900 text-xs">Bed #{{ $bed->bed_number }}</span>
                            @if($isMyBed)
                            <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-cyan-600 text-white uppercase tracking-wider flex items-center gap-0.5 shadow-xs">
                                <i class="fa-solid fa-user-check text-[7px]"></i> Your Bed
                            </span>
                            @elseif($isEmer && $isAvail)
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-black bg-rose-100 text-rose-700 uppercase tracking-wider">ER</span>
                            @endif
                        </div>
                        <span class="w-2.5 h-2.5 rounded-full {{ $isMyBed ? 'bg-cyan-500 ring-4 ring-cyan-200 animate-pulse' : ($isAvail ? ($isEmer ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500') : ($isOccupied ? 'bg-rose-500' : 'bg-amber-500')) }}"></span>
                    </div>

                    @if($bed->status === 'occupied')
                        @if(Auth::user()->isPatient())
                            @if($isMyBed)
                            <div class="mt-2 space-y-1.5 text-[11px]">
                                <div class="p-2 bg-cyan-100/60 border border-cyan-200 rounded-xl">
                                    <div class="flex items-center gap-1.5 text-cyan-900 font-extrabold text-xs">
                                        <i class="fa-solid fa-circle-check text-cyan-600 text-xs"></i>
                                        <span>This Bed is for You</span>
                                    </div>
                                    <p class="text-[10px] text-cyan-800/90 mt-0.5 font-medium">
                                        Room {{ $room->room_number }} &bull; {{ $room->room_type }}
                                    </p>
                                </div>
                                <div class="pt-0.5 text-[10px] text-slate-600 space-y-0.5">
                                    <p class="truncate"><span class="text-slate-400 font-medium">Doctor:</span> <strong class="text-slate-800">{{ Str::startsWith($bed->currentAdmission->doctor?->user?->name, 'Dr.') ? $bed->currentAdmission->doctor->user->name : 'Dr. ' . ($bed->currentAdmission->doctor?->user?->name ?? 'Specialist') }}</strong></p>
                                    <p><span class="text-slate-400 font-medium">Stay:</span> <strong class="text-slate-800">{{ $bed->currentAdmission->stay_days }} Day(s)</strong> (Active)</p>
                                </div>
                            </div>
                            @else
                            <div class="mt-2 space-y-1 text-[11px]">
                                <p class="font-bold text-slate-700">Currently Occupied</p>
                                <p class="text-slate-400 text-[10px]">Inpatient Care Active</p>
                                <div class="pt-2">
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 rounded-md text-[10px] font-bold">Occupied</span>
                                </div>
                            </div>
                            @endif
                        @elseif($bed->currentAdmission)
                        <div class="mt-2 space-y-0.5 text-[11px]">
                            <p class="font-bold text-slate-900 truncate">{{ $bed->currentAdmission->patient->full_name }}</p>
                            <p class="text-slate-500 font-mono text-[10px]">{{ $bed->currentAdmission->patient->patient_code }}</p>
                            <p class="text-slate-600 text-[10px] truncate">{{ Str::startsWith($bed->currentAdmission->doctor?->user?->name, 'Dr.') ? $bed->currentAdmission->doctor->user->name : 'Dr. ' . ($bed->currentAdmission->doctor?->user?->name ?? 'Doctor') }}</p>
                            
                            <div class="mt-2 pt-2 border-t border-rose-100/90 space-y-1.5">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span class="text-slate-400 font-semibold">Stay:</span>
                                    <span class="font-bold text-slate-700">{{ $bed->currentAdmission->stay_days }} Day(s)</span>
                                </div>
                                
                                @php
                                    $canSwitch = Auth::user()->canSwitchBed($bed->currentAdmission);
                                    $canDischarge = Auth::user()->canDischargeAdmission($bed->currentAdmission);
                                @endphp

                                @if($canSwitch && $canDischarge)
                                <div class="grid grid-cols-2 gap-1.5 w-full">
                                    <button type="button" 
                                            @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; currentBedDesc = 'Bed #{{ $bed->bed_number }} (Room {{ $room->room_number }})'; switchModal = true"
                                            class="w-full py-1 px-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                            title="Transfer patient to another available bed">
                                        <i class="fa-solid fa-arrows-rotate text-[8px]"></i> Switch
                                    </button>
                                    <button type="button" @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; dischargeModal = true"
                                            class="w-full py-1 px-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[9.5px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                            title="Discharge patient">
                                        <i class="fa-solid fa-arrow-right-from-bracket text-[8px] text-rose-600"></i> Discharge
                                    </button>
                                </div>
                                @elseif($canSwitch)
                                <button type="button" 
                                        @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; currentBedDesc = 'Bed #{{ $bed->bed_number }} (Room {{ $room->room_number }})'; switchModal = true"
                                        class="w-full py-1 px-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-[10px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                        title="Transfer patient to another available bed">
                                    <i class="fa-solid fa-arrows-rotate text-[8px]"></i> Switch Bed
                                </button>
                                @elseif($canDischarge)
                                <button type="button" @click="selectedAdmissionId = {{ $bed->currentAdmission->id }}; selectedPatientName = '{{ addslashes($bed->currentAdmission->patient->full_name) }}'; dischargeModal = true"
                                        class="w-full py-1 px-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-[10px] font-bold shadow-xs transition flex items-center justify-center gap-1"
                                        title="Discharge patient">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-[8.5px] text-rose-600"></i> Discharge Patient
                                </button>
                                @else
                                <p class="text-[9px] text-slate-400 font-medium italic text-right truncate">{{ Str::startsWith($bed->currentAdmission->doctor?->user?->name, 'Dr.') ? $bed->currentAdmission->doctor->user->name : 'Dr. ' . ($bed->currentAdmission->doctor?->user?->name ?? 'Specialist') }}</p>
                                @endif
                            </div>
                        </div>
                        @else
                        <div class="mt-2 space-y-1 text-[11px]">
                            <p class="font-bold text-rose-700">Occupied (Bed Held)</p>
                            <p class="text-slate-400 text-[10px]">No linked patient record</p>
                            @if(Auth::user()->canReleaseBed($bed))
                            <div class="pt-2">
                                <form action="{{ route('facilities.discharge', $bed->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-md text-[10px] font-bold shadow-xs">
                                        Release Bed
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        @endif

                    @elseif($bed->status === 'available')
                    <div class="mt-4 text-center">
                        @if($bed->canBeBookedBy(Auth::user()))
                            @if(Auth::user()->isPatient())
                            <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                    class="w-full py-2 px-3 bg-gradient-to-r from-rose-600 via-rose-500 to-red-600 hover:from-rose-500 hover:to-red-500 text-white rounded-xl text-xs font-black shadow-md shadow-rose-600/25 hover:shadow-lg hover:shadow-rose-600/35 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150 flex items-center justify-center gap-2 group">
                                <span class="w-5 h-5 rounded-lg bg-white/20 flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-truck-medical text-[10px] text-white"></i>
                                </span>
                                <span class="tracking-wide">Request Bed</span>
                                <i class="fa-solid fa-arrow-right text-[9px] opacity-75 group-hover:translate-x-1 transition-transform"></i>
                            </button>
                            @elseif($isEmer)
                            <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                    class="w-full py-2 px-3 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow-md hover:shadow-rose-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150 flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-truck-medical text-[11px]"></i>
                                <span>Admit to ER</span>
                            </button>
                            @else
                            <button @click="selectedBedId = {{ $bed->id }}; selectedBedNum = '{{ $bed->bed_number }}'; selectedRoomNum = '{{ $room->room_number }}'; admitModal = true"
                                    class="w-full py-2 px-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow-md hover:shadow-emerald-500/20 hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150 flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-plus text-[10px]"></i>
                                <span>Admit Patient</span>
                            </button>
                            @endif
                        @else
                        {{-- Locked for patient self-booking --}}
                        @if(Auth::user()->isPatient() && $bed->isEmergency() && ($existingNonEmer = Auth::user()->currentNonEmergencyAdmission()))
                        <div class="w-full py-1.5 px-2 bg-amber-50 border border-amber-200/90 rounded-xl text-[10px] font-semibold text-amber-800 flex flex-col items-center justify-center gap-0.5 select-none text-center" title="You already have a bed booked in Bed #{{ $existingNonEmer->bed->bed_number }} (Room {{ $existingNonEmer->bed->room?->room_number }})">
                            <span class="font-black text-amber-900 flex items-center gap-1 text-[9.5px]">
                                <i class="fa-solid fa-ban text-[8.5px] text-amber-600"></i> Locked
                            </span>
                            <span class="text-[9px] text-amber-700 leading-tight">
                                You already have a bed booked in Bed #{{ $existingNonEmer->bed->bed_number }}
                            </span>
                        </div>
                        @else
                        <div class="w-full py-2 px-2.5 bg-slate-100/80 border border-dashed border-slate-300/80 rounded-xl text-[10px] font-semibold text-slate-400 flex items-center justify-center gap-1.5 select-none" title="Only doctors and hospital staff can book {{ $room->room_type }} beds">
                            <i class="fa-solid fa-lock text-[9px] text-slate-400"></i>
                            <span>Doctor / Staff Referral Only</span>
                        </div>
                        @endif
                        @endif
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
                            <option value="Emergency">Emergency</option>
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

    <!-- Modal 4: Switch Bed / Inpatient Transfer (Admin & Attending Doctor) -->
    <div x-show="switchModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="switchModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-arrows-rotate text-indigo-600"></i> Switch Inpatient Bed
            </h4>

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
                    <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Confirm Bed Transfer
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
