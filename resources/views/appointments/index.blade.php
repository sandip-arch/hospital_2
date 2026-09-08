@extends('layouts.app')

@section('title', 'Appointments Management')
@section('header_title', 'Clinical Appointments')
@section('header_subtitle', 'Schedule patient visits, manage doctor queues, and track consultation statuses')

@section('content')
<div class="space-y-6" x-data="{ statusModal: false, selectedAptId: null, selectedAptStatus: '' }">

    <!-- Header Actions & Filters -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <form action="{{ route('appointments.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Date</label>
                <input type="date" name="date" value="{{ request('date') }}"
                       class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Department</label>
                <select name="department_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Departments</option>
                    @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Doctor</label>
                <select name="doctor_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Doctors</option>
                    @foreach($doctors as $doc)
                    <option value="{{ $doc->id }}" {{ request('doctor_id') == $doc->id ? 'selected' : '' }}>{{ $doc->full_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Statuses</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>No Show</option>
                </select>
            </div>

            <div class="sm:col-span-12 flex items-center justify-between pt-2 border-t border-slate-100 mt-2">
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition">
                        <i class="fa-solid fa-filter mr-1"></i> Filter Appointments
                    </button>
                    @if(request()->hasAny(['date', 'doctor_id', 'department_id', 'status']))
                    <a href="{{ route('appointments.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                        Reset Filters
                    </a>
                    @endif
                </div>

                <a href="{{ route('appointments.create') }}" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-calendar-plus"></i> Book New Visit
                </a>
            </div>
        </form>
    </div>

    <!-- Appointments Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Date & Slot</th>
                        <th class="py-4 px-6">Patient</th>
                        <th class="py-4 px-6">Doctor & Speciality</th>
                        <th class="py-4 px-6">Reason / Notes</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($appointments as $apt)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">{{ $apt->appointment_date->format('M d, Y') }}</span>
                            <span class="text-[11px] font-mono text-cyan-600 font-semibold">{{ $apt->time_slot }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <a href="{{ route('patients.show', $apt->patient_id) }}" class="font-bold text-slate-900 hover:text-cyan-600 text-sm block">
                                {{ $apt->patient->full_name }}
                            </a>
                            <span class="font-mono text-[10px] text-slate-400 font-semibold">{{ $apt->patient->patient_code }} &bull; {{ $apt->patient->phone }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-800 block">{{ $apt->doctor->full_name }}</span>
                            <span class="text-[10px] text-slate-500">{{ $apt->department->name ?? 'Specialist' }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-600 max-w-xs truncate">
                            {{ $apt->reason ?? 'Routine Medical Visit' }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge }}">
                                {{ ucfirst($apt->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <a href="{{ route('appointments.show', $apt->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                View
                            </a>
                            @if(Auth::user()->isDoctor() || Auth::user()->isAdmin() || Auth::user()->isStaff())
                            <button @click="selectedAptId = {{ $apt->id }}; selectedAptStatus = '{{ $apt->status }}'; statusModal = true" 
                                    class="px-2.5 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 font-bold rounded-lg text-xs transition inline-block">
                                Status
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No appointments matching the selected criteria.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($appointments->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $appointments->links() }}
        </div>
        @endif
    </div>

    <!-- Status Change Modal -->
    <div x-show="statusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="statusModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-cyan-600"></i> Update Appointment Status
            </h4>

            <form :action="'{{ url('appointments') }}/' + selectedAptId + '/status'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Status</label>
                    <select name="status" x-model="selectedAptStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="scheduled">Scheduled</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="no_show">No Show</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Doctor / Receptionist Notes</label>
                    <textarea name="doctor_notes" rows="3" placeholder="e.g. Patient checked in or consulted." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="statusModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md">Save Status</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
