@extends('layouts.app')

@section('title', 'Clinical Departments')
@section('header_title', 'Hospital Departments Directory')
@section('header_subtitle', 'Clinical service divisions, specialty centers, and resource allocations')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-base">Hospital Departments</h3>
            <p class="text-xs text-slate-500">Configure medical divisions and staff assignments</p>
        </div>
        <button @click="addModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
            <i class="fa-solid fa-plus"></i> Create Department
        </button>
    </div>

    <!-- Departments Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($departments as $dept)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md hover:border-purple-500/50 transition">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 font-black text-lg flex items-center justify-center">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Active
                    </span>
                </div>

                <h4 class="font-bold text-slate-900 text-base">{{ $dept->name }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $dept->description ?? 'Specialized clinical hospital division providing inpatient and outpatient healthcare services.' }}</p>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-3 gap-2 text-center text-xs">
                <div class="p-2 bg-slate-50 rounded-xl">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Doctors</span>
                    <strong class="text-slate-900 text-sm">{{ $dept->doctors_count }}</strong>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Staff</span>
                    <strong class="text-slate-900 text-sm">{{ $dept->staff_count }}</strong>
                </div>
                <div class="p-2 bg-slate-50 rounded-xl">
                    <span class="text-[10px] text-slate-400 font-bold block uppercase">Rooms</span>
                    <strong class="text-slate-900 text-sm">{{ $dept->rooms_count }}</strong>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Department Modal -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="addModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-hospital text-purple-600"></i> Create Clinical Department
            </h4>

            <form action="{{ route('admin.departments.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Department Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Oncology & Hematology"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Scope / Description</label>
                    <textarea name="description" rows="3" placeholder="Overview of services, equipment, and medical capabilities..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md">Create Department</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
