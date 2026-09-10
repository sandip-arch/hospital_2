@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-800/40 p-8 lg:p-10">
    
    <!-- Header -->
    <div class="text-center mb-8">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30 mb-4">
            <i class="fa-solid fa-unlock-keyhole text-2xl"></i>
        </div>
        <h2 class="font-black text-slate-900 text-2xl tracking-tight">Forgot Password?</h2>
        <p class="text-xs text-slate-500 mt-2 font-medium">No worries! Enter your registered email address below and we will send you a secure 6-digit OTP to reset it.</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
    <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 font-semibold flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
        <span>{{ $errors->first() }}</span>
    </div>
    @endif

    <!-- Form -->
    <form action="{{ route('password.email') }}" method="POST" class="space-y-5">
        @csrf
        
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
            <div class="relative">
                <i class="fa-solid fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition" 
                       placeholder="user@hospital.test">
            </div>
        </div>

        <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-cyan-600/30 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-paper-plane"></i> Send Reset OTP
        </button>
    </form>

    <!-- Back to Login -->
    <div class="mt-8 text-center text-xs text-slate-500 font-medium pt-5 border-t border-slate-100">
        Remembered your password? <a href="{{ route('login') }}" class="text-cyan-600 font-bold hover:underline">Back to Login</a>
    </div>

</div>
@endsection