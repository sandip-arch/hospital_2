@extends('layouts.public')

@section('title', 'Specialized Medical Departments')

@section('content')
<div class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">

        <div class="max-w-2xl mb-10">
            <span class="text-xs font-extrabold uppercase tracking-wider text-cyan-600 bg-cyan-100/50 px-3 py-1 rounded-full">Hospital Centers</span>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-2">Clinical Specialities & Departments</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Explore our clinical infrastructure, multidisciplinary medical teams, and diagnostic facilities.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($departments as $dept)
            <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl shadow-sm">
                                <i class="fa-solid fa-{{ $dept->icon ?? 'hospital' }}"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900">{{ $dept->name }}</h3>
                                <span class="text-xs font-mono font-bold text-slate-400">Code: {{ $dept->code }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full text-xs font-semibold">
                            {{ $dept->doctors->count() }} Doctors
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed mb-6">{{ $dept->description }}</p>

                    <!-- Doctors attached -->
                    <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Key Attending Specialists</p>
                        <div class="flex flex-wrap gap-2">
                            @forelse($dept->doctors as $doc)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-semibold text-slate-800 shadow-2xs">
                                <i class="fa-solid fa-user-doctor text-cyan-600 text-[10px]"></i> {{ $doc->full_name }}
                            </span>
                            @empty
                            <span class="text-xs text-slate-400">Specialist rotation scheduled</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500"><i class="fa-solid fa-bed text-cyan-600 mr-1"></i> {{ $dept->rooms->count() }} Dedicated Rooms</span>
                    <a href="{{ route('public.doctors', ['department_id' => $dept->id]) }}" class="px-4 py-2 bg-slate-900 hover:bg-cyan-600 text-white text-xs font-bold rounded-xl transition">
                        View Doctors in {{ $dept->name }}
                    </a>
                </div>
            </div>
            @endforeach
        </div>

    </div>
</div>
@endsection
