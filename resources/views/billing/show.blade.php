@extends('layouts.app')

@section('title', 'Invoice #' . $invoice->invoice_number)
@section('header_title', 'Invoice #' . $invoice->invoice_number)
@section('header_subtitle', 'Patient: ' . $invoice->patient->full_name . ' • Net Total: $' . number_format($invoice->net_amount, 2))

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="invoiceBillingHandler()">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('billing.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
        <div class="flex items-center gap-2">
            @if($invoice->status === 'checking')
                <span class="px-4 py-2.5 bg-amber-50 text-amber-800 border border-amber-300 text-xs font-bold rounded-xl flex items-center gap-2 shadow-sm animate-pulse">
                    <i class="fa-solid fa-clock text-amber-600"></i> Verification Pending
                </span>
            @elseif($invoice->balance_due > 0)
                @if(Auth::user()->isPatient())
                <button @click="openQrModal()" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-qrcode"></i> Pay Online (QR)
                </button>
                @endif
                @if(Auth::user()->canVerifyPayments())
                <button @click="payModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-cash-register"></i> Collect at Counter
                </button>
                @endif
            @endif
            <a href="{{ route('billing.print', $invoice->id) }}" target="_blank"
               class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-print"></i> Print Invoice / Receipt
            </a>
        </div>
    </div>

    <!-- Staff Verification Banner (When Checking) -->
    @if($invoice->status === 'checking')
        @if(Auth::user()->canVerifyPayments())
        <div class="p-5 bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 rounded-3xl border-2 border-amber-300 shadow-sm space-y-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-amber-500 text-white flex items-center justify-center font-bold text-xl shadow-md shrink-0 animate-pulse">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-amber-700 tracking-wider">Action Required &bull; Front Desk Terminal</span>
                        <h4 class="text-base font-black text-slate-900 mt-0.5">Online QR Payment Verification Needed</h4>
                        <p class="text-xs text-slate-600 mt-1">
                            Patient <strong class="text-slate-900">{{ $invoice->patient->full_name }}</strong> has submitted a payment of 
                            <strong class="text-emerald-700 font-black text-sm">${{ number_format($invoice->latestPendingPayment()?->amount_paid ?? $invoice->balance_due, 2) }}</strong> 
                            via QR / UPI (Ref: <span class="font-mono font-bold text-slate-800">{{ $invoice->latestPendingPayment()?->transaction_reference ?? 'QR-TRANSACTION' }}</span>).
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('billing.verify', $invoice->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i class="fa-solid fa-check"></i> Approve Payment
                        </button>
                    </form>
                    <button type="button" @click="declineModal = true" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                        <i class="fa-solid fa-xmark"></i> Decline
                    </button>
                </div>
            </div>
        </div>
        @else
        <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-sm shrink-0 animate-pulse">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <strong class="font-bold text-slate-900">Payment Verification In Progress</strong>
                <p class="text-[11px] text-slate-600 mt-0.5">Your contactless QR payment of ${{ number_format($invoice->latestPendingPayment()?->amount_paid ?? $invoice->balance_due, 2) }} has been submitted. The hospital front desk receptionist is reviewing and confirming the transaction.</p>
            </div>
        </div>
        @endif
    @endif

    <!-- Patient Declined Payment Warning Banner -->
    @if($invoice->status === 'unpaid' && $invoice->latestRejectedPayment())
    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-200 text-rose-800 text-xs flex items-start gap-3">
        <div class="w-8 h-8 rounded-xl bg-rose-200 text-rose-800 flex items-center justify-center font-bold text-sm shrink-0 mt-0.5">
            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
        </div>
        <div class="space-y-1">
            <strong class="font-bold text-rose-900 block">Previous Payment Attempt Was Declined</strong>
            <p class="text-xs text-rose-800">
                <span class="font-semibold text-rose-900">Front Desk Note:</span> 
                "{{ $invoice->latestRejectedPayment()->rejection_reason ?? 'Payment verification was not approved.' }}"
            </p>
            @if(Auth::user()->isPatient())
            <p class="text-[11px] text-rose-600">Please click <strong>Pay Online (QR)</strong> above to re-scan and try again, or settle in-person at the front desk cashier.</p>
            @else
            <p class="text-[11px] text-rose-600">The patient has been notified of this reason and may re-submit payment online or settle at the front desk counter.</p>
            @endif
        </div>
    </div>
    @endif

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
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ml-1 {{ $pay->status_badge }}">
                            {{ $pay->status }}
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            Processed on {{ $pay->payment_date->format('M d, Y - h:i A') }} &bull; Ref: {{ $pay->transaction_reference }}
                            @if($pay->rejection_reason)
                            &bull; <span class="text-rose-600 font-semibold">Declined: {{ $pay->rejection_reason }}</span>
                            @endif
                        </p>
                    </div>
                    <span class="font-black {{ $pay->status === 'completed' ? 'text-emerald-600' : ($pay->status === 'pending' ? 'text-amber-600' : 'text-slate-400') }} text-sm">
                        ${{ number_format($pay->amount_paid, 2) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-3 text-center">No payment transactions recorded yet.</p>
                @endforelse
            </div>
        </div>

    </div>

    @if(Auth::user()->isPatient())
    <!-- DEMO QR PAYMENT MODAL -->
    <div x-show="qrModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="closeQrModal()" class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-slate-100 text-center space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <span class="text-xs font-black uppercase text-cyan-600 tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-qrcode"></i> Scan to Pay
                </span>
                <button type="button" @click="closeQrModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div>
                <p class="text-xs text-slate-500">Scan this QR code with any camera or UPI app:</p>
                <div class="text-xl font-black text-slate-900 mt-1">${{ number_format($invoice->balance_due, 2) }}</div>
                <div class="text-[11px] font-mono text-slate-400">Ref: {{ $invoice->invoice_number }}</div>
            </div>

            <!-- Crisp QR Box -->
            <div class="p-4 bg-white rounded-2xl border-2 border-dashed border-cyan-300 inline-block shadow-sm">
                @php
                    $currentHost = request()->getHost();
                    if (in_array($currentHost, ['127.0.0.1', 'localhost', '::1'])) {
                        $lanIp = gethostbyname(gethostname());
                        $port = request()->getPort();
                        $portStr = ($port && $port != 80 && $port != 443) ? ":{$port}" : '';
                        $qrTargetUrl = request()->getScheme() . "://{$lanIp}{$portStr}/billing/{$invoice->id}/qr-scan";
                    } else {
                        $qrTargetUrl = route('billing.qr-scan', $invoice->id);
                    }
                    $qrImg = "https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=" . urlencode($qrTargetUrl);
                @endphp
                <img src="{{ $qrImg }}" alt="Scan QR to Pay" class="w-48 h-48 mx-auto rounded-lg shadow-inner" />
            </div>

            <div class="space-y-1">
                <span class="text-[10px] font-mono text-cyan-700 font-bold block bg-cyan-50 py-1 px-2 rounded-lg border border-cyan-100 break-all">
                    {{ $qrTargetUrl }}
                </span>
                <p class="text-[10px] text-slate-400">Ensure your mobile phone is connected to the same Wi-Fi.</p>
            </div>

            <div class="p-2.5 bg-cyan-50/80 rounded-xl border border-cyan-200/60 text-[11px] text-cyan-800 flex items-center justify-center gap-2">
                <i class="fa-solid fa-spinner fa-spin text-cyan-600"></i>
                <span>Listening for mobile scan... Modal will auto-close</span>
            </div>

            <!-- 1-Click Simulation Button (For Desktop Demo) -->
            <div class="pt-1">
                <button type="button" @click="simulateScan()" :disabled="simulating"
                        class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                    <template x-if="!simulating">
                        <span><i class="fa-solid fa-bolt text-amber-400"></i> Simulate Phone Scan</span>
                    </template>
                    <template x-if="simulating">
                        <span><i class="fa-solid fa-spinner fa-spin"></i> Submitting...</span>
                    </template>
                </button>
                <p class="text-[10px] text-slate-400 mt-1">One-click simulation for testing on desktop</p>
            </div>
        </div>
    </div>
    @endif

    <!-- DECLINE REASON MODAL (Staff) -->
    <div x-show="declineModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="declineModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <h4 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-circle-xmark text-rose-600"></i> Decline Online Payment
            </h4>

            <form action="{{ route('billing.verify', $invoice->id) }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" value="decline">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Declining *</label>
                    <textarea name="rejection_reason" rows="3" required placeholder="e.g. Reference code mismatch, amount not credited, duplicate submission..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1">This explanation will be sent directly to the patient's notification center.</p>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="declineModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs rounded-xl shadow-md">Confirm Decline</button>
                </div>
            </form>
        </div>
    </div>

    <!-- IN-PERSON CASHIER PAYMENT MODAL (Staff) -->
    <div x-show="payModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="payModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cash-register text-emerald-600"></i> Record In-Person Payment
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
                        <option value="credit_card">Credit Card (POS Terminal)</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="upi">UPI / QR Contactless</option>
                        <option value="insurance">Health Insurance Claim Settlement</option>
                        <option value="bank_transfer">Bank Wire Transfer</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Transaction Reference / Receipt Code</label>
                    <input type="text" name="transaction_reference" placeholder="e.g. RCPT-998822 or Terminal Trace #7712"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="payModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md">Complete Settlement</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function invoiceBillingHandler() {
    return {
        payModal: false,
        qrModal: false,
        declineModal: false,
        simulating: false,
        pollTimer: null,

        openQrModal() {
            this.qrModal = true;
            this.startPolling();
        },

        closeQrModal() {
            this.qrModal = false;
            this.stopPolling();
        },

        startPolling() {
            this.stopPolling();
            this.pollTimer = setInterval(() => {
                fetch("{{ route('billing.status', $invoice->id) }}")
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'checking') {
                            this.closeQrModal();
                            window.location.reload();
                        }
                    })
                    .catch(err => console.log('Poll error:', err));
            }, 2000);
        },

        stopPolling() {
            if (this.pollTimer) {
                clearInterval(this.pollTimer);
                this.pollTimer = null;
            }
        },

        simulateScan() {
            this.simulating = true;
            fetch("{{ route('billing.qr-scan', $invoice->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(res => res.json())
            .then(data => {
                this.simulating = false;
                this.closeQrModal();
                window.location.reload();
            })
            .catch(err => {
                this.simulating = false;
                console.error(err);
                alert('Simulation error. Please check connection.');
            });
        }
    }
}
</script>
@endsection
