@extends('layouts.app')

@section('title', 'Diagnostic Tests Catalog')
@section('header_title', 'Laboratory & Diagnostics Catalog')
@section('header_subtitle', 'Master list of pathology, blood panels, and radiology imaging tests')

@section('content')
<div class="space-y-6" x-data="{ addModal: false }">

    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-base">Diagnostic Test Menu</h3>
            <p class="text-xs text-slate-500">Standard test codes and hospital fee schedules</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('lab.requests') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-list-check mr-1"></i> Lab Requests Queue
            </a>
            @if(Auth::user()->isAdmin())
            <button @click="addModal = true" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-plus"></i> Add Test to Catalog
            </button>
            @endif
        </div>
    </div>

    <!-- Catalog Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($tests as $test)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200 px-2.5 py-0.5 rounded-full">
                        {{ $test->code }}
                    </span>
                    <span class="text-xs font-bold text-slate-400">{{ $test->reports_count }} Orders</span>
                </div>
                <h4 class="font-bold text-slate-900 text-base">{{ $test->test_name }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed">{{ $test->description ?? 'Standard diagnostic laboratory procedure.' }}</p>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block">Procedure Fee</span>
                    <span class="text-lg font-black text-slate-900">${{ number_format($test->cost, 2) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Test Modal -->
    <div x-show="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="addModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-flask-vial text-purple-600"></i> Add Diagnostic Test
            </h4>

            <form action="{{ route('lab.tests.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Test Name *</label>
                    <input type="text" name="test_name" required placeholder="e.g. Thyroid Stimulating Hormone (TSH)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Code *</label>
                        <input type="text" name="code" required placeholder="e.g. LAB-TSH"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Cost ($) *</label>
                        <input type="number" step="0.01" name="cost" required placeholder="45.00"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Clinical Description</label>
                    <textarea name="description" rows="2" placeholder="Procedure clinical utility..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="addModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md">Add Test</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
