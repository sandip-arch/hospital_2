@extends('layouts.app')

@section('title', Auth::user()->isPatient() ? 'My Prescribed Medications' : 'Pharmacy & Drug Inventory')
@section('header_title', Auth::user()->isPatient() ? 'My Prescriptions & Medicine Refills' : 'Pharmacy & Medicine Inventory')
@section('header_subtitle', Auth::user()->isPatient() ? 'Doctor prescribed medications, dosage details, unit pricing, and hospital pharmacy fulfillment' : 'Drug catalog, real-time stock levels, low-stock reorder thresholds, and restock batches')

@section('content')
<div class="space-y-6" x-data="{ 
    addModal: false, 
    adjustModal: false, 
    orderModal: false, 
    selectedMedId: null, 
    selectedMedName: '', 
    selectedMedStock: 0,
    selectedMedPrice: 0 
}">

    <!-- KPI Summary Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @if(Auth::user()->isPatient())
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Prescribed Medicines</span>
            <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_medicines'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-amber-600 uppercase">Active Prescriptions</span>
            <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['active_prescriptions'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">Dispensed Courses</span>
            <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['dispensed_prescriptions'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-cyan-600 uppercase">Hospital Pharmacy</span>
            <h4 class="text-base font-black text-slate-900 mt-1 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Open 24/7</h4>
        </div>
        @else
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Drugs</span>
            <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_medicines'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Inventory Units</span>
            <h4 class="text-2xl font-black text-blue-600 mt-1">{{ number_format($stats['total_stock_units']) }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-rose-600 uppercase">Low Stock Alerts</span>
            <h4 class="text-2xl font-black text-rose-600 mt-1">{{ $stats['low_stock_count'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">Pending Dispensing</span>
            <h4 class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['pending_prescriptions'] }}</h4>
        </div>
        @endif
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('pharmacy.index') }}" method="GET" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="{{ Auth::user()->isPatient() ? 'sm:col-span-8' : 'sm:col-span-6' }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="{{ Auth::user()->isPatient() ? 'Search your prescribed medications...' : 'Search drug by brand or generic name...' }}">
                </div>
            </div>

            <div class="{{ Auth::user()->isPatient() ? 'sm:col-span-4' : 'sm:col-span-3' }}">
                <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-bold">
                    <option value="">All Drug Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            @if(!Auth::user()->isPatient())
            <div class="sm:col-span-3 flex items-center">
                <label class="flex items-center gap-2 text-xs font-bold text-rose-700 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded text-rose-600 focus:ring-rose-500">
                    <span>Show Low Stock Only</span>
                </label>
            </div>
            @endif
        </form>

        @if(!Auth::user()->isPatient())
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pharmacy.dispense-queue') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-prescription-bottle-medical"></i> Dispense Queue
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->isStaff())
            <button @click="addModal = true" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Add Medicine
            </button>
            @endif
        </div>
        @endif
    </div>

    <!-- Inventory / Prescribed Meds Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Medicine Name</th>
                        <th class="py-4 px-6">Generic Name</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Unit Price</th>
                        @if(!Auth::user()->isPatient())
                        <th class="py-4 px-6">Stock Quantity</th>
                        <th class="py-4 px-6">Reorder Threshold</th>
                        @endif
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicines as $med)
                    <tr class="hover:bg-slate-50/80 transition {{ !Auth::user()->isPatient() && $med->isLowStock() ? 'bg-rose-50/20' : '' }}">
                        <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-cyan-50 text-cyan-700 flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-pills"></i>
                                </div>
                                <span>{{ $med->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 text-slate-600 font-medium">
                            {{ $med->generic_name ?? '-' }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $med->category }}
                            </span>
                        </td>
                        <td class="py-4 px-6 font-bold text-slate-900">${{ number_format($med->unit_price, 2) }}</td>
                        
                        @if(!Auth::user()->isPatient())
                        <td class="py-4 px-6">
                            @if($med->isLowStock())
                            <span class="px-2.5 py-1 rounded-xl font-black bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1">
                                <i class="fa-solid fa-triangle-exclamation text-[10px]"></i> {{ $med->stock_quantity }} Units
                            </span>
                            @else
                            <span class="font-bold text-slate-900">{{ $med->stock_quantity }} Units</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-slate-500 font-medium">{{ $med->reorder_level }} Units</td>
                        <td class="py-4 px-6 text-right space-x-1">
                            <button @click="selectedMedId = {{ $med->id }}; selectedMedName = '{{ addslashes($med->name) }}'; selectedMedStock = {{ $med->stock_quantity }}; adjustModal = true"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-cyan-50 hover:text-cyan-700 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                <i class="fa-solid fa-boxes-stacked mr-1"></i> Stock Adjust
                            </button>
                        </td>
                        @else
                        <td class="py-4 px-6 text-right">
                            <button @click="selectedMedId = {{ $med->id }}; selectedMedName = '{{ addslashes($med->name) }}'; selectedMedPrice = {{ $med->unit_price }}; orderModal = true"
                                    class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 ml-auto">
                                <i class="fa-solid fa-cart-plus"></i> Request Refill
                            </button>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ Auth::user()->isPatient() ? '5' : '7' }}" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-pills text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">{{ Auth::user()->isPatient() ? 'You have no doctor-prescribed medications in pharmacy records yet.' : 'No pharmaceutical medicines found.' }}</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($medicines->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $medicines->links() }}
        </div>
        @endif
    </div>

    @if(Auth::user()->isPatient())
    <!-- Modal: Patient Refill / Order Modal -->
    <div x-show="orderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="orderModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-cyan-600"></i> Request Medication Refill
            </h4>

            <form :action="'{{ url('pharmacy') }}/' + selectedMedId + '/order'" method="POST" class="space-y-4">
                @csrf

                <div class="p-4 bg-cyan-50/50 rounded-2xl border border-cyan-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-cyan-700 uppercase">Selected Drug</span>
                        <h5 class="font-bold text-slate-900 text-sm" x-text="selectedMedName"></h5>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 block">Price / Unit</span>
                        <strong class="text-slate-900 text-sm" x-text="'$' + selectedMedPrice.toFixed(2)"></strong>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (Units / Strips / Bottles) *</label>
                    <input type="number" name="quantity" min="1" max="10" value="1" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery Instructions or Notes</label>
                    <textarea name="delivery_instructions" rows="2" placeholder="e.g. Please dispense along with my active prescription course"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="orderModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md">Confirm Refill Order</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    @if(!Auth::user()->isPatient())
    <!-- Modal 1: Add Medicine (Staff/Admin) -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="addModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-pills text-cyan-600"></i> Add Medicine to Inventory
            </h4>

            <form action="{{ route('pharmacy.store') }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Brand Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Lipitor 20mg"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Generic Name</label>
                        <input type="text" name="generic_name" placeholder="e.g. Atorvastatin"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Category *</label>
                        <input type="text" name="category" required placeholder="e.g. Cardiovascular"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Unit Price ($) *</label>
                        <input type="number" step="0.01" name="unit_price" required placeholder="24.00"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Initial Stock Quantity *</label>
                        <input type="number" name="stock_quantity" min="0" required placeholder="100"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Reorder Threshold *</label>
                        <input type="number" name="reorder_level" min="1" value="20" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Batch Expiry Date</label>
                    <input type="date" name="expiry_date"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md">Add Medicine</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal 2: Stock Adjustment (Staff/Admin) -->
    <div x-show="adjustModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="adjustModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-boxes-stacked text-cyan-600"></i> Adjust Stock for <span x-text="selectedMedName" class="text-cyan-700"></span>
            </h4>

            <form :action="'{{ url('pharmacy') }}/' + selectedMedId + '/adjust'" method="POST" class="space-y-4">
                @csrf

                <div class="p-3 bg-slate-50 rounded-xl text-xs flex justify-between">
                    <span class="text-slate-500">Current In Stock:</span>
                    <strong class="text-slate-900" x-text="selectedMedStock + ' units'"></strong>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Adjustment Type *</label>
                    <select name="adjustment_type" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="add">Add Shipment / Restock (+)</option>
                        <option value="subtract">Subtract Inventory / Wastage (-)</option>
                        <option value="set">Set Exact Count (=)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity *</label>
                    <input type="number" name="quantity" min="1" required placeholder="e.g. 50"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Reason / Supplier Batch Ref</label>
                    <input type="text" name="reason" placeholder="e.g. Monthly supplier delivery #BATCH-88"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="adjustModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>
@endsection
