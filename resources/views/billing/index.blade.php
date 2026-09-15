@extends('layouts.app')

@section('title', Auth::user()->isPatient() ? 'My Medical Bills & Invoices' : 'Billing & Financial Ledger')
@section('header_title', Auth::user()->isPatient() ? 'My Medical Billing & Payments' : 'Billing & Invoicing Desk')
@section('header_subtitle', Auth::user()->isPatient() ? 'Personal medical invoices, consultant spending breakdown, and settled receipts' : 'Consolidated patient medical invoices, revenue collection, insurance claims, and payment receipts')

@section('content')
<div class="space-y-6">

    <!-- Financial KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ Auth::user()->isPatient() ? 'My Total Invoiced' : 'Total Invoiced' }}</p>
                <h4 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($stats['total_invoiced'], 2) }}</h4>
                <p class="text-[11px] text-slate-400 mt-1 font-semibold">{{ Auth::user()->isPatient() ? 'Your Total Medical Charges' : 'Gross Hospital Charges' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-file-invoice"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ Auth::user()->isPatient() ? 'My Total Settled' : 'Total Collected' }}</p>
                <h4 class="text-2xl font-black text-emerald-600 mt-1">${{ number_format($stats['total_collected'], 2) }}</h4>
                <p class="text-[11px] text-emerald-600 mt-1 font-semibold">{{ Auth::user()->isPatient() ? 'Your Completed Payments' : 'Settled Revenue' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">{{ Auth::user()->isPatient() ? 'My Balance Due' : 'Outstanding Balance' }}</p>
                <h4 class="text-2xl font-black text-rose-600 mt-1">${{ number_format($stats['total_unpaid'], 2) }}</h4>
                <p class="text-[11px] text-rose-600 mt-1 font-semibold">{{ Auth::user()->isPatient() ? 'Amount Pending Payment' : 'Unsettled Invoices' }}</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Unpaid Invoices</p>
                <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['unpaid_count'] }}</h4>
                <p class="text-[11px] text-amber-600 mt-1 font-semibold">Pending Settlement</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                <i class="fa-solid fa-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Spending Breakdown by Consultant (For Patients) -->
    @if(Auth::user()->isPatient() && isset($consultantSpendings) && $consultantSpendings->count() > 0)
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor text-cyan-600"></i> Spending by Consultant / Department
                </h4>
                <p class="text-xs text-slate-500">Breakdown of consultations and medical service expenditure</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                {{ $consultantSpendings->count() }} Doctors Consulted
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($consultantSpendings as $spending)
            <div class="p-4 rounded-2xl border border-slate-200/80 bg-slate-50/60 hover:bg-white transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold text-sm">
                            <i class="fa-solid fa-stethoscope"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-slate-900 text-sm">{{ $spending['doctor_name'] }}</h5>
                            <span class="text-[11px] text-slate-500">{{ $spending['department'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-slate-400 block text-[10px]">Visits</span>
                        <strong class="text-slate-800">{{ $spending['invoices_count'] }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Total Billed</span>
                        <strong class="text-slate-900 font-extrabold">${{ number_format($spending['total_amount'], 2) }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">Paid</span>
                        <strong class="text-emerald-600 font-extrabold">${{ number_format($spending['paid_amount'], 2) }}</strong>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Status Filter Pills -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold custom-scrollbar">
        @php
            $currentStatus = request('status', '');
            $hasActiveFilters = request('search') || request('status') || (request('date_preset') && request('date_preset') !== 'all') || request('date_from') || request('date_to') || (request('payment_method') && request('payment_method') !== 'all') || request('patient_id');
        @endphp

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => ''])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ empty($currentStatus) || $currentStatus === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span>All Invoices</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ empty($currentStatus) || $currentStatus === 'all' ? 'bg-slate-800 text-cyan-300' : 'bg-slate-100 text-slate-600' }}">
                {{ $statusCounts['all'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => 'unpaid'])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ $currentStatus === 'unpaid' ? 'bg-rose-600 text-white shadow-sm' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200' }}">
            <i class="fa-solid fa-circle-exclamation text-[11px]"></i>
            <span>Unpaid</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'unpaid' ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800' }}">
                {{ $statusCounts['unpaid'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => 'checking'])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ $currentStatus === 'checking' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
            <i class="fa-solid fa-clock-rotate-left text-[11px]"></i>
            <span>Under Verification</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'checking' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800' }}">
                {{ $statusCounts['checking'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => 'partially_paid'])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ $currentStatus === 'partially_paid' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-blue-700 hover:bg-blue-50 border border-blue-200' }}">
            <i class="fa-solid fa-percent text-[11px]"></i>
            <span>Partially Paid</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'partially_paid' ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-800' }}">
                {{ $statusCounts['partially_paid'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => 'paid'])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ $currentStatus === 'paid' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' }}">
            <i class="fa-solid fa-circle-check text-[11px]"></i>
            <span>Fully Settled</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'paid' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                {{ $statusCounts['paid'] ?? 0 }}
            </span>
        </a>

        <a href="{{ route('billing.index', array_merge(request()->except(['status', 'page']), ['status' => 'cancelled'])) }}"
           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 shrink-0 {{ $currentStatus === 'cancelled' ? 'bg-slate-700 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <span>Cancelled</span>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] {{ $currentStatus === 'cancelled' ? 'bg-slate-800 text-slate-300' : 'bg-slate-100 text-slate-600' }}">
                {{ $statusCounts['cancelled'] ?? 0 }}
            </span>
        </a>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4" x-data="{ showAdvanced: {{ request('date_from') || request('date_to') || request('payment_method') || request('sort_by') ? 'true' : 'false' }} }">
        <form action="{{ route('billing.index') }}" method="GET" class="space-y-4">
            @if(request('patient_id'))
            <input type="hidden" name="patient_id" value="{{ request('patient_id') }}">
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <!-- Search bar -->
                <div class="sm:col-span-6 lg:col-span-5 relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="{{ Auth::user()->isPatient() ? 'Search by invoice # or description...' : 'Search patient name, UPI, phone, invoice #, doctor...' }}">
                    @if(request('search'))
                    <a href="{{ route('billing.index', request()->except(['search', 'page'])) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs" title="Clear search">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                    @endif
                </div>

                <!-- Date Preset -->
                <div class="sm:col-span-3 lg:col-span-3">
                    <select name="date_preset" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold text-slate-700">
                        <option value="all" {{ request('date_preset', 'all') == 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="today" {{ request('date_preset') == 'today' ? 'selected' : '' }}>Today's Invoices</option>
                        <option value="yesterday" {{ request('date_preset') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_preset') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_preset') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('date_preset') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    </select>
                </div>

                <!-- Status Select (Complementary) -->
                <div class="sm:col-span-3 lg:col-span-2">
                    <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold text-slate-700">
                        <option value="" {{ empty(request('status')) ? 'selected' : '' }}>All Statuses</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="checking" {{ request('status') == 'checking' ? 'selected' : '' }}>Checking / QR Scan</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Fully Settled</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <!-- Search & Toggle Buttons -->
                <div class="sm:col-span-12 lg:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-magnifying-glass"></i> Search
                    </button>
                    <button type="button" @click="showAdvanced = !showAdvanced" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition" :class="showAdvanced ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : ''" title="Advanced Filters">
                        <i class="fa-solid fa-sliders"></i>
                    </button>
                    @if($hasActiveFilters)
                    <a href="{{ route('billing.index') }}" class="p-2.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-xl text-xs font-bold transition" title="Reset All Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Advanced Filter Drawer -->
            <div x-show="showAdvanced" x-transition class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs" x-cloak>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">From Date</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">To Date</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="all">All Payment Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="debit_card" {{ request('payment_method') == 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                        <option value="upi" {{ request('payment_method') == 'upi' ? 'selected' : '' }}>UPI / QR</option>
                        <option value="insurance" {{ request('payment_method') == 'insurance' ? 'selected' : '' }}>Insurance Claim</option>
                        <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Wire</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Sort Invoices By</label>
                    <select name="sort_by" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="latest" {{ request('sort_by', 'latest') == 'latest' ? 'selected' : '' }}>Newest Invoices First</option>
                        <option value="oldest" {{ request('sort_by') == 'oldest' ? 'selected' : '' }}>Oldest Invoices First</option>
                        <option value="amount_high" {{ request('sort_by') == 'amount_high' ? 'selected' : '' }}>Highest Total Amount</option>
                        <option value="amount_low" {{ request('sort_by') == 'amount_low' ? 'selected' : '' }}>Lowest Total Amount</option>
                        <option value="due_soon" {{ request('sort_by') == 'due_soon' ? 'selected' : '' }}>Due Date (Expiring Soonest)</option>
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex items-center justify-end gap-2 pt-2">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs transition">
                        Apply Advanced Filters
                    </button>
                </div>
            </div>
        </form>

        <!-- Active Filter Indicator Banner -->
        @if($hasActiveFilters)
        <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-slate-400 font-semibold">Active Criteria:</span>
                @if(request('search'))
                <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg font-bold flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-[10px] text-slate-400"></i> "{{ request('search') }}"
                </span>
                @endif
                @if(request('status'))
                <span class="px-2.5 py-1 bg-cyan-50 text-cyan-700 border border-cyan-200 rounded-lg font-bold">
                    Status: {{ ucfirst(str_replace('_', ' ', request('status'))) }}
                </span>
                @endif
                @if(request('date_preset') && request('date_preset') !== 'all')
                <span class="px-2.5 py-1 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg font-bold">
                    Date: {{ ucfirst(str_replace('_', ' ', request('date_preset'))) }}
                </span>
                @endif
                @if(request('date_from') || request('date_to'))
                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg font-bold">
                    Period: {{ request('date_from') ?? 'Beginning' }} &rarr; {{ request('date_to') ?? 'Present' }}
                </span>
                @endif
                @if(request('payment_method') && request('payment_method') !== 'all')
                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg font-bold">
                    Method: {{ ucfirst(str_replace('_', ' ', request('payment_method'))) }}
                </span>
                @endif
            </div>
            <a href="{{ route('billing.index') }}" class="text-rose-600 hover:text-rose-700 font-bold hover:underline flex items-center gap-1">
                <i class="fa-solid fa-xmark"></i> Clear All Filters
            </a>
        </div>
        @endif
    </div>

    @if(!Auth::user()->isPatient())
    <div class="flex justify-end">
        <a href="{{ route('billing.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-file-circle-plus"></i> Create Custom Invoice
        </a>
    </div>
    @endif

    <!-- Invoices & Transaction History Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-receipt text-cyan-600"></i> Financial Transaction & Invoice History
                </h4>
                <p class="text-xs text-slate-400 mt-0.5">Click any patient's name to view their complete profile, billing ledger, and transaction history</p>
            </div>
            <span class="text-xs font-bold px-3 py-1 bg-slate-100 text-slate-700 rounded-full">
                {{ $invoices->total() }} Records Found
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Invoice #</th>
                        @if(!Auth::user()->isPatient())
                        <th class="py-4 px-6">Patient (Dossier)</th>
                        @else
                        <th class="py-4 px-6">Consultant / Service</th>
                        @endif
                        <th class="py-4 px-6">Invoice Date & Due</th>
                        <th class="py-4 px-6">Billed Services</th>
                        <th class="py-4 px-6">Net Total</th>
                        <th class="py-4 px-6">Settled</th>
                        <th class="py-4 px-6">Balance Due</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 font-mono font-bold text-slate-900 text-sm">
                            <a href="{{ route('billing.show', $inv->id) }}" class="hover:text-cyan-600 transition flex items-center gap-1.5">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        @if(!Auth::user()->isPatient())
                        <td class="py-4 px-6">
                            @if($inv->patient)
                            <a href="{{ route('patients.show', ['id' => $inv->patient_id, 'tab' => 'billing', 'from' => 'billing']) }}"
                               class="font-bold text-slate-900 hover:text-cyan-600 transition inline-flex items-center gap-1.5 group"
                               title="Click to open patient profile and financial history">
                                <span>{{ $inv->patient->full_name }}</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400 group-hover:text-cyan-600 transition"></i>
                            </a>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-slate-400 font-mono">{{ $inv->patient->patient_code }}</span>
                                @if($inv->patient->phone)
                                <span class="text-[10px] text-slate-400">&bull; {{ $inv->patient->phone }}</span>
                                @endif
                            </div>
                            @else
                            <span class="font-bold text-slate-400 italic">No Patient Assigned</span>
                            @endif
                        </td>
                        @else
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block">
                                {{ $inv->appointment?->doctor?->user?->name ?? 'Hospital General Service' }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">{{ $inv->appointment?->department?->name ?? 'Facility Care' }}</span>
                        </td>
                        @endif
                        <td class="py-4 px-6 text-slate-600">
                            <div>{{ $inv->invoice_date->format('M d, Y') }}</div>
                            <div class="text-[10px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 max-w-[200px]">
                            @if($inv->items->isNotEmpty())
                            <div class="font-semibold text-slate-800 truncate" title="{{ $inv->items->pluck('item_description')->join(', ') }}">
                                {{ $inv->items->first()->item_description }}
                                @if($inv->items->count() > 1)
                                <span class="text-[10px] font-bold text-cyan-600">+{{ $inv->items->count() - 1 }} more</span>
                                @endif
                            </div>
                            @else
                            <span class="text-slate-400 italic">General Treatment</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">${{ number_format($inv->net_amount, 2) }}</td>
                        <td class="py-4 px-6 font-bold text-emerald-600">${{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="py-4 px-6 font-black text-rose-600">${{ number_format($inv->balance_due, 2) }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                            @if(!Auth::user()->isPatient() && in_array($inv->status, ['unpaid', 'partially_paid', 'checking']))
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-lg text-xs transition inline-block shadow-sm">
                                <i class="fa-solid fa-cash-register mr-1"></i> Settle
                            </a>
                            @else
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                View
                            </a>
                            @endif
                            <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="p-1.5 bg-slate-900 hover:bg-cyan-600 text-white rounded-lg text-xs transition inline-block" title="Print Receipt">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-14 text-center text-slate-400">
                            <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 text-slate-300"></i>
                            <p class="font-bold text-slate-700 text-sm">No invoices or transaction records found matching your filters.</p>
                            <p class="text-xs text-slate-400 mt-1">Try resetting or broadening your search criteria.</p>
                            @if($hasActiveFilters)
                            <a href="{{ route('billing.index') }}" class="mt-3 inline-block px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800 transition">
                                Reset All Filters
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
