@extends('layouts.app')

@section('title', 'Provision New User')
@section('header_title', 'Create User Account')
@section('header_subtitle', 'Provision credentials and assign Role-Based Access Control (RBAC) privileges')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ selectedRole: 'staff' }">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Users Directory
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Assign System Role *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($roles as $r)
                    <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center gap-2"
                           :class="selectedRole === '{{ $r->name }}' ? 'bg-purple-50 border-purple-500 text-purple-900 font-bold' : 'border-slate-200 text-slate-700 hover:bg-slate-50'">
                        <input type="radio" name="role" value="{{ $r->name }}" x-model="selectedRole" required class="text-purple-600 focus:ring-purple-500">
                        <span class="text-xs">{{ $r->display_name ?? ucfirst($r->name) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Basic Credentials -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Dr. Jennifer Adams"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="j.adams@hospital.test"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="e.g. jadams"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Temporary Password *</label>
                    <input type="password" name="password" required placeholder="Minimum 8 characters"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <!-- Doctor Specific Details -->
            <div x-show="selectedRole === 'doctor'" class="p-5 rounded-2xl bg-cyan-50/50 border border-cyan-200 space-y-4" x-cloak>
                <h4 class="text-xs font-black uppercase tracking-wider text-cyan-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-user-doctor"></i> Doctor Clinical Profile
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Clinical Department *</label>
                        <select name="doctor_department_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Medical Specialization *</label>
                        <input type="text" name="specialization" placeholder="e.g. Interventional Cardiology"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">License Number *</label>
                        <input type="text" name="license_number" placeholder="e.g. MD-2026-991"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Consultation Fee ($) *</label>
                        <input type="number" step="0.01" name="consultation_fee" placeholder="150.00"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Phone *</label>
                        <input type="text" name="doctor_phone" placeholder="+1 (555) 019-2000"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>
            </div>

            <!-- Staff Specific Details -->
            <div x-show="selectedRole === 'staff'" class="p-5 rounded-2xl bg-emerald-50/50 border border-emerald-200 space-y-4" x-cloak>
                <h4 class="text-xs font-black uppercase tracking-wider text-emerald-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-hospital-user"></i> Staff Support Profile
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Job Title *</label>
                        <input type="text" name="job_title" placeholder="e.g. Senior Nurse / Head Pharmacist"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Assigned Department</label>
                        <select name="staff_department_id" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">-- General Operations --</option>
                            @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 mb-1">Extension Phone</label>
                        <input type="text" name="staff_phone" placeholder="+1 (555) 019-3000"
                               class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Provision Account
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
