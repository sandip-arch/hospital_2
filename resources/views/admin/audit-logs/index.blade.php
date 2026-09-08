@extends('layouts.app')

@section('title', 'Security & Audit Logs')
@section('header_title', 'System Compliance & Audit Trail')
@section('header_subtitle', 'Immutable audit logs tracking authentication, clinical updates, financial transactions, and RBAC modifications')

@section('content')
<div class="space-y-6" x-data="{ payloadModal: false, selectedPayload: '' }">

    <!-- Filters & Action Bar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
        <form action="{{ route('admin.audit-logs.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Actor (User)</label>
                <select name="user_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Personnel</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->primaryRoleDisplay() }})</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Action Type</label>
                <select name="action" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $act)
                    <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex items-end">
                <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Apply Audit Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Timestamp</th>
                        <th class="py-4 px-6">Actor</th>
                        <th class="py-4 px-6">Action</th>
                        <th class="py-4 px-6">Affected Target</th>
                        <th class="py-4 px-6">IP & Origin</th>
                        <th class="py-4 px-6 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6 text-slate-500 font-sans">
                            <span class="font-bold text-slate-900 block">{{ $log->created_at->format('M d, Y H:i:s') }}</span>
                            <span class="text-[10px] text-slate-400 font-sans">{{ $log->created_at->diffForHumans() }}</span>
                        </td>
                        <td class="py-4 px-6 font-sans">
                            @if($log->user)
                            <span class="font-bold text-slate-900 block">{{ $log->user->name }}</span>
                            <span class="text-[10px] text-slate-400 uppercase font-semibold">{{ $log->user->primaryRoleDisplay() }}</span>
                            @else
                            <span class="text-slate-400">System Routine</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 font-sans">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-sans">
                            {{ $log->table_name ?? 'System' }} @if($log->record_id)<span class="text-slate-400 font-mono">#{{ $log->record_id }}</span>@endif
                        </td>
                        <td class="py-4 px-6 text-slate-500 text-[11px]">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                        <td class="py-4 px-6 text-right font-sans">
                            @if($log->old_values || $log->new_values)
                            <button @click="selectedPayload = JSON.stringify({ old: {{ $log->old_values ?? '{}' }}, new: {{ $log->new_values ?? '{}' }} }, null, 2); payloadModal = true"
                                    class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px]">
                                Payload
                            </button>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400 font-sans">No audit events recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    </div>

    <!-- Payload Modal -->
    <div x-show="payloadModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="payloadModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-4">
            <h4 class="text-sm font-bold text-slate-900 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-code text-purple-600"></i> Audit State Diff Payload
            </h4>

            <pre class="bg-slate-950 text-cyan-300 p-4 rounded-2xl text-xs font-mono overflow-x-auto max-h-80" x-text="selectedPayload"></pre>

            <div class="pt-2 flex justify-end">
                <button type="button" @click="payloadModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection
