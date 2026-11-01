@extends('layouts.app')

@section('title', Auth::user()->isPatient() ? 'Pharmacy & Medicine Orders' : 'Pharmacy & Drug Inventory')
@section('header_title', Auth::user()->isPatient() ? 'Pharmacy & Medicine Refills' : 'Pharmacy & Medicine Inventory')
@section('header_subtitle', Auth::user()->isPatient() ? 'Browse pharmaceutical medicines, check live hospital stock, purchase medications, and track refill orders' : 'Drug catalog, real-time stock levels, low-stock reorder thresholds, and prescription dispensing queue')

@section('content')
<div class="space-y-6" x-data="{ 
    addModal: false, 
    adjustModal: false, 
    orderModal: false, 
    cancelModal: false,
    selectedMedId: null, 
    selectedMedName: '', 
    selectedMedStock: 0,
    selectedMedPrice: 0,
    selectedQty: 1,
    selectedOrderId: null,
    selectedOrderName: '',
    cancelReason: ''
}">

    <!-- Alerts -->
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2 shadow-xs">
        <i class="fa-solid fa-circle-check text-base text-emerald-600"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2 shadow-xs">
        <i class="fa-solid fa-circle-exclamation text-base text-rose-600"></i>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- KPI Summary Bar -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @if(Auth::user()->isPatient())
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Available Drugs</span>
            <h4 class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total_medicines'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-amber-600 uppercase">Active Prescriptions</span>
            <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['active_prescriptions'] }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-cyan-600 uppercase">My Medicine Orders</span>
            <h4 class="text-2xl font-black text-cyan-600 mt-1">{{ $stats['my_orders_count'] ?? 0 }}</h4>
        </div>
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-emerald-600 uppercase">Hospital Pharmacy</span>
            <h4 class="text-base font-black text-slate-900 mt-1 flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Open 24/7</h4>
        </div>
        @else
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Total Catalog</span>
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
            <span class="text-[11px] font-bold text-amber-600 uppercase">Pending Orders</span>
            <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $stats['pending_orders_count'] ?? 0 }}</h4>
        </div>
        @endif
    </div>

    @if(Auth::user()->isPatient())
    <!-- Patient Scope Toggle (Doctor Prescribed vs Full Catalog) -->
    <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
        <a href="{{ route('pharmacy.index', ['scope' => 'prescribed']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($scope ?? 'prescribed') === 'prescribed' ? 'bg-cyan-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-stethoscope"></i> My Prescribed Refills
        </a>
        <a href="{{ route('pharmacy.index', ['scope' => 'all']) }}" 
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($scope ?? '') === 'all' ? 'bg-cyan-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200' }}">
            <i class="fa-solid fa-pills"></i> All Hospital Medicines & OTC Catalog
        </a>
    </div>
    @endif

    <!-- Filters & Actions -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('pharmacy.index') }}" method="GET" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
            @if(Auth::user()->isPatient() && !empty($scope))
            <input type="hidden" name="scope" value="{{ $scope }}">
            @endif

            <div class="{{ Auth::user()->isPatient() ? 'sm:col-span-6' : 'sm:col-span-5' }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                           placeholder="Search drug by brand or generic name...">
                </div>
            </div>

            <div class="sm:col-span-3">
                <select name="category" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-bold">
                    <option value="">All Drug Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="stock_status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 font-bold">
                    <option value="">All Stock Availability</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                    <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock Only</option>
                </select>
            </div>

            @if(!Auth::user()->isPatient())
            <div class="sm:col-span-1 flex items-center">
                <label class="flex items-center gap-1.5 text-[11px] font-bold text-rose-700 cursor-pointer whitespace-nowrap" title="Show Low Stock Only">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded text-rose-600 focus:ring-rose-500">
                    <span>Low</span>
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

    <!-- Inventory / Drug Catalog Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden" data-live-results>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Medicine Name</th>
                        <th class="py-4 px-6">Generic Name</th>
                        <th class="py-4 px-6">Category</th>
                        <th class="py-4 px-6">Unit Price</th>
                        <th class="py-4 px-6">Stock Availability</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($medicines as $med)
                    <tr class="hover:bg-slate-50/80 transition {{ $med->isOutOfStock() ? 'bg-rose-50/20' : ($med->isLowStock() ? 'bg-amber-50/20' : '') }}">
                        <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg {{ $med->isOutOfStock() ? 'bg-rose-100 text-rose-600' : 'bg-cyan-50 text-cyan-700' }} flex items-center justify-center font-bold text-xs shrink-0">
                                    <i class="fa-solid fa-pills"></i>
                                </div>
                                <div>
                                    <span class="block">{{ $med->name }}</span>
                                    @if(Auth::user()->isPatient() && isset($prescribedMedicineIds) && $prescribedMedicineIds->contains($med->id))
                                    <span class="text-[9.5px] font-bold text-cyan-700 uppercase tracking-wide">
                                        <i class="fa-solid fa-check text-[8px]"></i> On Your Prescription
                                    </span>
                                    @endif
                                </div>
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
                        
                        <!-- Stock Availability -->
                        <td class="py-4 px-6">
                            @if($med->isOutOfStock())
                            <span class="px-2.5 py-1 rounded-xl font-black bg-rose-100 text-rose-800 border border-rose-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-xmark text-rose-600 text-[10px]"></i> Out of Stock (0)
                            </span>
                            @elseif($med->isLowStock())
                            <span class="px-2.5 py-1 rounded-xl font-black bg-amber-100 text-amber-800 border border-amber-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600 text-[10px]"></i> Low Stock ({{ $med->stock_quantity }} left)
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-xl font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 inline-flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-[10px]"></i> In Stock ({{ $med->stock_quantity }} units)
                            </span>
                            @endif
                        </td>

                        <td class="py-4 px-6 text-right space-x-1">
                            @if(Auth::user()->isPatient())
                                @if($med->isOutOfStock())
                                <button type="button" disabled 
                                        class="px-3.5 py-1.5 bg-slate-100 text-slate-400 font-bold rounded-xl text-xs cursor-not-allowed opacity-60 flex items-center gap-1.5 ml-auto"
                                        title="Currently unavailable in pharmacy stock">
                                    <i class="fa-solid fa-ban"></i> Out of Stock
                                </button>
                                @else
                                <button type="button"
                                        @click="selectedMedId = {{ $med->id }}; selectedMedName = '{{ addslashes($med->name) }}'; selectedMedStock = {{ $med->stock_quantity }}; selectedMedPrice = {{ $med->unit_price }}; selectedQty = 1; orderModal = true"
                                        class="px-3.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center gap-1.5 ml-auto">
                                    <i class="fa-solid fa-cart-shopping"></i> Buy / Order
                                </button>
                                @endif
                            @else
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button"
                                            @click="selectedMedId = {{ $med->id }}; selectedMedName = '{{ addslashes($med->name) }}'; selectedMedStock = {{ $med->stock_quantity }}; selectedMedPrice = {{ $med->unit_price }}; selectedQty = 1; orderModal = true"
                                            class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-800 border border-cyan-200 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                        <i class="fa-solid fa-cart-plus"></i> Order
                                    </button>
                                    <button type="button"
                                            @click="selectedMedId = {{ $med->id }}; selectedMedName = '{{ addslashes($med->name) }}'; selectedMedStock = {{ $med->stock_quantity }}; adjustModal = true"
                                            class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                                        <i class="fa-solid fa-boxes-stacked"></i> Adjust
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-pills text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No pharmaceutical medicines found matching criteria.</p>
                            @if(Auth::user()->isPatient() && ($scope ?? '') === 'prescribed')
                            <p class="text-xs text-slate-400 mt-1">You have no active doctor-prescribed medications yet. <a href="{{ route('pharmacy.index', ['scope' => 'all']) }}" class="text-cyan-600 underline font-bold">Browse the full pharmacy catalog</a></p>
                            @endif
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

    <!-- Patient Section: My Orders & Refill History -->
    @if(Auth::user()->isPatient())
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-heading font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-cyan-600"></i> My Medicine Orders & Refills History
                </h4>
                <p class="text-[11px] text-slate-400">Track your order statuses, pharmacy preparation, and cancel unconfirmed orders with instant stock rollback.</p>
            </div>
            <span class="text-xs font-bold text-slate-500 font-mono">{{ count($myOrders ?? []) }} Orders Recorded</span>
        </div>

        @if(!empty($myOrders) && count($myOrders) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Medicine Name</th>
                        <th class="py-3 px-4">Quantity</th>
                        <th class="py-3 px-4">Total Price</th>
                        <th class="py-3 px-4">Ordered Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($myOrders as $order)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">#ORD-{{ $order->id }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $order->medicine?->name ?? 'Medicine #' . $order->medicine_id }}
                            @if($order->delivery_instructions)
                            <span class="block text-[10px] text-slate-400 font-normal truncate max-w-xs">{{ $order->delivery_instructions }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-700">{{ $order->quantity }} Units</td>
                        <td class="py-3.5 px-4 font-black text-slate-900">${{ number_format($order->total_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $order->status_badge }}">
                                {{ $order->status }}
                            </span>
                            @if($order->status === 'cancelled' && $order->cancellation_reason)
                            <span class="block text-[9px] text-rose-500 mt-0.5 truncate max-w-xs">Reason: {{ $order->cancellation_reason }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            @if($order->status === 'pending')
                            <form action="{{ route('pharmacy.orders.cancel', $order->id) }}" method="POST" onsubmit="return confirm('Cancel this medicine order? Stock will be immediately restored to pharmacy inventory.')">
                                @csrf
                                <input type="hidden" name="reason" value="Cancelled by patient request">
                                <button type="submit" class="px-3 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto">
                                    <i class="fa-solid fa-ban text-[10px]"></i> Cancel Order
                                </button>
                            </form>
                            @else
                            <span class="text-[11px] text-slate-400 italic">No action</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-8 text-center text-slate-400">
            <i class="fa-solid fa-basket-shopping text-3xl mb-2 text-slate-300"></i>
            <p class="font-bold text-slate-700 text-xs">No orders placed yet</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Pick any available medicine above to buy or request a refill.</p>
        </div>
        @endif
    </div>
    @endif

    <!-- Staff/Admin Section: Recent Orders & Fulfillment Status Processing -->
    @if(!Auth::user()->isPatient() && !empty($recentOrders))
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-heading font-black text-slate-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-dolly text-purple-600"></i> Pharmacy Refill & Dispense Orders Desk
                </h4>
                <p class="text-[11px] text-slate-400">Patient medicine orders received online. Update fulfillment status or cancel with automatic stock restoration.</p>
            </div>
            <span class="text-xs font-bold text-slate-500 font-mono">{{ count($recentOrders) }} Recent Orders</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Patient / User</th>
                        <th class="py-3 px-4">Medicine</th>
                        <th class="py-3 px-4">Qty</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Update Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders as $ro)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">#ORD-{{ $ro->id }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $ro->patient?->full_name ?? $ro->user?->name ?? 'Guest/User' }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $ro->medicine?->name ?? 'Medicine #' . $ro->medicine_id }}
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-700">{{ $ro->quantity }} Units</td>
                        <td class="py-3.5 px-4 font-black text-slate-900">${{ number_format($ro->total_price, 2) }}</td>
                        <td class="py-3.5 px-4 text-slate-500">{{ $ro->created_at->format('M d, Y h:i A') }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $ro->status_badge }}">
                                {{ $ro->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <form action="{{ route('pharmacy.orders.status', $ro->id) }}" method="POST" class="inline-flex items-center gap-1.5">
                                @csrf
                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] font-bold focus:bg-white focus:outline-none">
                                    <option value="pending" {{ $ro->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $ro->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="dispensed" {{ $ro->status === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                                    <option value="cancelled" {{ $ro->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No orders recorded yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Universal Modal: Buy / Refill Medicine -->
    <div x-show="orderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="orderModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-cyan-600"></i> Place Medicine Purchase & Refill
            </h4>

            <form :action="'{{ url('pharmacy') }}/' + selectedMedId + '/buy'" method="POST" class="space-y-4">
                @csrf

                <!-- Drug Information Header -->
                <div class="p-4 bg-cyan-50/50 rounded-2xl border border-cyan-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-cyan-700 uppercase">Selected Drug</span>
                        <h5 class="font-bold text-slate-900 text-sm" x-text="selectedMedName"></h5>
                        <div class="flex items-center gap-1.5 mt-1 text-[11px]">
                            <span class="text-slate-500">Live Stock:</span>
                            <span class="font-black" :class="selectedMedStock > 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="selectedMedStock > 0 ? selectedMedStock + ' Units Available' : 'Out of Stock'"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 block">Unit Price</span>
                        <strong class="text-slate-900 text-sm" x-text="'$' + selectedMedPrice.toFixed(2)"></strong>
                    </div>
                </div>

                <!-- Quantity Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Quantity (Units / Strips / Bottles) *</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="quantity" min="1" :max="selectedMedStock" x-model.number="selectedQty" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>
                    <template x-if="selectedQty > selectedMedStock">
                        <p class="text-[11px] text-rose-600 font-bold mt-1">Warning: Requested quantity exceeds pharmacy stock (Max: <span x-text="selectedMedStock"></span> units).</p>
                    </template>
                </div>

                <!-- Live Subtotal Calculation -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between text-xs">
                    <span class="font-bold text-slate-600">Calculated Total Price:</span>
                    <strong class="text-slate-900 font-mono text-base font-black" x-text="'$' + (Math.max(1, selectedQty) * selectedMedPrice).toFixed(2)"></strong>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Delivery / Dispense Instructions (Optional)</label>
                    <textarea name="delivery_instructions" rows="2" placeholder="e.g. Please hold for in-person pickup at hospital outpatient counter"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="orderModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" :disabled="selectedQty > selectedMedStock || selectedMedStock <= 0" 
                            class="px-5 py-2 bg-cyan-600 hover:bg-cyan-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-xs rounded-xl shadow-md transition">
                        Confirm Purchase & Reserve Stock
                    </button>
                </div>
            </form>
        </div>
    </div>

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
