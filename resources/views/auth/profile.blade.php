@extends('layouts.app')

@section('title', 'My Profile & Security')
@section('header_title', 'Account Profile')
@section('header_subtitle', 'Manage personal credentials and security preferences')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Profile Overview Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-3xl font-extrabold shadow-lg shadow-cyan-500/20 shrink-0">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div class="flex-1 text-center sm:text-left min-w-0">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h3 class="text-xl font-bold text-slate-900">{{ $user->name }}</h3>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $user->roleBadgeColor() }}">
                    {{ $user->primaryRoleDisplay() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Username: <span class="font-mono text-slate-700 font-bold">{{ $user->username }}</span> &bull; Email: {{ $user->email }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Member since {{ $user->created_at->format('M d, Y') }} &bull; Account Status: <span class="text-emerald-600 font-bold uppercase">{{ $user->status }}</span></p>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        <h4 class="text-base font-bold text-slate-900 mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
            <i class="fa-solid fa-user-pen text-cyan-600"></i> Update Profile Information
        </h4>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-3">Change Security Password</h5>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Current Password</label>
                        <input type="password" name="current_password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="••••••••">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">New Password</label>
                        <input type="password" name="new_password"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Min. 6 chars">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Repeat">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
