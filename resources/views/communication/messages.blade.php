@extends('layouts.app')

@section('title', 'Internal Staff Messaging')
@section('header_title', 'Secure Internal Messaging')
@section('header_subtitle', 'Direct communication between doctors, clinical support teams, and hospital administrators')

@section('content')
<div class="h-[calc(100vh-12rem)] bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col md:flex-row">

    <!-- Left Users Roster -->
    <div class="w-full md:w-80 border-b md:border-b-0 md:border-r border-slate-200 flex flex-col shrink-0 bg-slate-50/50">
        <div class="p-4 border-b border-slate-200 bg-white">
            <h3 class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                <i class="fa-solid {{ Auth::user()->isPatient() ? 'fa-user-doctor text-cyan-600' : (Auth::user()->isDriver() ? 'fa-shield-halved text-purple-600' : 'fa-users text-indigo-600') }}"></i>
                {{ Auth::user()->isPatient() ? 'My Healthcare Contacts' : (Auth::user()->isDriver() ? 'Administrative Contacts' : 'Hospital Personnel Directory') }}
            </h3>
            <p class="text-[11px] text-slate-500 mt-0.5">
                {{ Auth::user()->isPatient() ? 'Your appointed doctors & receptionists' : (Auth::user()->isDriver() ? 'Superadmin, Admin & Hospital Admin' : 'Select a contact to start conversation') }}
            </p>
        </div>

        <div class="flex-1 overflow-y-auto divide-y divide-slate-100 custom-scrollbar">
            @forelse($users as $u)
            <a href="{{ route('communication.messages', ['user_id' => $u->id]) }}" 
               class="p-4 flex items-center gap-3 transition block {{ $selectedUser && $selectedUser->id === $u->id ? 'bg-cyan-50 border-l-4 border-cyan-600' : 'hover:bg-slate-100/70' }}">
                <div class="w-10 h-10 rounded-full {{ $u->isDoctor() ? 'bg-gradient-to-tr from-blue-600 to-cyan-600' : ($u->isReceptionist() ? 'bg-gradient-to-tr from-emerald-600 to-teal-600' : ($u->isDriver() ? 'bg-gradient-to-tr from-cyan-600 to-teal-700' : 'bg-slate-800')) }} text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                    {{ substr($u->name, 0, 2) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-slate-900 truncate">{{ $u->name }}</p>
                    @if($u->isDoctor() && $u->doctor)
                        <span class="text-[10px] text-blue-600 font-semibold truncate block">
                            <i class="fa-solid fa-stethoscope text-[9px] mr-0.5"></i> {{ $u->doctor->specialization }}
                        </span>
                    @elseif($u->isReceptionist())
                        <span class="text-[10px] text-emerald-600 font-semibold truncate block">
                            <i class="fa-solid fa-bell-concierge text-[9px] mr-0.5"></i> Front Desk / Receptionist
                        </span>
                    @elseif($u->isDriver())
                        <span class="text-[10px] text-cyan-600 font-semibold truncate block">
                            <i class="fa-solid fa-truck-medical text-[9px] mr-0.5"></i> Ambulance Driver
                        </span>
                    @elseif($u->isPatient())
                        <span class="text-[10px] text-amber-600 font-semibold truncate block">
                            <i class="fa-solid fa-hospital-user text-[9px] mr-0.5"></i> Patient
                        </span>
                    @else
                        <span class="text-[10px] text-purple-600 font-semibold block uppercase">
                            <i class="fa-solid fa-shield-halved text-[9px] mr-0.5"></i> {{ $u->primaryRoleDisplay() }}
                        </span>
                    @endif
                </div>
            </a>
            @empty
            <div class="p-8 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <i class="fa-solid fa-user-slash text-xl"></i>
                </div>
                <p class="text-xs font-bold text-slate-700">No contacts available</p>
                <p class="text-[11px] text-slate-400 mt-1">
                    @if(Auth::user()->isPatient())
                        Book a consultation with a doctor to begin communicating with them here.
                    @elseif(Auth::user()->isDriver())
                        No active hospital administrators available.
                    @else
                        No eligible contacts found in directory.
                    @endif
                </p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Right Chat Conversation Panel -->
    <div class="flex-1 flex flex-col bg-white">
        @if($selectedUser)
        <!-- Conversation Header -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-cyan-600 to-blue-700 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                    {{ substr($selectedUser->name, 0, 2) }}
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">{{ $selectedUser->name }}</h4>
                    <p class="text-[11px] text-slate-400">{{ $selectedUser->primaryRoleDisplay() }} &bull; {{ $selectedUser->email }}</p>
                </div>
            </div>
        </div>

        <!-- Messages History Stream -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-slate-50/30">
            @forelse($messages as $msg)
            @php $isMe = $msg->sender_id === Auth::id(); @endphp
            <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                <div class="max-w-md p-4 rounded-2xl text-xs leading-relaxed {{ $isMe ? 'bg-cyan-600 text-white rounded-br-none shadow-md shadow-cyan-600/20' : 'bg-white border border-slate-200/80 text-slate-800 rounded-bl-none shadow-xs' }}">
                    {{ $msg->message_body }}
                </div>
                <span class="text-[10px] text-slate-400 mt-1 px-1">{{ $msg->sent_at->format('h:i A') }}</span>
            </div>
            @empty
            <div class="p-12 text-center text-slate-400">
                <i class="fa-solid fa-comments text-4xl mb-2 text-slate-300"></i>
                <p class="text-xs font-semibold text-slate-600">No prior messages with {{ $selectedUser->name }}.</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Send a message below to start communication.</p>
            </div>
            @endforelse
        </div>

        <!-- Send Message Box -->
        <div class="p-4 border-t border-slate-200 bg-white">
            <form action="{{ route('communication.messages.send') }}" method="POST" class="flex gap-2">
                @csrf
                <input type="hidden" name="receiver_id" value="{{ $selectedUser->id }}">
                <input type="text" name="message_body" required placeholder="Type secure internal message to {{ $selectedUser->name }}..."
                       class="flex-1 px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <button type="submit" class="px-5 py-3 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-2xl shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Send
                </button>
            </form>
        </div>
        @else
        <div class="flex-1 flex flex-col items-center justify-center p-12 text-slate-400">
            <div class="w-16 h-16 rounded-3xl bg-slate-100 flex items-center justify-center text-2xl text-slate-400 mb-4">
                <i class="fa-solid fa-comments"></i>
            </div>
            <h4 class="font-bold text-slate-700 text-base">Select a conversation</h4>
            <p class="text-xs text-slate-400 mt-1">
                {{ Auth::user()->isPatient() ? 'Select an appointed doctor or front-desk receptionist from the left roster to start chatting.' : 'Choose a colleague or patient from the left directory to start a conversation.' }}
            </p>
        </div>
        @endif
    </div>

</div>
@endsection
