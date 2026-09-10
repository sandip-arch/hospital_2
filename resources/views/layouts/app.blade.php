<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hospital Management System') - Apex Horizon Medical</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <title>@yield('title', 'Hospital Management System') - {{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon Medical Center' }}</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        },
                        medical: {
                            emerald: '#059669',
                            rose: '#e11d48',
                            amber: '#d97706',
                            indigo: '#4f46e5',
                            violet: '#7c3aed'
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(15, 23, 42, 0.05)',
                        'card': '0 10px 30px -5px rgba(15, 23, 42, 0.06)',
                        'glow': '0 0 25px -5px rgba(14, 165, 233, 0.25)',
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.6);
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .custom-dark-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.6);
        }
        .custom-dark-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 9999px;
        }
        .custom-dark-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }

        @media print {
            .no-print { display: none !important; }
            .print-only { display: block !important; }
            body { background: white !important; font-size: 12pt; color: #000 !important; }
            main { padding: 0 !important; }
            .shadow-sm, .shadow-md, .shadow-xl, .shadow-2xl { box-shadow: none !important; }
            .border { border-color: #cbd5e1 !important; }
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 flex" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden transition-opacity" @click="sidebarOpen = false" x-cloak></div>

    <!-- Sidebar Navigation -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'" 
           class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 text-slate-300 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shrink-0 border-r border-slate-800/80 no-print">
        
        <!-- Hospital Branding -->
        <div class="h-20 flex items-center gap-3.5 px-6 bg-slate-950/80 border-b border-slate-800/80">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/25 ring-1 ring-cyan-400/30">
                <i class="fa-solid fa-hospital text-xl"></i>
            </div>
            <div>
                <h1 class="font-heading font-extrabold text-white tracking-wide text-base leading-tight truncate max-w-[160px]" title="{{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon' }}">
                    {{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon' }}
                </h1>
                <p class="text-[10px] text-cyan-400 font-bold tracking-widest uppercase truncate max-w-[160px]">
                    {{ $hospitalSettings['hospital_phone'] ?? 'Medical Center' }}
                </p>
            </div>
        </div>

        <!-- User Role Badge Summary -->
        <div class="px-5 py-4 border-b border-slate-800/60 bg-slate-900/40">
            <div class="flex items-center gap-3 p-2 rounded-2xl bg-slate-800/40 border border-slate-700/50">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-700 border border-cyan-400/30 flex items-center justify-center text-white font-extrabold text-sm shadow-inner shrink-0">
                    {{ substr(Auth::user()->name ?? 'U', 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-cyan-300 font-mono font-medium truncate flex items-center gap-0.5">
                        <i class="fa-solid fa-at text-[9px] text-cyan-400"></i>{{ Auth::user()->username }}
                    </p>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-bold truncate">{{ Auth::user()->primaryRoleDisplay() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1.5 text-xs font-semibold custom-dark-scrollbar">
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-chart-pie w-5 text-center text-sm {{ request()->routeIs('dashboard') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Clinical & Operations -->
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Care & Operations</div>

            @if(!Auth::user()->isPatient())
            <a href="{{ route('patients.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('patients.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-user-injured w-5 text-center text-sm {{ request()->routeIs('patients.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Patients Directory</span>
            </a>
            @endif

            <a href="{{ route('appointments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('appointments.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-calendar-check w-5 text-center text-sm {{ request()->routeIs('appointments.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Appointments</span>
            </a>

            @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
            <a href="{{ route('doctor.schedule') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('doctor.schedule*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-calendar-days w-5 text-center text-sm {{ request()->routeIs('doctor.schedule*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Doctor Roster / Schedule</span>
            </a>
            @endif

            <a href="{{ route('medical-records.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('medical-records.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-notes-medical w-5 text-center text-sm {{ request()->routeIs('medical-records.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>EMR / Clinical Records</span>
            </a>

            <a href="{{ route('prescriptions.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('prescriptions.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-prescription w-5 text-center text-sm {{ request()->routeIs('prescriptions.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>e-Prescriptions</span>
            </a>

            <a href="{{ route('lab.requests') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('lab.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-flask-vial w-5 text-center text-sm {{ request()->routeIs('lab.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Laboratory & Diagnostics</span>
            </a>

            <a href="{{ route('ambulance.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('ambulance.*') && !request()->routeIs('ambulance.complaints.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-truck-medical w-5 text-center text-sm {{ request()->routeIs('ambulance.*') && !request()->routeIs('ambulance.complaints.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Ambulance Services</span>
            </a>

            @if(Auth::user()->isDriver() || Auth::user()->isAdmin())
            <a href="{{ route('ambulance.complaints.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('ambulance.complaints.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-triangle-exclamation w-5 text-center text-sm {{ request()->routeIs('ambulance.complaints.*') ? 'text-amber-400' : 'text-slate-400' }}"></i>
                <span>Ambulance Complaints</span>
            </a>
            @endif

            <!-- Facilities & Inventory -->
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Facilities & Pharmacy</div>

            <a href="{{ route('facilities.bed-tracker') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('facilities.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-bed-pulse w-5 text-center text-sm {{ request()->routeIs('facilities.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Bed Tracker & ADT</span>
            </a>

            <a href="{{ route('pharmacy.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('pharmacy.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-pills w-5 text-center text-sm {{ request()->routeIs('pharmacy.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Pharmacy & Inventory</span>
            </a>

            <!-- Financials & Communication -->
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Finance & Comms</div>

            <a href="{{ route('billing.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('billing.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm {{ request()->routeIs('billing.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Billing & Invoices</span>
            </a>

            <a href="{{ route('communication.messages') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('communication.messages*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-comments w-5 text-center text-sm {{ request()->routeIs('communication.messages*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Internal Messages</span>
            </a>

            <!-- Administration (Superadmin / Admin) -->
            @if(Auth::user()->isAdmin())
            <div class="pt-4 pb-1 px-3 text-[10px] font-bold uppercase tracking-widest text-cyan-400">System Administration</div>

            <a href="{{ route('admin.ambulances.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.ambulances.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-truck-medical w-5 text-center text-sm {{ request()->routeIs('admin.ambulances.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Ambulance Fleet</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-users-gear w-5 text-center text-sm {{ request()->routeIs('admin.users.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>User & Role RBAC</span>
            </a>

            <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.departments.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-sitemap w-5 text-center text-sm {{ request()->routeIs('admin.departments.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Departments Setup</span>
            </a>

            <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-shield-halved w-5 text-center text-sm {{ request()->routeIs('admin.audit-logs.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>Audit Logs & Security</span>
            </a>

            <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition duration-150 {{ request()->routeIs('admin.settings.*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/10 text-cyan-300 border border-cyan-500/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' }}">
                <i class="fa-solid fa-sliders w-5 text-center text-sm {{ request()->routeIs('admin.settings.*') ? 'text-cyan-400' : 'text-slate-400' }}"></i>
                <span>System Settings</span>
            </a>
            @endif

        </nav>

        <!-- Sidebar Footer / Portal Switch -->
        <div class="p-4 border-t border-slate-800/80 bg-slate-950/90 flex items-center justify-between">
            <a href="{{ route('public.home') }}" target="_blank" class="text-xs text-slate-400 hover:text-cyan-400 flex items-center gap-1.5 transition font-semibold">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>Public Portal</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300 font-bold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-power-off"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <!-- Top Header Bar -->
        <header class="h-20 bg-white border-b border-slate-200/80 px-6 lg:px-8 flex items-center justify-between shrink-0 shadow-soft relative z-30 no-print">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 transition">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div>
                    <h2 class="font-heading text-lg lg:text-xl font-extrabold text-slate-900 leading-tight">@yield('header_title', 'Hospital Workspace')</h2>
                    <p class="text-xs text-slate-500 font-medium">@yield('header_subtitle', 'Apex Horizon International Medical Center')</p>
                </div>
            </div>

            <!-- Topbar Actions -->
            <div class="flex items-center gap-3">

                <!-- Live Date Indicator -->
                <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100/80 border border-slate-200/60 text-xs font-semibold text-slate-600">
                    <i class="fa-regular fa-clock text-cyan-600"></i>
                    <span>{{ date('l, M d, Y') }}</span>
                </div>

                <!-- Role Badge -->
                <span class="px-3 py-1 text-xs font-bold rounded-full border {{ Auth::user()->roleBadgeColor() }}">
                    <i class="fa-solid fa-shield text-[10px] mr-1"></i> {{ Auth::user()->primaryRoleDisplay() }}
                </span>

                <!-- Notifications Dropdown -->
                <div class="relative z-50" x-data="{ open: false }">
                    <button @click="open = !open" class="relative p-2.5 rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition">
                        <i class="fa-solid fa-bell text-lg"></i>
                        @php
                            $unreadNotifs = Auth::user()->notifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadNotifs > 0)
                        <span class="absolute top-1.5 right-1.5 w-4 h-4 bg-rose-500 text-white rounded-full text-[10px] font-bold flex items-center justify-center animate-pulse">
                            {{ $unreadNotifs }}
                        </span>
                        @endif
                    </button>

                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-3xl shadow-2xl border border-slate-200/80 py-3 z-50">
                        <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between">
                            <span class="font-heading font-bold text-sm text-slate-900">Notifications ({{ $unreadNotifs }} New)</span>
                            @if($unreadNotifs > 0)
                            <form action="{{ route('communication.notifications.readAll') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-cyan-600 hover:underline">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
                            @forelse(Auth::user()->notifications()->where('is_read', false)->latest()->take(6)->get() as $n)
                            <a href="{{ route('communication.notifications.open', $n->id) }}" class="p-3.5 hover:bg-slate-50/80 transition flex gap-3 bg-cyan-50/30 block group">
                                <div class="w-8 h-8 rounded-xl bg-cyan-100 group-hover:bg-cyan-600 group-hover:text-white text-cyan-700 flex items-center justify-center text-xs shrink-0 mt-0.5 transition">
                                    <i class="fa-solid {{ $n->type_icon }}"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-bold text-slate-900 truncate group-hover:text-cyan-700 transition">{{ $n->title }}</p>
                                    <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">{{ $n->message }}</p>
                                    <span class="text-[10px] text-slate-400 mt-1 block font-medium">{{ $n->created_at->diffForHumans() }}</span>
                                </div>
                            </a>
                            @empty
                            <div class="p-8 text-center text-xs text-slate-400">
                                <i class="fa-regular fa-bell-slash text-2xl mb-2 text-slate-300"></i>
                                <p>No unread notifications</p>
                            </div>
                            @endforelse
                        </div>
                        <div class="px-5 pt-3 border-t border-slate-100 text-center">
                            <a href="{{ route('communication.notifications') }}" class="text-xs font-bold text-cyan-600 hover:underline">View All Notifications &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile Menu -->
                <div class="relative z-50" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-2xl hover:bg-slate-100 transition">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-700 text-white font-extrabold text-sm flex items-center justify-center shadow-sm">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 mr-1"></i>
                    </button>

                    <div x-show="open" @click.outside="open = false" x-cloak
                         class="absolute right-0 mt-3 w-60 bg-white rounded-3xl shadow-2xl border border-slate-200/80 py-3 z-50">
                        <div class="px-5 py-2.5 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-900">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-cyan-600 font-mono font-semibold flex items-center gap-1 mt-0.5">
                                <i class="fa-solid fa-at text-[10px]"></i>{{ Auth::user()->username }}
                            </p>
                            <p class="text-[11px] text-slate-500 truncate font-mono">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('profile') }}" class="flex items-center gap-2.5 px-5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                <i class="fa-solid fa-user-circle text-slate-400 text-sm"></i> My Profile & Security
                            </a>
                            <a href="{{ route('public.home') }}" target="_blank" class="flex items-center gap-2.5 px-5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                <i class="fa-solid fa-globe text-slate-400 text-sm"></i> Public Website
                            </a>
                        </div>
                        <div class="border-t border-slate-100 my-1"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2.5 px-5 py-2 text-xs text-rose-600 hover:bg-rose-50 transition font-bold">
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i> Log Out
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </header>

        <!-- Main Body / View Content -->
        <main class="flex-1 overflow-y-auto p-6 lg:p-8 bg-slate-50 custom-scrollbar">
            
            <!-- Global Flash Alerts -->
            @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-900 px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-soft" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-500 hover:text-emerald-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-900 px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-soft" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                    <span class="text-xs font-bold">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @if(session('info'))
            <div class="mb-6 bg-cyan-50 border border-cyan-200 text-cyan-900 px-5 py-3.5 rounded-2xl flex items-center justify-between shadow-soft" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-cyan-600 text-lg"></i>
                    <span class="text-xs font-bold">{{ session('info') }}</span>
                </div>
                <button @click="show = false" class="text-cyan-500 hover:text-cyan-700"><i class="fa-solid fa-xmark"></i></button>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-900 px-5 py-3.5 rounded-2xl shadow-soft" x-data="{ show: true }" x-show="show">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-xs text-rose-800">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Please resolve the following errors:
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <ul class="mt-2 text-xs list-disc list-inside space-y-1 text-rose-700 font-medium">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @yield('content')

        </main>
    </div>

    @stack('scripts')
</body>
</html>
