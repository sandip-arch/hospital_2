@extends('layouts.app')

@section('title', 'Diagnostic Report - ' . $report->test->test_name)
@section('header_title', 'Diagnostic Pathology Report')
@section('header_subtitle', 'Patient: ' . $report->patient->full_name . ' • Test: ' . $report->test->test_name)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between no-print">
        <a href="{{ route('lab.requests') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Lab Queue
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
            <i class="fa-solid fa-print"></i> Print Official Report
        </button>
    </div>

    <!-- Official Report Card -->
    <div class="bg-white rounded-3xl p-8 lg:p-12 border border-slate-200/80 shadow-sm space-y-6">
        
        <!-- Header -->
        <div class="flex items-center justify-between pb-6 border-b-2 border-slate-900">
            <div>
                <h1 class="text-2xl font-black text-slate-900">Apex Horizon Diagnostic Laboratories</h1>
                <p class="text-xs text-slate-500">500 Health Sciences Blvd, Boston MA 02115 &bull; Accredited by CAP / CLIA</p>
                <p class="text-xs text-purple-700 font-bold uppercase tracking-wider mt-1">Official Diagnostic Laboratory Report</p>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $report->status_badge }}">
                    {{ ucfirst(str_replace('_', ' ', $report->status)) }}
                </span>
                <span class="text-xs font-mono block text-slate-400 mt-1">Report #LAB-{{ $report->id }}</span>
            </div>
        </div>

        <!-- Demographics -->
        <div class="grid grid-cols-2 gap-4 py-4 bg-slate-50 rounded-2xl p-4 border border-slate-100 text-xs">
            <div>
                <p><strong class="text-slate-900">Patient:</strong> {{ $report->patient->full_name }}</p>
                <p><strong class="text-slate-900">UPI:</strong> {{ $report->patient->patient_code }}</p>
                <p><strong class="text-slate-900">Age / Gender:</strong> {{ $report->patient->age }} yrs / {{ $report->patient->gender }}</p>
            </div>
            <div>
                <p><strong class="text-slate-900">Ordering Physician:</strong> {{ $report->doctor->full_name }}</p>
                <p><strong class="text-slate-900">Department:</strong> {{ $report->doctor->department->name ?? 'General' }}</p>
                <p><strong class="text-slate-900">Report Date:</strong> {{ $report->report_date ? $report->report_date->format('M d, Y - h:i A') : 'Pending Completion' }}</p>
            </div>
        </div>

        <!-- Procedure Details -->
        <div class="space-y-4">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400">Diagnostic Procedure</span>
                <h3 class="text-lg font-black text-slate-900">{{ $report->test->test_name }} ({{ $report->test->code }})</h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $report->test->description }}</p>
            </div>

            <!-- Findings -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">Findings & Parameters Summary</h4>
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200/80 font-mono text-xs text-slate-800 leading-relaxed whitespace-pre-line">
                    {{ $report->result_summary ?? 'Laboratory sample is currently being processed. Full parameters will be updated upon completion.' }}
                </div>
            </div>
        </div>

        <!-- Sign off -->
        <div class="pt-12 flex items-end justify-between text-xs border-t border-slate-100">
            <div>
                <p class="text-slate-400">Verified by: <strong class="text-slate-700">{{ $report->technician->name ?? 'Chief Medical Technologist' }}</strong></p>
                <p class="text-[10px] text-slate-400">Electronic Verification &bull; Apex Horizon LIS Engine</p>
            </div>
            <div class="text-center w-56 border-t border-slate-400 pt-2">
                <p class="font-bold text-slate-900">Diagnostic Pathologist</p>
                <p class="text-slate-500 text-[11px]">Authorized Medical Signature</p>
            </div>
        </div>

    </div>

</div>
@endsection
