<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $hospitalSettings['hospital_name'] ?? 'Apex Horizon Medical Center') - Excellence in Healthcare</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">

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
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-full font-sans antialiased text-slate-800 flex flex-col bg-slate-50">

    <!-- Top Emergency & Info Banner -->
    <div class="bg-slate-950 text-slate-300 text-xs py-2.5 px-6 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-6">
                <span class="flex items-center gap-1.5 text-rose-400 font-bold">
                    <i class="fa-solid fa-phone-volume animate-pulse"></i> 24/7 Emergency: {{ $hospitalSettings['emergency_contact_number'] ?? '911 / +1 (555) 911-0000' }}
                </span>
                <a href="{{ route('ambulance.index') }}" class="inline-flex items-center gap-1.5 text-rose-400 hover:text-rose-300 font-bold transition">
                    <i class="fa-solid fa-truck-medical"></i> Ambulance Radar & Dispatch
                </a>
                <span class="hidden lg:inline-flex items-center gap-1.5 text-slate-400 font-medium">
                    <i class="fa-solid fa-location-dot text-cyan-400"></i> {{ $hospitalSettings['hospital_address'] ?? '500 Health Sciences Blvd, Boston MA' }}
                </span>
            </div>
            <div class="flex items-center gap-4 text-slate-400 font-medium text-xs">
                <span class="hidden sm:inline">Visiting: 08:00 AM - 08:00 PM</span>
                @auth
                <a href="{{ route('dashboard') }}" class="text-cyan-400 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-gauge"></i> Portal Workspace
                </a>
                @else
                <a href="{{ route('login') }}" class="text-cyan-400 font-bold hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-user-lock"></i> Staff & Patient Login
                </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Navigation Header -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <a href="{{ route('public.home') }}" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                    <i class="fa-solid fa-hospital text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-heading font-extrabold text-slate-900 tracking-tight text-xl leading-none">{{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon' }}</h1>
                    <p class="text-xs text-cyan-600 font-bold tracking-wider uppercase mt-1">{{ $hospitalSettings['hospital_phone'] ?? 'Medical Center' }}</p>
                </div>
            </a>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-600">
                <a href="{{ route('public.home') }}" class="hover:text-cyan-600 transition {{ request()->routeIs('public.home') ? 'text-cyan-600 font-extrabold' : '' }}">Home</a>
                <a href="{{ route('public.departments') }}" class="hover:text-cyan-600 transition {{ request()->routeIs('public.departments') ? 'text-cyan-600 font-extrabold' : '' }}">Specialities</a>
                <a href="{{ route('public.doctors') }}" class="hover:text-cyan-600 transition {{ request()->routeIs('public.doctors') ? 'text-cyan-600 font-extrabold' : '' }}">Find a Doctor</a>
                <a href="{{ route('ambulance.index') }}" class="hover:text-rose-600 transition flex items-center gap-1.5 {{ request()->routeIs('ambulance.*') ? 'text-rose-600 font-extrabold' : '' }}">
                    <i class="fa-solid fa-truck-medical text-rose-500"></i>
                    <span>Ambulance</span>
                </a>
                <a href="{{ route('public.home') }}#services" class="hover:text-cyan-600 transition">Services</a>
                <a href="#contact" class="hover:text-cyan-600 transition">Contact</a>
            </nav>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-slate-950 text-white text-xs font-bold hover:bg-slate-800 shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-cyan-400"></i> My Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="px-4 py-2 text-xs font-bold text-slate-700 hover:text-cyan-600 transition">Sign In</a>
                <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold shadow-lg shadow-cyan-600/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i> Patient Portal
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 text-sm py-16 border-t border-slate-800" id="contact">
        <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-4 gap-10">
            <div>
                <div class="flex items-center gap-3 text-white mb-4">
                    <div class="w-9 h-9 rounded-xl bg-cyan-500 flex items-center justify-center text-white font-bold">
                        <i class="fa-solid fa-hospital"></i>
                    </div>
                    <span class="font-heading font-bold text-lg">Apex Horizon</span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed font-medium">
                    Accredited multi-specialty tertiary healthcare facility delivering world-class clinical care, surgical precision, and patient wellness.
                </p>
            </div>
            <div>
                <h4 class="font-heading text-white font-bold text-sm mb-4">Clinical Centers</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="{{ route('public.doctors') }}" class="hover:text-white transition">Cardiology & Heart Care</a></li>
                    <li><a href="{{ route('public.doctors') }}" class="hover:text-white transition">Neurology & Brain Sciences</a></li>
                    <li><a href="{{ route('public.doctors') }}" class="hover:text-white transition">Orthopedics & Joint Care</a></li>
                    <li><a href="{{ route('public.doctors') }}" class="hover:text-white transition">Pediatrics & Neonatal ICU</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-heading text-white font-bold text-sm mb-4">Quick Portals</h4>
                <ul class="space-y-2 text-xs font-medium">
                    <li><a href="{{ route('login') }}" class="hover:text-white transition">Doctor & Staff Login</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white transition">Patient Registration</a></li>
                    <li><a href="{{ route('login.demo', 'superadmin') }}" class="hover:text-white transition">Demo 1-Click Access</a></li>
                    <li><a href="{{ route('public.doctors') }}" class="hover:text-white transition">Book Consultation</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-heading text-white font-bold text-sm mb-4">Contact Info</h4>
                <p class="text-xs text-slate-400 mb-2 font-medium"><i class="fa-solid fa-map-pin text-cyan-400 mr-1.5"></i> 500 Health Sciences Blvd, Boston MA 02115</p>
                <p class="text-xs text-slate-400 mb-2 font-medium"><i class="fa-solid fa-phone text-cyan-400 mr-1.5"></i> +1 (800) 555-APEX / +1 (555) 019-9000</p>
                <p class="text-xs text-slate-400 font-medium"><i class="fa-solid fa-envelope text-cyan-400 mr-1.5"></i> contact@apexmedical.test</p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 pt-8 mt-12 border-t border-slate-900 text-center text-xs text-slate-500 font-medium">
            &copy; 2026 Apex Horizon International Medical Center. Built with Laravel 12. All rights reserved.
        </div>
    </footer>

</body>
</html>
