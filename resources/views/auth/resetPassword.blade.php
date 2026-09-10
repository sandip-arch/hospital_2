@extends('layouts.auth')

@section('title', 'Create New Password')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-800/40 p-8 lg:p-10">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30 mb-4">
            <i class="fa-solid fa-key text-2xl"></i>
        </div>
        <h2 class="font-black text-slate-900 text-2xl tracking-tight">New Password</h2>
        <p class="text-xs text-slate-500 mt-2 font-medium">Your identity has been verified. Please create a strong, new password below.</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
        @csrf
        
        <!-- Hidden email field passed securely from the previous step -->
        <input type="hidden" name="email" value="{{ session('reset_email') ?? old('email') }}">

        <!-- New Password Field -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">New Password *</label>
            <div class="relative">
                <i class="fa-solid fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input id="new-password" type="password" name="password" required autofocus
                       class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" 
                       placeholder="••••••••"
                       oninput="toggleIconDisplay('new-password', 'toggle-span-new')">
                <span id="toggle-span-new" class="toggle-password" onclick="togglePasswordVisibility('new-password', 'toggle-icon-new')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; display: none;">
                    <i id="toggle-icon-new" class="fa-solid fa-eye text-slate-500 hover:text-cyan-600 transition"></i>
                </span>
            </div>
        </div>

        <!-- Confirm Password Field -->
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password *</label>
            <div class="relative">
                <i class="fa-solid fa-check-double absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input id="confirm-password" type="password" name="password_confirmation" required
                       class="w-full pl-10 pr-10 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" 
                       placeholder="••••••••"
                       oninput="toggleIconDisplay('confirm-password', 'toggle-span-confirm')">
                <span id="toggle-span-confirm" class="toggle-password" onclick="togglePasswordVisibility('confirm-password', 'toggle-icon-confirm')" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10; display: none;">
                    <i id="toggle-icon-confirm" class="fa-solid fa-eye text-slate-500 hover:text-cyan-600 transition"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 mt-4 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-cyan-600/30 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-floppy-disk"></i> Save New Password
        </button>
    </form>
</div>

<!-- Scripts for Toggle Logic -->
<script>
    // Show the eye icon only when the user starts typing
    function toggleIconDisplay(inputId, spanId) {
        const input = document.getElementById(inputId);
        const span = document.getElementById(spanId);
        
        if (input.value.length > 0) {
            span.style.display = 'block';
        } else {
            span.style.display = 'none';
        }
    }

    // Toggle between password dots and visible text
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection