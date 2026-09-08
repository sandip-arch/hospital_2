@extends('layouts.app')

@section('title', 'Patients Directory')
@section('header_title', 'Patients Master Directory')
@section('header_subtitle', 'Unique Patient Identification (UPI), demographics, emergency contacts, and medical histories')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        
        <!-- Filter Form -->
        <form action="{{ route('patients.index') }}" method="GET" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="Search by UPI code, name, phone, or email...">
                </div>
            </div>

            <div class="sm:col-span-3">
                <select name="gender" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Genders</option>
                    <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ request('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="blood_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="">All Blood Types</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                    <option value="{{ $bt }}" {{ request('blood_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <div class="flex items-center gap-2 shrink-0">
            <button type="submit" onclick="document.forms[0].submit()" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('patients.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> Register Patient
            </a>
        </div>
    </div>

    <!-- Patients Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">UPI Code</th>
                        <th class="py-4 px-6">Patient Name</th>
                        <th class="py-4 px-6">Age / Gender</th>
                        <th class="py-4 px-6">Blood Type</th>
                        <th class="py-4 px-6">Phone & Email</th>
                        <th class="py-4 px-6">Emergency Contact</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patients as $p)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6">
                            <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                                {{ $p->patient_code }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                            <a href="{{ route('patients.show', $p->id) }}" class="hover:text-cyan-600 transition">
                                {{ $p->full_name }}
                            </a>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            {{ $p->age }} yrs &bull; {{ $p->gender }}
                        </td>
                        <td class="py-4 px-6">
                            @if($p->blood_type)
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                {{ $p->blood_type }}
                            </span>
                            @else
                            <span class="text-slate-400">N/A</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            <div>{{ $p->phone }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $p->email ?? 'No email' }}</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            @if($p->emergencyContacts->isNotEmpty())
                            <div class="font-semibold">{{ $p->emergencyContacts->first()->contact_name }} ({{ $p->emergencyContacts->first()->relationship }})</div>
                            <div class="text-[10px] text-slate-400">{{ $p->emergencyContacts->first()->phone }}</div>
                            @else
                            <span class="text-slate-400">None on file</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            <a href="{{ route('patients.show', $p->id) }}" class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                <i class="fa-solid fa-id-card"></i> Dossier
                            </a>
                            <a href="{{ route('patients.edit', $p->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-users text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No patients found.</p>
                            <p class="text-xs text-slate-400 mt-1">Register a new patient to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($patients->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $patients->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
