@extends('layouts.app')

@section('title', 'Notification Center')
@section('header_title', 'In-App Notification Center')
@section('header_subtitle', 'System alerts, clinical appointments, laboratory result publications, and financial notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-900 text-base">Alerts & Notifications</h3>
            <p class="text-xs text-slate-500">Real-time alerts for your active healthcare workflow</p>
        </div>

        <form action="{{ route('communication.notifications.readAll') }}" method="POST">
            @csrf
            <button type="submit" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i class="fa-solid fa-check-double"></i> Mark All as Read
            </button>
        </form>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($notifications as $n)
        <div class="p-6 transition flex items-start justify-between gap-4 {{ $n->is_read ? 'bg-white opacity-80' : 'bg-cyan-50/20' }}">
            <a href="{{ route('communication.notifications.open', $n->id) }}" class="flex items-start gap-4 flex-1 group">
                <div class="w-10 h-10 rounded-2xl bg-slate-100 group-hover:bg-cyan-600 group-hover:text-white flex items-center justify-center text-lg shrink-0 transition">
                    <i class="fa-solid {{ $n->type_icon }}"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-slate-900 text-sm group-hover:text-cyan-700 transition">{{ $n->title }}</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase">
                            {{ $n->type }}
                        </span>
                        @if(!$n->is_read)
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">{{ $n->message }}</p>
                    <span class="text-[10px] text-slate-400 mt-2 block">{{ $n->created_at->format('M d, Y - h:i A') }} ({{ $n->created_at->diffForHumans() }})</span>
                </div>
            </a>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('communication.notifications.open', $n->id) }}" class="px-3 py-1.5 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 font-bold rounded-lg text-xs transition inline-flex items-center gap-1">
                    Open <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
                @if(!$n->is_read)
                <form action="{{ route('communication.notifications.read', $n->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition">
                        Mark Read
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="p-12 text-center text-slate-400">
            <i class="fa-solid fa-bell-slash text-4xl mb-3 text-slate-300"></i>
            <p class="font-semibold text-slate-600 text-sm">You have no notifications.</p>
        </div>
        @endforelse
    </div>

    @if($notifications->hasPages())
    <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
        {{ $notifications->links() }}
    </div>
    @endif

</div>
@endsection
