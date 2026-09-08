@extends('layouts.app')

@section('title', 'Electronic Medical Records (EMR)')
@section('header_title', 'Clinical Records & EMR')
@section('header_subtitle', 'Patient diagnoses, clinical observations, vital signs timelines, and consultation notes')

@section('content')
<div class="space-y-6">

    <!-- Header Actions & Search -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('medical-records.index') }}" method="GET" class="flex-1 max-w-lg">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                       placeholder="Search by patient name, UPI, diagnosis, or symptoms...">
            </div>
        </form>

        @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
        <a href="{{ route('medical-records.create') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-file-circle-plus"></i> New Consultation Note
        </a>
        @endif
    </div>

    <!-- Records Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($records as $rec)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-cyan-500/50 transition flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="font-mono text-[11px] font-bold text-cyan-700 bg-cyan-50 border border-cyan-200 px-2.5 py-0.5 rounded-full">
                        {{ $rec->patient->patient_code }}
                    </span>
                    <span class="text-xs text-slate-400 font-semibold">{{ $rec->visit_date->format('M d, Y') }}</span>
                </div>

                <h4 class="font-bold text-slate-900 text-base">
                    <a href="{{ route('medical-records.show', $rec->id) }}" class="hover:text-cyan-600 transition">
                        {{ $rec->diagnosis }}
                    </a>
                </h4>

                <p class="text-xs text-slate-600 font-medium">Patient: <strong class="text-slate-900">{{ $rec->patient->full_name }}</strong></p>
                <p class="text-[11px] text-slate-500">Doctor: {{ $rec->doctor->full_name }} ({{ $rec->doctor->specialization }})</p>

                @if($rec->symptoms)
                <p class="text-xs text-slate-500 line-clamp-2 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                    <strong class="text-slate-700">Symptoms:</strong> {{ $rec->symptoms }}
                </p>
                @endif

                <!-- Vital Signs preview -->
                @if($rec->details->isNotEmpty())
                <div class="flex flex-wrap gap-1.5 pt-1">
                    @foreach($rec->details->take(3) as $v)
                    <span class="px-2 py-0.5 bg-slate-100 rounded-md text-[10px] text-slate-700 font-semibold">
                        {{ $v->vital_sign_name }}: <strong>{{ $v->vital_sign_value }}</strong>
                    </span>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('medical-records.show', $rec->id) }}" class="text-xs font-bold text-cyan-600 hover:underline flex items-center gap-1">
                    Full Dossier &rarr;
                </a>
                <a href="{{ route('medical-records.print', $rec->id) }}" target="_blank" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="Print Encounter">
                    <i class="fa-solid fa-print"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="col-span-3 bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            <i class="fa-solid fa-notes-medical text-4xl mb-3 text-slate-300"></i>
            <p class="font-semibold text-slate-600 text-sm">No electronic medical records found.</p>
        </div>
        @endforelse
    </div>

    @if($records->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
        {{ $records->links() }}
    </div>
    @endif

</div>
@endsection
