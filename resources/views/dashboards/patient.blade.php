@extends('layouts.app')

@section('title', 'Patient Health Portal')
@section('header_title', 'My Health Portal')
@section('header_subtitle', 'Personal medical records, upcoming consultations, active prescriptions, and billing')

@section('content')
<div class="space-y-8">

    <!-- Virtual Patient Identity Card -->
    <div class="bg-gradient-to-tr from-slate-900 via-slate-950 to-cyan-950 rounded-3xl p-6 lg:p-8 text-white shadow-2xl border border-cyan-500/20 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-0">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-3xl font-black shadow-lg shadow-cyan-500/30 shrink-0">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="text-2xl font-black">{{ $patient->full_name }}</h3>
                        <span class="px-3 py-1 bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 rounded-full text-xs font-mono font-bold">
                            UPI: {{ $patient->patient_code }}
                        </span>
                        <!-- Sign-in Username Badge -->
                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/40 rounded-full text-xs font-mono font-bold flex items-center gap-1.5" title="Use this username or email to sign in">
                            <i class="fa-solid fa-at text-cyan-400 text-[11px]"></i>Sign-in Username: <strong class="text-white font-mono font-bold">{{ Auth::user()->username }}</strong>
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-300 mt-2">
                        <span><i class="fa-solid fa-cake-candles text-cyan-400 mr-1"></i> {{ $patient->age }} yrs ({{ $patient->dob ? $patient->dob->format('M d, Y') : 'N/A' }})</span>
                        <span><i class="fa-solid fa-venus-mars text-cyan-400 mr-1"></i> {{ $patient->gender }}</span>
                        <span><i class="fa-solid fa-droplet text-rose-400 mr-1"></i> Blood: <strong class="text-white">{{ $patient->blood_type ?? 'Unknown' }}</strong></span>
                        <span><i class="fa-solid fa-phone text-cyan-400 mr-1"></i> {{ $patient->phone }}</span>
                        <span><i class="fa-solid fa-key text-cyan-400 mr-1"></i> Login: <code class="text-cyan-300 font-mono font-bold">{{ Auth::user()->username }}</code></span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('appointments.create') }}" class="px-5 py-3 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs rounded-2xl shadow-lg shadow-cyan-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-calendar-plus"></i> Book Consultation
                </a>
            </div>
        </div>
    </div>

    <!-- Patient Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left: Upcoming Appointments & Active Prescriptions -->
        <div class="lg:col-span-8 space-y-8">

            <!-- Upcoming Appointments -->
            <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-calendar-check text-cyan-600"></i> My Upcoming Consultations
                        </h4>
                        <p class="text-xs text-slate-500">Scheduled clinical appointments</p>
                    </div>
                    <a href="{{ route('appointments.create') }}" class="text-xs font-bold text-cyan-600 hover:underline">+ New Visit</a>
                </div>

                <div class="space-y-4">
                    @forelse($upcomingAppointments as $apt)
                    <div class="p-5 rounded-2xl border border-slate-200/80 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-800 font-black text-sm flex items-center justify-center shrink-0">
                                {{ $apt->appointment_date->format('d M') }}
                            </div>
                            <div>
                                <h5 class="font-bold text-slate-900 text-sm">{{ $apt->doctor->full_name }}</h5>
                                <p class="text-xs text-cyan-600 font-semibold mt-0.5">{{ $apt->department->name ?? 'Specialist' }} &bull; Slot: {{ $apt->time_slot }}</p>
                                <p class="text-xs text-slate-500 mt-1">Reason: {{ $apt->reason ?? 'General Medical Visit' }}</p>
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $apt->status_badge }}">
                            {{ ucfirst($apt->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400">
                        <i class="fa-solid fa-calendar-xmark text-3xl mb-2 text-slate-300"></i>
                        <p class="text-xs font-semibold text-slate-600">You have no upcoming appointments.</p>
                        <a href="{{ route('appointments.create') }}" class="mt-2 inline-block text-xs font-bold text-cyan-600 hover:underline">Schedule one now &rarr;</a>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Active e-Prescriptions -->
            <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <i class="fa-solid fa-prescription text-emerald-600"></i> My Medical Prescriptions
                        </h4>
                        <p class="text-xs text-slate-500">Doctor issued medication guidelines</p>
                    </div>
                    <a href="{{ route('prescriptions.index') }}" class="text-xs font-bold text-emerald-600 hover:underline">All Prescriptions</a>
                </div>

                <div class="space-y-4">
                    @forelse($prescriptions as $rx)
                    <div class="p-5 rounded-2xl border border-slate-200/80 bg-white hover:border-emerald-500/50 transition">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">Rx #{{ $rx->id }}</span>
                                <span class="text-xs text-slate-400">by {{ $rx->doctor->full_name }} ({{ $rx->prescribed_date->format('M d, Y') }})</span>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $rx->status_badge }}">
                                {{ ucfirst($rx->status) }}
                            </span>
                        </div>

                        <div class="divide-y divide-slate-100 text-xs bg-slate-50 rounded-xl p-3">
                            @foreach($rx->items as $item)
                            <div class="py-2 flex items-center justify-between">
                                <div>
                                    <span class="font-bold text-slate-800">{{ $item->medicine->name ?? 'Medicine' }}</span>
                                    <span class="text-slate-500 font-mono ml-2">({{ $item->dosage }})</span>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $item->frequency }} &bull; {{ $item->duration_days }} Days &bull; {{ $item->instructions }}</p>
                                </div>
                                <span class="font-bold text-slate-700">Qty: {{ $item->quantity_prescribed }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-6 text-center">No prescriptions on record.</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Right: Published Lab Results & Invoices / Online Pay -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Published Diagnostics -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-flask-vial text-purple-600"></i> My Diagnostic Reports
                    </h4>
                    <a href="{{ route('lab.requests') }}" class="text-xs font-bold text-purple-600 hover:underline">All Tests</a>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($labReports as $lab)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center justify-between">
                            <p class="font-bold text-slate-800">{{ $lab->test->test_name }}</p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lab->status_badge }}">
                                {{ ucfirst($lab->status) }}
                            </span>
                        </div>
                        @if($lab->status === 'completed')
                        <p class="text-[11px] text-slate-600 mt-1 line-clamp-2">{{ $lab->result_summary }}</p>
                        <div class="mt-2 text-right">
                            <a href="{{ route('lab.reports.show', $lab->id) }}" class="text-xs font-bold text-purple-600 hover:underline">
                                View Official Report &rarr;
                            </a>
                        </div>
                        @else
                        <p class="text-[10px] text-amber-600 mt-1 font-semibold">Processing sample in laboratory...</p>
                        @endif
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No diagnostic reports on record.</p>
                    @endforelse
                </div>
            </div>

            <!-- Billing & Direct Payment -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-receipt text-cyan-600"></i> Invoices & Payments
                    </h4>
                    <a href="{{ route('billing.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">All Bills</a>
                </div>

                <div class="space-y-3 text-xs">
                    @forelse($invoices as $inv)
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] text-slate-500">
                            <span>Total: ${{ number_format($inv->net_amount, 2) }}</span>
                            <span>Due: <strong class="text-rose-600">${{ number_format($inv->balance_due, 2) }}</strong></span>
                        </div>

                        <div class="pt-1 flex items-center justify-end gap-2">
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-3 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-100">
                                View
                            </a>
                            @if($inv->balance_due > 0)
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-3 py-1 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg text-[11px] font-bold shadow-sm">
                                Pay Online
                            </a>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-4 text-center">No invoices issued.</p>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
