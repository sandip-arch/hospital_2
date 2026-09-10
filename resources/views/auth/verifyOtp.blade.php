@extends('layouts.auth')

@section('title', 'Verify OTP')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-800/40 p-8 lg:p-10">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30 mb-4">
            <i class="fa-solid fa-shield-halved text-2xl"></i>
        </div>
        <h2 class="font-black text-slate-900 text-2xl tracking-tight">Verify Your Email</h2>
        <p class="text-xs text-slate-500 mt-2 font-medium">We've sent a 6-digit secure code to your email. Please enter it below to verify your identity.</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('password.otp.verify') }}" method="POST" class="space-y-5">
        @csrf
        
        <!-- Hidden email field so the controller knows whose OTP this is -->
        <input type="hidden" name="email" value="{{ session('reset_email') ?? old('email') }}">

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1 text-center">Enter 6-Digit OTP *</label>
            <div class="relative max-w-[200px] mx-auto">
                <input type="text" name="otp" required autofocus maxlength="6" pattern="\d{6}"
                       class="w-full py-3 bg-slate-50 border border-slate-200 rounded-xl text-center tracking-[0.5em] text-lg font-black text-slate-900 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" 
                       placeholder="••••••">
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 mt-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-cyan-600/30 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-check-circle"></i> Verify Code
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500 font-medium">
        <a href="{{ route('password.request') }}" class="text-slate-400 hover:text-cyan-600 transition">Did not receive it? Go back</a>
    </div>

</div>
@endsection