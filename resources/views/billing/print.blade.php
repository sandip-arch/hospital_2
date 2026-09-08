<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hospital Tax Invoice - {{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 md:p-12 text-slate-800 text-sm">

    <div class="max-w-3xl mx-auto bg-white p-8 md:p-12 shadow-md rounded-2xl print:shadow-none print:p-0">
        
        <div class="flex items-center justify-between no-print mb-8 pb-4 border-b border-slate-200">
            <span class="text-xs text-slate-500 font-bold">Printable Hospital Tax Invoice & Receipt</span>
            <button onclick="window.print()" class="px-4 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl hover:bg-slate-800">
                Print Invoice
            </button>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Apex Horizon Medical Center</h1>
                <p class="text-xs text-slate-500">500 Health Sciences Blvd, Boston MA 02115 &bull; Phone: +1 (800) 555-APEX</p>
                <p class="text-xs text-cyan-700 font-bold uppercase tracking-wider mt-1">Hospital Inpatient & Outpatient Invoicing Desk</p>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block uppercase font-bold">Tax Invoice</span>
                <span class="text-lg font-mono font-black text-slate-900">{{ $invoice->invoice_number }}</span>
            </div>
        </div>

        <!-- Billed to and Dates -->
        <div class="grid grid-cols-2 gap-4 py-6 border-b border-slate-200 text-xs">
            <div>
                <strong class="text-slate-400 uppercase block mb-1">Billed To (Patient):</strong>
                <p class="text-sm font-bold text-slate-900">{{ $invoice->patient->full_name }}</p>
                <p class="text-slate-600">UPI: <span class="font-mono font-bold">{{ $invoice->patient->patient_code }}</span></p>
                <p class="text-slate-600">Phone: {{ $invoice->patient->phone }}</p>
                <p class="text-slate-500">{{ $invoice->patient->address ?? 'Address on record' }}</p>
            </div>
            <div>
                <strong class="text-slate-400 uppercase block mb-1">Invoice Details:</strong>
                <p><strong class="text-slate-700">Invoice Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                <p><strong class="text-slate-700">Payment Due:</strong> {{ $invoice->due_date->format('M d, Y') }}</p>
                <p><strong class="text-slate-700">Payment Status:</strong> <span class="uppercase font-bold text-cyan-800">{{ $invoice->status }}</span></p>
            </div>
        </div>

        <!-- Itemized Charges -->
        <div class="py-6 border-b border-slate-200">
            <table class="w-full text-left text-xs border border-slate-200 rounded-lg overflow-hidden">
                <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">Service Item Description</th>
                        <th class="py-2.5 px-3">Qty</th>
                        <th class="py-2.5 px-3">Unit Price</th>
                        <th class="py-2.5 px-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($invoice->items as $idx => $item)
                    <tr>
                        <td class="py-2.5 px-3 font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->item_description }}</td>
                        <td class="py-2.5 px-3">{{ $item->quantity }}</td>
                        <td class="py-2.5 px-3">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="py-2.5 px-3 text-right font-bold text-slate-900">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Calculation -->
        <div class="py-6 border-b border-slate-200 flex justify-end text-xs">
            <div class="w-64 space-y-1.5">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-bold text-slate-900">${{ number_format($invoice->total_amount, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                <div class="flex justify-between text-emerald-700">
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
                <div class="pt-2 border-t border-slate-300 flex justify-between font-black text-slate-900 text-sm">
                    <span>Net Amount:</span>
                    <span>${{ number_format($invoice->net_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-bold text-emerald-700">
                    <span>Paid to Date:</span>
                    <span>${{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                <div class="flex justify-between font-black text-rose-700 text-sm pt-1 border-t border-slate-200">
                    <span>Balance Due:</span>
                    <span>${{ number_format($invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="pt-12 flex items-end justify-between text-xs">
            <div>
                <p class="text-slate-400">Computer generated official medical receipt.</p>
                <p class="text-[10px] text-slate-400">All payments are subject to hospital audit verification.</p>
            </div>
            <div class="text-center w-56 border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-900">Hospital Accounts Officer</p>
                <p class="text-slate-500 text-[11px]">Authorized Cashier Seal</p>
            </div>
        </div>

    </div>

</body>
</html>
