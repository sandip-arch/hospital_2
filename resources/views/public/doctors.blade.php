@extends('layouts.public')

@section('title', 'Find a Specialist Doctor')

@section('content')
<div class="py-12 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">

        <!-- Page Header -->
        <div class="max-w-2xl mb-8">
            <span class="text-xs font-extrabold uppercase tracking-wider text-cyan-600 bg-cyan-100/50 px-3 py-1 rounded-full">Doctor Directory</span>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight mt-2">Specialist Physicians & Surgeons</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Search our medical staff across specialized departments and book direct consultations.</p>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm mb-8">
            <form action="{{ route('public.doctors') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-6">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500"
                               placeholder="Search doctor by name or specialization...">
                    </div>
                </div>

                <div class="sm:col-span-4">
                    <select name="department_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">All Departments</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2 flex gap-2">
                    <button type="submit" class="w-full py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs rounded-xl transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'department_id']))
                    <a href="{{ route('public.doctors') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Doctors Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($doctors as $doc)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col justify-between hover:shadow-lg transition">
                <div>
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-700 text-white flex items-center justify-center text-2xl font-black shadow-md shadow-cyan-500/20 mb-4">
                        {{ substr($doc->user->name, 0, 1) }}
                    </div>
                    <h3 class="text-base font-bold text-slate-900">{{ $doc->full_name }}</h3>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                        {{ $doc->department->name ?? 'General' }}
                    </span>
                    <p class="text-xs text-slate-600 font-medium mt-2">{{ $doc->specialization }}</p>
                    <p class="text-xs text-slate-400 mt-2 line-clamp-3 leading-relaxed">{{ $doc->bio ?? 'Dedicated healthcare provider delivering personalized medical attention.' }}</p>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-semibold">Consultation</span>
                        <span class="text-base font-black text-slate-900">${{ number_format($doc->consultation_fee, 2) }}</span>
                    </div>
                    <a href="{{ route('public.home') }}#quick-book" class="px-4 py-2 bg-slate-900 hover:bg-cyan-600 text-white text-xs font-bold rounded-xl transition flex items-center gap-1">
                        <i class="fa-solid fa-calendar-check text-[10px]"></i> Book
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-4 bg-white rounded-3xl p-12 text-center border border-slate-200">
                <i class="fa-solid fa-user-doctor text-4xl text-slate-300 mb-3"></i>
                <h4 class="text-base font-bold text-slate-700">No doctors match your filter</h4>
                <p class="text-xs text-slate-400 mt-1">Try resetting the department or search term</p>
            </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $doctors->links() }}
        </div>

    </div>
</div>
@endsection
