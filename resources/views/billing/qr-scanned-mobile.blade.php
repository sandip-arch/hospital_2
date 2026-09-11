<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Submitted - Invoice #{{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-3xl p-6 sm:p-8 shadow-xl border border-slate-100 text-center space-y-5">
        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-2xl shadow-inner">
            <i class="fa-solid fa-check"></i>
        </div>

        <div>
            <span class="text-[11px] font-black uppercase tracking-wider text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                Verification Pending
            </span>
            <h2 class="text-2xl font-black text-slate-900 mt-3">Payment Submitted!</h2>
            <p class="text-xs text-slate-500 mt-1">Thank you, your contactless transaction has been received.</p>
        </div>

        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-left space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-500">Invoice Ref:</span>
                <span class="font-mono font-bold text-slate-900">{{ $invoice->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Patient:</span>
                <span class="font-bold text-slate-800">{{ $invoice->patient->full_name }}</span>
            </div>
            <div class="flex justify-between pt-2 border-t border-slate-200">
                <span class="text-slate-700 font-bold">Amount Transferred:</span>
                <span class="text-base font-black text-cyan-800">${{ number_format($pendingPayment->amount_paid, 2) }}</span>
            </div>
        </div>

        <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-200/80 text-amber-800 text-xs flex items-start gap-2.5 text-left">
            <i class="fa-solid fa-clock text-amber-600 mt-0.5"></i>
            <div>
                <strong class="font-bold block">Front Desk Verification in Progress</strong>
                <p class="text-[11px] text-amber-700 mt-0.5">Hospital receptionist / cashier will verify this transaction shortly. You will receive an instant notification upon approval.</p>
            </div>
        </div>

        <div class="pt-2">
            <a href="{{ route('billing.show', $invoice->id) }}" class="inline-block w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-md">
                View Invoice on Portal
            </a>
        </div>
    </div>
</body>
</html>
