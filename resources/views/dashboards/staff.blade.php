@extends('layouts.app')

@section('title', 'Staff Operations Center')
@section('header_title', 'Staff & Clinical Support Center')
@section('header_subtitle', 'Front desk admissions, nurse bed monitoring, pharmacy dispensing, and billing desk')

@section('content')
<div class="space-y-8">

    <!-- Staff Header & Sub-role identifier -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-950 to-emerald-950 rounded-3xl p-6 lg:p-8 text-white shadow-xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-18 h-18 p-4 rounded-3xl bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 text-3xl font-black flex items-center justify-center shrink-0">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-2xl font-black">{{ Auth::user()->name }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        {{ $staff->job_title ?? 'Clinical & Administrative Staff' }}
                    </span>
                    <!-- Sign-in Username Badge -->
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-950/80 text-emerald-300 border border-emerald-400/40 shadow-xs flex items-center gap-1.5" title="Use this username or email to sign in">
                        <i class="fa-solid fa-at text-emerald-400 text-[11px]"></i>Sign-in Username: <strong class="text-white font-mono font-bold">{{ Auth::user()->username }}</strong>
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1">Department: <span class="text-cyan-400 font-semibold">{{ $staff->department->name ?? 'General Operations' }}</span> &bull; Extension: {{ $staff->phone ?? '+1 (555) 019-9000' }}</p>
                <p class="text-[11px] text-slate-300 mt-0.5">Shift: Active Day Service &bull; Central Medical Grid &bull; Sign in with: <code class="text-emerald-300 font-mono font-bold">{{ Auth::user()->username }}</code></p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('patients.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> New Patient (UPI)
            </a>
            <a href="{{ route('appointments.create') }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-plus"></i> Counter Booking
            </a>
            <a href="{{ route('facilities.bed-tracker') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-bed-pulse"></i> Bed Tracker
            </a>
        </div>
    </div>

    <!-- Multi-Workstation Operational Queues -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <a href="{{ route('facilities.bed-tracker') }}" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:border-emerald-500 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Available Beds</p>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-110 flex items-center justify-center transition">
                    <i class="fa-solid fa-bed"></i>
                </div>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mt-2">{{ $stats['available_beds'] }} Free</h4>
            <p class="text-[11px] text-slate-400 mt-1">{{ $stats['occupied_beds'] }} Occupied &bull; Ward Monitor</p>
        </a>

        <a href="{{ route('pharmacy.dispense-queue') }}" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:border-blue-500 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pharmacy Rx Queue</p>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 group-hover:scale-110 flex items-center justify-center transition">
                    <i class="fa-solid fa-prescription-bottle-medical"></i>
                </div>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mt-2">{{ $stats['pending_prescriptions'] }} Active</h4>
            <p class="text-[11px] text-blue-600 font-semibold mt-1">Ready for Dispensing &rarr;</p>
        </a>

        <a href="{{ route('lab.requests') }}" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:border-purple-500 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Lab Diagnostics</p>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 group-hover:scale-110 flex items-center justify-center transition">
                    <i class="fa-solid fa-flask-vial"></i>
                </div>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mt-2">{{ $stats['pending_labs'] }} Pending</h4>
            <p class="text-[11px] text-purple-600 font-semibold mt-1">Input Results &rarr;</p>
        </a>

        <a href="{{ route('billing.index') }}" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:border-amber-500 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Cashier / Unpaid</p>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 group-hover:scale-110 flex items-center justify-center transition">
                    <i class="fa-solid fa-cash-register"></i>
                </div>
            </div>
            <h4 class="text-2xl font-black text-slate-900 mt-2">{{ $stats['unpaid_bills'] }} Invoices</h4>
            <p class="text-[11px] text-amber-600 font-semibold mt-1">Collect Payments &rarr;</p>
        </a>

    </div>

    <!-- Active Support Queues Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Front Desk & Reception Appointments Queue -->
        <div class="lg:col-span-6 bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-bell-concierge text-cyan-600"></i> Front Desk Check-in Desk
                    </h4>
                    <p class="text-xs text-slate-500">Today's scheduled counter arrivals</p>
                </div>
                <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">All Appointments</a>
            </div>

            <div class="divide-y divide-slate-100 text-xs">
                @forelse($todayAppointments as $apt)
                <div class="py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="font-bold text-slate-800">{{ $apt->patient->full_name }}</p>
                            <span class="font-mono text-[10px] text-slate-400">{{ $apt->patient->patient_code }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-0.5">Doctor: {{ $apt->doctor->full_name }} ({{ $apt->time_slot }})</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge }}">
                            {{ ucfirst($apt->status) }}
                        </span>
                        <a href="{{ route('appointments.show', $apt->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px]">
                            Details
                        </a>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-6 text-center">No arrivals remaining for today.</p>
                @endforelse
            </div>
        </div>

        <!-- Pharmacy Dispensing & Billing Queues -->
        <div class="lg:col-span-6 space-y-6">

            <!-- Active Prescriptions Queue -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-pills text-emerald-600"></i> Pharmacy Dispensing Counter
                        </h4>
                        <p class="text-[11px] text-slate-500">Prescriptions ready for verification & fulfillment</p>
                    </div>
                    <a href="{{ route('pharmacy.dispense-queue') }}" class="text-xs font-bold text-emerald-600 hover:underline">Dispense Queue</a>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($activePrescriptions as $rx)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-800">{{ $rx->patient->full_name }} ({{ $rx->patient->patient_code }})</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">By {{ $rx->doctor->full_name }} &bull; {{ $rx->items->count() }} Medication(s)</p>
                        </div>
                        <form action="{{ route('prescriptions.dispense', $rx->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs transition">
                                <i class="fa-solid fa-check mr-1"></i> Dispense
                            </button>
                        </form>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No pending prescriptions in queue.</p>
                    @endforelse
                </div>
            </div>

            <!-- Cashier Unpaid Invoices -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice-dollar text-amber-600"></i> Cashier Billing Desk
                        </h4>
                        <p class="text-[11px] text-slate-500">Unsettled patient medical bills</p>
                    </div>
                    <a href="{{ route('billing.index') }}" class="text-xs font-bold text-amber-600 hover:underline">Billing Terminal</a>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($recentInvoices as $inv)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</span>
                                <span class="font-semibold text-slate-600">{{ $inv->patient->full_name }}</span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Balance Due: <span class="text-rose-600 font-black">${{ number_format($inv->balance_due, 2) }}</span></p>
                        </div>
                        <a href="{{ route('billing.show', $inv->id) }}" class="px-3 py-1.5 bg-slate-900 hover:bg-cyan-600 text-white font-bold rounded-xl text-xs transition">
                            Collect Payment
                        </a>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No outstanding unpaid invoices.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
