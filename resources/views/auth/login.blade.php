@extends('layouts.auth')

@section('title', 'Hospital System Login')

@section('content')
<style>
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}
</style>
<div class="grid grid-cols-1 lg:grid-cols-12 bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-800/60 ring-1 ring-white/10">

    <!-- Left Login Form Panel -->
    <div class="lg:col-span-5 p-8 lg:p-12 flex flex-col justify-between">
        <div>
            <!-- Branding -->
            <div class="flex items-center gap-3.5 mb-8">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30">
                    <i class="fa-solid fa-hospital text-2xl"></i>
                </div>
                <div>
                    <h2 class="font-heading font-extrabold text-slate-900 text-xl leading-tight">Apex Horizon</h2>
                    <p class="text-[11px] text-cyan-600 font-bold uppercase tracking-wider">Medical Center Portal</p>
                </div>
            </div>

            <h3 class="font-heading text-2xl font-black text-slate-900 tracking-tight">Sign In</h3>
            <p class="text-xs text-slate-500 mt-1 mb-6 font-medium">Enter your credentials or click any demo role on the right</p>

            @if($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if(session('success'))
            <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs text-emerald-700 font-semibold flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                <span>{{ session('success') }}</span>
            </div>
            @endif

            <!-- Standard Credentials Form -->
            <form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email or Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
                               placeholder="user@hospital.test or username">
                    </div>
                </div>

                <div>
                    <div class="input-group" style="position: relative;">
                        <label class="block text-xs font-bold text-slate-700">Password</label>
                    </div>
                    <div class="relative">
    <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
    
    <input id="password" type="password" name="password" required
           class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition"
           placeholder="••••••••"
           oninput="toggleIconDisplay('password', 'toggle-span-login')">
           
    <span id="toggle-span-login" class="toggle-password" onclick="togglePasswordVisibility('password', 'toggle-icon')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; display: none;">
        <i id="toggle-icon" class="fa fa-eye" style="color: #6b7280;"></i>
    </span>
</div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-600 font-medium">
                        <input type="checkbox" name="remember" class="rounded text-cyan-600 focus:ring-cyan-500">
                        <span>Remember session</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-cyan-600/30 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i> Authenticate & Enter
                </button>
            </form>
        </div>

        <!-- Registration & Public Link -->
        <div class="pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
            <span>New patient? <a href="{{ route('register') }}" class="text-cyan-600 font-bold hover:underline">Register</a></span>
            <a href="{{ route('public.home') }}" class="text-slate-400 hover:text-slate-700 flex items-center gap-1"><i class="fa-solid fa-house"></i> Public Home</a>
        </div>
    </div>

    <!-- Right 1-Click Quick Role Selector (For Instant Evaluation) -->
    <div class="lg:col-span-7 bg-gradient-to-br from-slate-950 via-slate-900 to-slate-950 text-white p-8 lg:p-10 flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-slate-800">
        <div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-[10px] font-extrabold uppercase tracking-widest text-cyan-400 bg-cyan-950/80 border border-cyan-800/80 px-3 py-1 rounded-full">
                    <i class="fa-solid fa-bolt mr-1"></i> Instant 1-Click Demo Login
                </span>
                <span class="text-xs text-slate-400 font-medium">Default Password: <code class="text-cyan-300 font-bold bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-700">password</code></span>
            </div>
            
            <h4 class="font-heading text-xl font-bold tracking-tight mb-1">Select Role to Test Instantly</h4>
            <p class="text-xs text-slate-400 mb-5 font-normal">Click any tile below to log in directly with that role's full permissions and dashboard:</p>

            <!-- Roles Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                
                <!-- 1. Superadmin -->
                <a href="{{ route('login.demo', 'superadmin') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-purple-950/50 border border-slate-800 hover:border-purple-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-purple-500/20 text-purple-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-purple-300">1. Superadmin</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">superadmin@hospital.test</p>
                    </div>
                </a>

                <!-- 2. Admin -->
                <a href="{{ route('login.demo', 'admin') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-indigo-950/50 border border-slate-800 hover:border-indigo-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-indigo-300">2. Admin (Operations)</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">admin@hospital.test</p>
                    </div>
                </a>

                <!-- 3. Doctor -->
                <a href="{{ route('login.demo', 'doctor') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-blue-950/50 border border-slate-800 hover:border-blue-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-500/20 text-blue-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-blue-300">3. Doctor (Cardiology)</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">dr.sarah@hospital.test</p>
                    </div>
                </a>

                <!-- 4. Patient -->
                <a href="{{ route('login.demo', 'patient') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-amber-950/50 border border-slate-800 hover:border-amber-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-amber-300">4. Patient Portal (Johnathan)</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">patient.john@hospital.test</p>
                    </div>
                </a>

                <!-- 5a. Staff: Receptionist -->
                <a href="{{ route('login.demo', 'receptionist') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-emerald-950/50 border border-slate-800 hover:border-emerald-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-bell-concierge"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-emerald-300">5a. Staff: Receptionist</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">receptionist@hospital.test</p>
                    </div>
                </a>

                <!-- 5b. Staff: Nurse -->
                <a href="{{ route('login.demo', 'nurse') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-emerald-950/50 border border-slate-800 hover:border-emerald-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-user-nurse"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-emerald-300">5b. Staff: Head Nurse</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">nurse@hospital.test</p>
                    </div>
                </a>

                <!-- 5c. Staff: Pharmacist -->
                <a href="{{ route('login.demo', 'pharmacist') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-emerald-950/50 border border-slate-800 hover:border-emerald-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-pills"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-emerald-300">5c. Staff: Pharmacist</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">pharmacist@hospital.test</p>
                    </div>
                </a>

                <!-- 5d. Staff: Lab Tech -->
                <a href="{{ route('login.demo', 'labtech') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-emerald-950/50 border border-slate-800 hover:border-emerald-500/60 transition flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-emerald-300">5d. Staff: Lab Technician</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">labtech@hospital.test</p>
                    </div>
                </a>

                <!-- 5e. Staff: Cashier -->
                <a href="{{ route('login.demo', 'cashier') }}" class="group p-3 rounded-2xl bg-slate-900/90 hover:bg-emerald-950/50 border border-slate-800 hover:border-emerald-500/60 transition flex items-center gap-3 sm:col-span-2">
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 group-hover:scale-110 flex items-center justify-center font-bold text-sm transition shrink-0">
                        <i class="fa-solid fa-cash-register"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-white group-hover:text-emerald-300">5e. Staff: Cashier & Billing Clerk</p>
                        <p class="text-[10px] text-slate-400 truncate font-mono">cashier@hospital.test</p>
                    </div>
                </a>

            </div>
        </div>

        <div class="pt-5 border-t border-slate-800/80 flex items-center justify-between text-[11px] text-slate-400">
            <span class="flex items-center gap-1.5"><i class="fa-solid fa-database text-cyan-400"></i> SQLite & MySQL (hospital.sql)</span>
            <span class="text-cyan-400 font-bold">26 Normalized Tables</span>
        </div>
    </div>

</div>
<script>
    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            // Show password
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash'); // Changes icon to a crossed-out eye
        } else {
            // Hide password
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }
    function toggleIconDisplay(inputId, spanId) {
    const input = document.getElementById(inputId);
    const span = document.getElementById(spanId);
    
    if (input.value.length > 0) {
        span.style.display = 'block'; 
    } else {
        span.style.display = 'none';  
    }
    }
</script>
@endsection
