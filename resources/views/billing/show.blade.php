@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number)
@section('header_title', 'Invoice #' . $invoice->invoice_number)
@section('header_subtitle', 'Patient: ' . $invoice->patient->full_name . ' • Net Total: $' . number_format($invoice->net_amount, 2))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{ payModal: false }">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('billing.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            @if($invoice->balance_due > 0)
            <button @click="payModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-credit-card"></i> Collect / Settle Payment
            </button>
            @endif
            <a href="{{ route('billing.print', $invoice->id) }}" target="_blank"
               class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print Invoice / Receipt
            </a>
        </div>
    </div>

    <!-- Master Invoice Box -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm space-y-6">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[10px] uppercase font-bold text-cyan-600 tracking-wider">Consolidated Patient Invoice</span>
                <h3 class="text-2xl font-black text-slate-900 font-mono mt-1">{{ $invoice->invoice_number }}</h3>
                <p class="text-xs text-slate-500 mt-1">Invoice Date: {{ $invoice->invoice_date->format('M d, Y') }} &bull; Due: {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>
            <div class="text-right">
                <span class="px-3.5 py-1 rounded-full text-xs font-black border {{ $invoice->status_badge }}">
                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                </span>
            </div>
        </div>

        <!-- Demographics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Billed To</span>
                <p class="font-bold text-slate-900 text-sm">{{ $invoice->patient->full_name }}</p>
                <p class="text-slate-500">UPI: <span class="font-mono font-bold">{{ $invoice->patient->patient_code }}</span> &bull; Phone: {{ $invoice->patient->phone }}</p>
                <p class="text-slate-500">{{ $invoice->patient->address ?? 'Address on file' }}</p>
            </div>

            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-1">
                <span class="text-[10px] uppercase font-bold text-slate-400 block">Financial Summary</span>
                <div class="flex justify-between"><span class="text-slate-500">Gross Total:</span> <span class="font-bold">${{ number_format($invoice->total_amount, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Amount Paid:</span> <span class="font-bold text-emerald-600">${{ number_format($invoice->paid_amount, 2) }}</span></div>
                <div class="flex justify-between pt-1 border-t border-slate-200"><span class="text-slate-700 font-bold">Balance Remaining:</span> <span class="font-black text-rose-600 text-sm">${{ number_format($invoice->balance_due, 2) }}</span></div>
            </div>
        </div>

        <!-- Line Items Table -->
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Itemized Hospital Services</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">#</th>
                            <th class="py-3 px-4">Service Description</th>
                            <th class="py-3 px-4">Quantity</th>
                            <th class="py-3 px-4">Unit Price</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($invoice->items as $idx => $item)
                        <tr>
                            <td class="py-3 px-4 font-bold text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $item->item_description }}</td>
                            <td class="py-3 px-4 text-slate-700">{{ $item->quantity }}</td>
                            <td class="py-3 px-4 text-slate-700">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="py-3 px-4 text-right font-black text-slate-900">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals Breakdown -->
        <div class="flex flex-col sm:flex-row justify-between items-start gap-4 pt-4 border-t border-slate-100 text-xs">
            <div class="text-slate-500 max-w-sm">
                @if($invoice->notes)
                <strong class="text-slate-800 block mb-1">Notes:</strong>
                <p>{{ $invoice->notes }}</p>
                @endif
            </div>

            <div class="w-full sm:w-64 space-y-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-700 font-semibold">
                    <span>Discount:</span>
                    <span>-${{ number_format($invoice->discount_amount, 2) }}</span>
                </div>
                @endif
                @if($invoice->tax_amount > 0)
                <div class="flex justify-between text-slate-600">
                    <span>Tax (5%):</span>
                    <span>+${{ number_format($invoice->tax_amount, 2) }}</span>
                </div>
                @endif
                <div class="pt-2 border-t border-slate-200 flex justify-between items-center">
                    <span class="font-extrabold text-slate-900">Net Amount:</span>
                    <span class="font-black text-base text-cyan-800">${{ number_format($invoice->net_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Payments History Ledger -->
        <div class="pt-4 border-t border-slate-100">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Settlement Transactions</h4>
            <div class="space-y-2">
                @forelse($invoice->payments as $pay)
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100 flex items-center justify-between text-xs">
                    <div>
                        <span class="font-mono font-bold text-slate-900">{{ $pay->payment_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ml-2 {{ $pay->method_badge }}">
                            {{ str_replace('_', ' ', $pay->payment_method) }}
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">Processed on {{ $pay->payment_date->format('M d, Y - h:i A') }} &bull; Ref: {{ $pay->transaction_reference }}</p>
                    </div>
                    <span class="font-black text-emerald-600 text-sm">${{ number_format($pay->amount_paid, 2) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-3 text-center">No payment transactions recorded yet.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Payment Collection Modal -->
    <div x-show="payModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="payModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-emerald-600"></i> Record / Pay Bill
            </h4>

            <form action="{{ route('billing.pay', $invoice->id) }}" method="POST" class="space-y-4">
                @csrf

                <div class="p-3 bg-slate-50 rounded-xl text-xs flex justify-between">
                    <span class="text-slate-500">Outstanding Due:</span>
                    <strong class="text-rose-600 font-black text-sm">${{ number_format($invoice->balance_due, 2) }}</strong>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Amount to Pay ($) *</label>
                    <input type="number" step="0.01" name="amount_paid" value="{{ $invoice->balance_due }}" max="{{ $invoice->balance_due }}" min="0.01" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Payment Method *</label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="cash">Cash (Counter)</option>
                        <option value="credit_card">Credit Card (Visa / Mastercard / Amex)</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="upi">UPI / QR Contactless</option>
                        <option value="insurance">Health Insurance Claim Settlement</option>
                        <option value="bank_transfer">Bank Wire Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Transaction Reference / Authorization Code</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. TXN-998822 or Claim #7712"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="payModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md">Complete Payment</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
