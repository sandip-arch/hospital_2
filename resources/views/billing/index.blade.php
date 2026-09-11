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

    <!-- Filters & Actions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('billing.index') }}" method="GET" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-8">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="{{ Auth::user()->isPatient() ? 'Search by invoice number...' : 'Search by invoice number or patient name...' }}">
                </div>
            </div>

            <div class="sm:col-span-4">
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-bold">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="checking" {{ request('status') == 'checking' ? 'selected' : '' }}>Under Verification (Checking)</option>
                    <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Fully Settled / Paid</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </form>

        @if(!Auth::user()->isPatient())
        <a href="{{ route('billing.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-file-circle-plus"></i> Create Custom Invoice
        </a>
        @endif
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Invoice #</th>
                        @if(!Auth::user()->isPatient())
                        <th class="py-4 px-6">Patient</th>
                        @else
                        <th class="py-4 px-6">Consultant / Service</th>
                        @endif
                        <th class="py-4 px-6">Invoice Date & Due</th>
                        <th class="py-4 px-6">Net Amount</th>
                        <th class="py-4 px-6">Amount Settled</th>
                        <th class="py-4 px-6">Balance Due</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($invoices as $inv)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 font-mono font-bold text-slate-900 text-sm">
                            <a href="{{ route('billing.show', $inv->id) }}" class="hover:text-cyan-600">
                                {{ $inv->invoice_number }}
                            </a>
                        </td>
                        @if(!Auth::user()->isPatient())
                        <td class="py-4 px-6">
                            <a href="{{ route('patients.show', $inv->patient_id) }}" class="font-bold text-slate-900 hover:text-cyan-600">
                                {{ $inv->patient->full_name }}
                            </a>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $inv->patient->patient_code }}</span>
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
                        <td class="py-4 px-6 font-extrabold text-slate-900 text-sm">${{ number_format($inv->net_amount, 2) }}</td>
                        <td class="py-4 px-6 font-bold text-emerald-600">${{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="py-4 px-6 font-black text-rose-600">${{ number_format($inv->balance_due, 2) }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-1.5">
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                View
                            </a>
                            <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="p-1.5 bg-slate-900 hover:bg-cyan-600 text-white rounded-lg text-xs transition inline-block" title="Print Receipt">
                                <i class="fa-solid fa-print"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-file-invoice-dollar text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No invoices found matching criteria.</p>
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
