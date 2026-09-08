@extends('layouts.app')

@section('title', 'Pharmacy Dispensing Queue')
@section('header_title', 'Pharmacy Dispensing Counter')
@section('header_subtitle', 'Verify prescription medications, check inventory stock, and dispense drugs')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-base">Active Prescriptions Awaiting Fulfillment</h3>
            <p class="text-xs text-slate-500">Dispense medicines to automatically deduct stock from pharmacy inventory</p>
        </div>
        <a href="{{ route('pharmacy.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
            <i class="fa-solid fa-boxes-stacked mr-1"></i> Inventory Catalog
        </a>
    </div>

    <!-- Prescriptions Queue -->
    <div class="space-y-4">
        @forelse($prescriptions as $rx)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-6 hover:border-emerald-500/50 transition">
            <div class="space-y-3 flex-1">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs font-black text-slate-900 bg-slate-100 px-3 py-1 rounded-xl">
                        Rx #{{ $rx->id }}
                    </span>
                    <h4 class="font-bold text-slate-900 text-base">{{ $rx->patient->full_name }}</h4>
                    <span class="text-xs text-slate-400 font-mono">UPI: {{ $rx->patient->patient_code }}</span>
                </div>

                <p class="text-xs text-slate-500">Prescribed by: <strong class="text-slate-800">{{ $rx->doctor->full_name }}</strong> ({{ $rx->doctor->department->name ?? 'General' }}) &bull; Date: {{ $rx->prescribed_date->format('M d, Y') }}</p>

                <!-- Drug Items -->
                <div class="bg-slate-50 rounded-2xl p-4 divide-y divide-slate-100 text-xs">
                    @foreach($rx->items as $item)
                    <div class="py-2 flex items-center justify-between">
                        <div>
                            <strong class="text-slate-900">{{ $item->medicine->name }}</strong> ({{ $item->dosage }})
                            <p class="text-[11px] text-slate-500">{{ $item->frequency }} &bull; {{ $item->duration_days }} Days &bull; Instructions: {{ $item->instructions }}</p>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-slate-900 block">Dispense: {{ $item->quantity_prescribed }} Units</span>
                            <span class="text-[10px] text-slate-400">Available: {{ $item->medicine->stock_quantity }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-col items-end gap-3 shrink-0">
                <form action="{{ route('prescriptions.dispense', $rx->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs rounded-2xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-2">
                        <i class="fa-solid fa-check"></i> Fulfill & Dispense Drugs
                    </button>
                </form>

                <a href="{{ route('prescriptions.print', $rx->id) }}" target="_blank" class="text-xs font-bold text-slate-500 hover:text-slate-800 flex items-center gap-1">
                    <i class="fa-solid fa-print"></i> Print Rx Slip
                </a>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="fa-solid fa-circle-check text-4xl mb-3 text-emerald-400"></i>
            <p class="font-semibold text-slate-700 text-base">All prescriptions have been dispensed!</p>
            <p class="text-xs text-slate-400 mt-1">No pending medication orders in the pharmacy queue.</p>
        </div>
        @endforelse
    </div>

    @if($prescriptions->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
        {{ $prescriptions->links() }}
    </div>
    @endif

</div>
@endsection
