@extends('layouts.app')

@section('title', 'Edit User - ' . $user->name)
@section('header_title', 'Edit User Account')
@section('header_subtitle', 'Update personnel credentials, assigned role, and status')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-slate-500 hover:text-purple-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Users Directory
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Role Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">System Role *</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($roles as $r)
                    <label class="p-3 rounded-2xl border cursor-pointer transition flex items-center gap-2 {{ $user->hasRole($r->name) ? 'bg-purple-50 border-purple-500 text-purple-900 font-bold' : 'border-slate-200 text-slate-700' }}">
                        <input type="radio" name="role" value="{{ $r->name }}" {{ $user->hasRole($r->name) ? 'checked' : '' }} required class="text-purple-600 focus:ring-purple-500">
                        <span class="text-xs">{{ $r->display_name ?? ucfirst($r->name) }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <!-- Basic Credentials -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Legal Name *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Username *</label>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Password (Leave blank to keep current)</label>
                    <input type="password" name="password" placeholder="••••••••"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Account Status *</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="active" {{ $user->status === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="suspended" {{ $user->status === 'suspended' ? 'selected' : '' }}>Suspended</option>
                        <option value="inactive" {{ $user->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save User Changes
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
