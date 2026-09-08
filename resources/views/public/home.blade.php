@extends('layouts.public')

@section('title', 'Apex Horizon International Medical Center')

@section('content')

<!-- Hero Section -->
<section class="relative bg-gradient-to-br from-slate-900 via-slate-950 to-cyan-950 text-white overflow-hidden py-20 lg:py-28">
    <div class="absolute inset-0 bg-[radial-gradient(#0ea5e9_1px,transparent_1px)] [background-size:24px_24px] opacity-10"></div>
    <div class="max-w-7xl mx-auto px-6 relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
        
        <!-- Hero Text -->
        <div class="lg:col-span-7 space-y-6">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span> Joint Commission International (JCI) Accredited
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1]">
                Advanced Medicine. <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 via-blue-400 to-indigo-300">Compassionate Care.</span>
            </h1>
            <p class="text-sm sm:text-base text-slate-300 max-w-xl leading-relaxed">
                Welcome to Apex Horizon Medical Center. Equipped with 24/7 Level 1 Trauma Care, cutting-edge robotic surgical suites, continuous ICU telemetry, and world-class medical specialists.
            </p>

            <div class="flex flex-wrap items-center gap-4 pt-2">
                <a href="#quick-book" class="px-6 py-3.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-sm rounded-2xl shadow-lg shadow-cyan-500/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-calendar-check"></i> Book Consultation
                </a>
                <a href="{{ route('public.doctors') }}" class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm rounded-2xl border border-slate-700 shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-user-doctor text-cyan-400"></i> Find Specialists
                </a>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-3 gap-6 pt-8 border-t border-slate-800/80">
                <div>
                    <p class="text-2xl lg:text-3xl font-black text-white">{{ $stats['doctors_count'] }}+</p>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mt-1">Specialists</p>
                </div>
                <div>
                    <p class="text-2xl lg:text-3xl font-black text-cyan-400">{{ $stats['departments_count'] }}</p>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mt-1">Departments</p>
                </div>
                <div>
                    <p class="text-2xl lg:text-3xl font-black text-emerald-400">24 / 7</p>
                    <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold mt-1">Trauma & ICU</p>
                </div>
            </div>
        </div>

        <!-- Hero Quick Booking Form -->
        <div class="lg:col-span-5" id="quick-book">
            <div class="bg-white rounded-3xl p-8 shadow-2xl border border-slate-100 text-slate-800">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center font-bold text-lg">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 text-base">Quick Consultation Request</h3>
                        <p class="text-xs text-slate-500">Book direct with top clinical consultants</p>
                    </div>
                </div>

                <form action="{{ route('public.bookRequest') }}" method="POST" class="space-y-3.5">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Your Full Name *</label>
                        <input type="text" name="patient_name" required
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. John Doe">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="+1 (555) 000-0000">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email"
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="optional">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Department *</label>
                            <select name="department_id" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                @foreach($departments as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Physician *</label>
                            <select name="doctor_id" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                @foreach($doctors as $doc)
                                <option value="{{ $doc->id }}">{{ $doc->full_name }} (${{ number_format($doc->consultation_fee, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Preferred Date *</label>
                            <input type="date" name="appointment_date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required
                                   class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Time Slot *</label>
                            <select name="time_slot" required class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                                <option value="09:00 AM">09:00 AM</option>
                                <option value="10:30 AM">10:30 AM</option>
                                <option value="01:30 PM">01:30 PM</option>
                                <option value="03:00 PM">03:00 PM</option>
                                <option value="04:30 PM">04:30 PM</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Reason for Visit</label>
                        <input type="text" name="reason"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Chest pain evaluation">
                    </div>

                    <button type="submit" class="w-full py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-cyan-600/30 text-xs transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Confirm Appointment Request
                    </button>
                </form>
            </div>
        </div>

    </div>
</section>

<!-- Clinical Specialities Section -->
<section class="py-20 bg-white" id="services">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="text-xs font-extrabold uppercase tracking-wider text-cyan-600 bg-cyan-50 px-3 py-1 rounded-full border border-cyan-100">Centers of Excellence</span>
            <h2 class="text-3xl lg:text-4xl font-black text-slate-900 tracking-tight mt-3">Specialized Medical Departments</h2>
            <p class="text-xs sm:text-sm text-slate-500 mt-2">Comprehensive inpatient, outpatient, diagnostic, and surgical services under one integrated health network.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($departments as $dept)
            <div class="group bg-slate-50 hover:bg-white p-6 rounded-3xl border border-slate-200/80 hover:border-cyan-500/50 hover:shadow-xl transition duration-300">
                <div class="w-12 h-12 rounded-2xl bg-cyan-100 text-cyan-700 group-hover:bg-cyan-600 group-hover:text-white flex items-center justify-center text-xl mb-5 transition duration-300 shadow-sm">
                    <i class="fa-solid fa-{{ $dept->icon ?? 'stethoscope' }}"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900 group-hover:text-cyan-600 transition">{{ $dept->name }}</h3>
                <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">{{ $dept->description }}</p>
                <div class="mt-4 pt-4 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-400">
                    <span>{{ $dept->doctors_count }} Specialists</span>
                    <a href="{{ route('public.doctors', ['department_id' => $dept->id]) }}" class="text-cyan-600 font-semibold group-hover:translate-x-1 transition flex items-center gap-1">
                        View <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Featured Specialists -->
<section class="py-20 bg-slate-50 border-t border-slate-200/80">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-12">
            <div>
                <span class="text-xs font-extrabold uppercase tracking-wider text-cyan-600 bg-cyan-100/50 px-3 py-1 rounded-full">Medical Staff</span>
                <h2 class="text-3xl font-black text-slate-900 tracking-tight mt-2">Consultant Physicians & Surgeons</h2>
            </div>
            <a href="{{ route('public.doctors') }}" class="text-xs font-bold text-cyan-600 hover:text-cyan-700 flex items-center gap-1.5">
                Browse all doctors <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($doctors as $doc)
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm hover:shadow-md transition">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-700 text-white flex items-center justify-center text-2xl font-black shadow-md shadow-cyan-500/20 mb-4">
                    {{ substr($doc->user->name, 0, 1) }}
                </div>
                <h4 class="text-base font-bold text-slate-900">{{ $doc->full_name }}</h4>
                <p class="text-xs text-cyan-600 font-semibold mt-0.5">{{ $doc->department->name ?? 'Specialist' }}</p>
                <p class="text-xs text-slate-500 mt-2 line-clamp-2">{{ $doc->specialization }}</p>
                
                <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 block uppercase font-semibold">Consultation</span>
                        <span class="text-sm font-extrabold text-slate-900">${{ number_format($doc->consultation_fee, 2) }}</span>
                    </div>
                    <a href="#quick-book" class="px-3.5 py-1.5 bg-slate-900 hover:bg-cyan-600 text-white text-xs font-semibold rounded-xl transition">
                        Book
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Patient Portal Banner -->
<section class="py-16 bg-gradient-to-r from-cyan-600 to-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="space-y-2">
            <h3 class="text-2xl lg:text-3xl font-black tracking-tight">Access Your Health Dossier Online</h3>
            <p class="text-xs sm:text-sm text-cyan-100 max-w-xl">
                View your medical history, vital sign observations, electronic prescriptions, published lab results, and pay hospital invoices directly.
            </p>
        </div>
        <div class="flex items-center gap-4 shrink-0">
            <a href="{{ route('register') }}" class="px-6 py-3.5 bg-white text-slate-900 hover:bg-slate-100 font-extrabold text-xs rounded-2xl shadow-xl transition">
                Register as Patient
            </a>
            <a href="{{ route('login') }}" class="px-6 py-3.5 bg-slate-950 text-white hover:bg-slate-900 font-bold text-xs rounded-2xl shadow-xl transition">
                Sign In to Portal
            </a>
        </div>
    </div>
</section>

@endsection
