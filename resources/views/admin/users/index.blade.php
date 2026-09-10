@extends('layouts.app')

@section('title', 'User & Role Management')
@section('header_title', 'User Accounts & Role Governance')
@section('header_subtitle', 'Role-Based Access Control (RBAC), credentials provisioning, and account status management')

@section('content')
<div class="space-y-6">

    <!-- Actions & Filters -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('admin.users.index') }}" method="GET" class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-6">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                           placeholder="Search by name, email, or username...">
                </div>
            </div>

            <div class="sm:col-span-3">
                <select name="role" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All User Roles</option>
                    @foreach($roles as $r)
                    <option value="{{ $r->name }}" {{ request('role') == $r->name ? 'selected' : '' }}>{{ $r->display_name ?? ucfirst($r->name) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select name="status" onchange="this.form.submit()" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Account Statuses</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </form>

        <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 shrink-0">
            <i class="fa-solid fa-user-plus"></i> Provision New User
        </a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">User / Personnel</th>
                        <th class="py-4 px-6">Username</th>
                        <th class="py-4 px-6">Assigned Role</th>
                        <th class="py-4 px-6">Department / Title</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-800 text-white font-bold text-[11px] flex items-center justify-center">
                                    {{ substr($u->name, 0, 2) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ $u->name }}</span>
                                    <span class="text-[11px] text-slate-400">{{ $u->email }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-mono font-bold text-slate-700">{{ $u->username }}</td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $u->roleBadgeColor() }}">
                                {{ $u->primaryRoleDisplay() }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            @if($u->doctor)
                                <span class="font-semibold text-slate-800">{{ $u->doctor->specialization }}</span> ({{ $u->doctor->department->name ?? 'General' }})
                            @elseif($u->staff)
                                <span class="font-semibold text-slate-800">{{ $u->staff->job_title }}</span> ({{ $u->staff->department->name ?? 'Staff' }})
                            @elseif($u->ambulanceDriver)
                                <span class="font-semibold text-amber-800">Lic: {{ $u->ambulanceDriver->license_number }}</span>
                                <span class="text-[11px] text-slate-500 font-mono block">{{ $u->ambulanceDriver->contact_number }}</span>
                            @elseif($u->patient)
                                <span class="font-mono text-cyan-700 font-bold">UPI: {{ $u->patient->patient_code }}</span>
                            @else
                                <span class="text-slate-400">System Admin</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ $u->status }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-1.5">
                            <a href="{{ route('admin.users.edit', $u->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                Edit
                            </a>
                            @if($u->id !== Auth::id())
                            <form action="{{ route('admin.users.toggleStatus', $u->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold transition {{ $u->status === 'active' ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                                    {{ $u->status === 'active' ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
