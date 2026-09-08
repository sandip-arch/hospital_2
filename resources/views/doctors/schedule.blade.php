@extends('layouts.app')

@section('title', 'Doctor Availability Schedule')
@section('header_title', 'Doctor Roster & Availabilities')
@section('header_subtitle', 'Configure active weekly shifts and consultation hours')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(Auth::user()->isAdmin() && $allDoctors->isNotEmpty())
    <!-- Doctor Selector for Admin -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <span class="text-xs font-bold text-slate-700">Managing Availability For:</span>
        <form action="{{ route('doctor.schedule') }}" method="GET" class="flex items-center gap-2">
            <select name="doctor_id" onchange="this.form.submit()" class="px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                @foreach($allDoctors as $d)
                <option value="{{ $d->id }}" {{ $doctor->id == $d->id ? 'selected' : '' }}>
                    {{ $d->full_name }} ({{ $d->department->name ?? 'General' }})
                </option>
                @endforeach
            </select>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-black text-slate-900">{{ $doctor->full_name }} - Weekly Consultation Roster</h3>
                <p class="text-xs text-slate-500">Department: {{ $doctor->department->name ?? 'Specialist' }} &bull; Fee: ${{ number_format($doctor->consultation_fee, 2) }}</p>
            </div>
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full border border-emerald-200">
                <i class="fa-solid fa-clock mr-1"></i> Active Roster
            </span>
        </div>

        <form action="{{ route('doctor.schedule.update') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">

            <div class="space-y-3">
                @foreach($days as $index => $day)
                @php
                    $avail = $doctor->availabilities->firstWhere('day_of_week', $day);
                    $isAvail = $avail ? $avail->is_available : in_array($day, ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
                    $startTime = $avail ? substr($avail->start_time, 0, 5) : '09:00';
                    $endTime = $avail ? substr($avail->end_time, 0, 5) : '17:00';
                @endphp
                <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <input type="hidden" name="schedules[{{ $index }}][day]" value="{{ $day }}">

                    <div class="flex items-center gap-3 w-40">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-800">
                            <input type="checkbox" name="schedules[{{ $index }}][is_available]" value="1" {{ $isAvail ? 'checked' : '' }}
                                   class="rounded text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                            <span>{{ $day }}</span>
                        </label>
                    </div>

                    <div class="flex items-center gap-3 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 font-semibold">Start:</span>
                            <input type="time" name="schedules[{{ $index }}][start_time]" value="{{ $startTime }}"
                                   class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-slate-500 font-semibold">End:</span>
                            <input type="time" name="schedules[{{ $index }}][end_time]" value="{{ $endTime }}"
                                   class="px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Weekly Schedule
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
