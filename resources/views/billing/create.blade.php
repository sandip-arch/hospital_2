@extends('layouts.app')

@section('title', 'Create Patient Invoice')
@section('header_title', 'Create Consolidated Invoice')
@section('header_subtitle', 'Generate itemized medical bills for consultations, diagnostics, pharmacy, or procedures')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="invoiceBuilder()">

    <div class="flex items-center justify-between">
        <a href="{{ route('billing.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Invoices
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('billing.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Patient & Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patient *</label>
                    <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">-- Choose Patient --</option>
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}" {{ request('patient_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->full_name }} ({{ $p->patient_code }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Invoice Date *</label>
                    <input type="date" name="invoice_date" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Due Date *</label>
                    <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <!-- Dynamic Line Items -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-list-ol text-cyan-600"></i> Itemized Medical Services & Charges
                    </h4>
                    <button type="button" @click="addItem()" class="px-3.5 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 font-bold text-xs rounded-xl border border-cyan-200 flex items-center gap-1.5 transition">
                        <i class="fa-solid fa-plus"></i> Add Charge Line
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                            <div class="sm:col-span-6">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Item / Service Description *</label>
                                <input type="text" :name="'items[' + index + '][item_description]'" x-model="item.item_description" required placeholder="e.g. Doctor Consultation / Blood Test / Room Daily Rate"
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500 font-semibold">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Qty *</label>
                                <input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Price ($) *</label>
                                <input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" min="0" required
                                       class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            </div>

                            <div class="sm:col-span-2 flex items-center justify-between pt-4 sm:pt-0">
                                <div>
                                    <span class="text-[10px] font-bold text-slate-400 block uppercase">Subtotal</span>
                                    <span class="font-bold text-slate-900 text-xs" x-text="'$' + (item.quantity * item.unit_price).toFixed(2)"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-rose-500 hover:text-rose-700 text-xs font-bold p-1">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Totals & Calculations -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Invoice Notes / Payment Instructions</label>
                    <textarea name="notes" rows="3" placeholder="e.g. Net 7 days payment terms. Co-pay settled via insurance." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3 text-xs">
                    <div class="flex justify-between text-slate-600">
                        <span>Items Subtotal:</span>
                        <strong class="text-slate-900" x-text="'$' + calculateSubtotal().toFixed(2)"></strong>
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Discount ($):</span>
                        <input type="number" step="0.01" name="discount_amount" x-model.number="discount" min="0"
                               class="w-24 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-right text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div class="flex items-center justify-between text-slate-600">
                        <span>Tax Amount (5%):</span>
                        <input type="number" step="0.01" name="tax_amount" x-model.number="tax" min="0"
                               class="w-24 px-2.5 py-1 bg-white border border-slate-200 rounded-lg text-right text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                        <span class="text-sm font-black text-slate-900">Total Net Amount:</span>
                        <span class="text-lg font-black text-cyan-700" x-text="'$' + calculateNet().toFixed(2)"></span>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('billing.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-receipt"></i> Generate & Issue Invoice
                </button>
            </div>

        </form>

    </div>

</div>

<script>
    function invoiceBuilder() {
        return {
            discount: 0.00,
            tax: 0.00,
            items: [
                { item_description: 'Doctor Specialist Consultation', quantity: 1, unit_price: 120.00 }
            ],
            addItem() {
                this.items.push({ item_description: '', quantity: 1, unit_price: 0.00 });
            },
            removeItem(index) {
                if (this.items.length > 1) {
                    this.items.splice(index, 1);
                }
            },
            calculateSubtotal() {
                return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price || 0), 0);
            },
            calculateNet() {
                const subtotal = this.calculateSubtotal();
                return Math.max(0, subtotal - (this.discount || 0) + (this.tax || 0));
            }
        }
    }
</script>
@endsection
