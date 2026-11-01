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
<body class="min-h-full font-sans antialiased text-slate-800 flex flex-col bg-slate-50" x-data="{ mobileMenuOpen: false }">

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
                @if(!empty($hospitalSettings['hospital_logo']))
                <img src="{{ Str::startsWith($hospitalSettings['hospital_logo'], 'http') ? $hospitalSettings['hospital_logo'] : asset($hospitalSettings['hospital_logo']) }}" 
                     alt="{{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon' }}"
                     class="w-11 h-11 rounded-2xl object-contain bg-slate-50 p-1 border border-cyan-200 shadow-md shrink-0">
                @else
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20 shrink-0">
                    <i class="fa-solid fa-hospital text-2xl"></i>
                </div>
                @endif
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

            <!-- Action Buttons & Mobile Toggle -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-3">
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

                <!-- Mobile Menu Button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition focus:outline-none">
                    <i :class="mobileMenuOpen ? 'fa-solid fa-xmark text-lg' : 'fa-solid fa-bars text-lg'"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div x-show="mobileMenuOpen" @click.outside="mobileMenuOpen = false" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-t border-slate-200 bg-white/98 backdrop-blur-md px-6 py-4 space-y-3">
            <a href="{{ route('public.home') }}" class="block text-sm font-bold text-slate-700 hover:text-cyan-600">Home</a>
            <a href="{{ route('public.departments') }}" class="block text-sm font-bold text-slate-700 hover:text-cyan-600">Specialities</a>
            <a href="{{ route('public.doctors') }}" class="block text-sm font-bold text-slate-700 hover:text-cyan-600">Find a Doctor</a>
            <a href="{{ route('ambulance.index') }}" class="block text-sm font-bold text-rose-600 flex items-center gap-2">
                <i class="fa-solid fa-truck-medical text-rose-500"></i> Ambulance Dispatch Radar
            </a>
            <a href="{{ route('public.home') }}#services" class="block text-sm font-bold text-slate-700 hover:text-cyan-600">Clinical Services</a>
            <a href="#contact" class="block text-sm font-bold text-slate-700 hover:text-cyan-600">Contact Facility</a>
            <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
                @auth
                <a href="{{ route('dashboard') }}" class="w-full text-center py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold">Go to Workspace Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="w-full text-center py-2.5 rounded-xl bg-slate-100 text-slate-800 text-xs font-bold">Sign In to Account</a>
                <a href="{{ route('register') }}" class="w-full text-center py-2.5 rounded-xl bg-cyan-600 text-white text-xs font-bold shadow-md">Patient Portal Registration</a>
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
                <p class="text-xs text-slate-400 mb-2 font-medium"><i class="fa-solid fa-map-pin text-cyan-400 mr-1.5"></i> {{ $hospitalSettings['hospital_address'] ?? '500 Health Sciences Blvd, Boston MA 02115' }}</p>
                <p class="text-xs text-slate-400 mb-2 font-medium"><i class="fa-solid fa-phone text-cyan-400 mr-1.5"></i> {{ $hospitalSettings['hospital_phone'] ?? '+1 (800) 555-APEX / +1 (555) 019-9000' }}</p>
                <p class="text-xs text-slate-400 font-medium"><i class="fa-solid fa-envelope text-cyan-400 mr-1.5"></i> {{ $hospitalSettings['hospital_email'] ?? 'contact@apexmedical.test' }}</p>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6 pt-8 mt-12 border-t border-slate-900 text-center text-xs text-slate-500 font-medium">
            &copy; 2026 Apex Horizon International Medical Center. Built with Laravel 12. All rights reserved.
        </div>
    </footer>

    <!-- Universal Live Search & Filter Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let debounceTimer = null;
            let activeController = null;

            async function runFilter(form) {
                if (activeController) activeController.abort();
                activeController = new AbortController();

                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== null) params.append(key, value);
                }

                const baseUrl = form.getAttribute('action') || window.location.pathname;
                const targetUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;

                const resultsContainer = document.querySelector('[data-live-results]') || document.querySelector('main');
                if (resultsContainer) {
                    resultsContainer.classList.add('transition-opacity', 'duration-200');
                    resultsContainer.style.opacity = '0.5';
                }

                try {
                    const res = await fetch(targetUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                        signal: activeController.signal
                    });
                    if (!res.ok) throw new Error('Fetch failed');
                    const text = await res.text();
                    const doc = new DOMParser().parseFromString(text, 'text/html');
                    const newResults = doc.querySelector('[data-live-results]') || doc.querySelector('main');
                    if (resultsContainer && newResults) {
                        resultsContainer.innerHTML = newResults.innerHTML;
                    }
                    window.history.pushState(null, '', targetUrl);
                } catch (e) {
                    if (e.name !== 'AbortError') console.error(e);
                } finally {
                    if (resultsContainer) resultsContainer.style.opacity = '1';
                }
            }

            document.addEventListener('submit', (e) => {
                const form = e.target;
                if (form.matches('form[method="GET"], form[method="get"]')) {
                    e.preventDefault();
                    runFilter(form);
                }
            });

            document.addEventListener('input', (e) => {
                const input = e.target;
                const form = input.closest('form[method="GET"], form[method="get"]');
                if (!form) return;
                if (input.type === 'text' || input.type === 'search') {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => runFilter(form), 350);
                }
            });

            document.addEventListener('change', (e) => {
                const input = e.target;
                const form = input.closest('form[method="GET"], form[method="get"]');
                if (!form) return;
                if (input.tagName === 'SELECT' || input.type === 'checkbox') {
                    clearTimeout(debounceTimer);
                    runFilter(form);
                }
            });
        });
    </script>

</body>
</html>
