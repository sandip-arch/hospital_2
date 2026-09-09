@extends(Auth::check() ? 'layouts.app' : 'layouts.public')

@section('title', 'My Ambulance Bookings')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-heading font-extrabold text-slate-900">
                Ambulance Dispatch History
            </h1>
            <p class="text-xs text-slate-500 mt-1">Review your current and previous ambulance requests and track live dispatches.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('ambulance.book') }}" class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-rose-600 to-red-500 hover:from-rose-500 hover:to-red-400 text-white font-bold text-xs shadow-md shadow-rose-600/20 flex items-center gap-2 transition">
                <i class="fa-solid fa-plus"></i>
                <span>New Ambulance Request</span>
            </a>
            <a href="{{ route('ambulance.index') }}" class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                <i class="fa-solid fa-map-location-dot text-cyan-600 mr-1.5"></i>
                <span>Radar Map</span>
            </a>
        </div>
    </div>

    <!-- Bookings Table Card -->
    <div class="bg-white rounded-3xl shadow-soft border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-4 px-6">Booking ID</th>
                        <th class="py-4 px-6">Ambulance & Driver</th>
                        <th class="py-4 px-6">Pickup Address</th>
                        <th class="py-4 px-6">Destination Dept</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6">Date & Time</th>
                        <th class="py-4 px-6 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($bookings as $booking)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-4 px-6 font-mono font-bold text-slate-900">
                            #AMB-{{ str_pad($booking->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-800">{{ $booking->ambulance->vehicle_number ?? 'Ambulance' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $booking->ambulance->model ?? '' }}</div>
                            <div class="text-[11px] text-cyan-600">Driver: {{ $booking->ambulance->currentDriver?->user?->name ?? 'Assigned' }}</div>
                        </td>
                        <td class="py-4 px-6 max-w-xs">
                            <div class="truncate text-slate-800 font-semibold" title="{{ $booking->pickup_address }}">
                                {{ $booking->pickup_address }}
                            </div>
                            <div class="text-[11px] text-slate-400 font-mono">
                                {{ number_format($booking->pickup_latitude, 4) }}, {{ number_format($booking->pickup_longitude, 4) }}
                            </div>
                        </td>
                        <td class="py-4 px-6">
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold text-[11px]">
                                {{ $booking->destinationDepartment?->name ?? 'Emergency Medicine' }}
                            </span>
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $booking->statusBadge() }}">
                                {{ $booking->statusDisplay() }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                            <div>{{ $booking->booking_time ? $booking->booking_time->format('M d, Y') : 'N/A' }}</div>
                            <div class="text-[11px] text-slate-400">{{ $booking->booking_time ? $booking->booking_time->format('h:i A') : '' }}</div>
                        </td>
                        <td class="py-4 px-6 text-right whitespace-nowrap">
                            <a href="{{ route('ambulance.track', $booking->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 font-bold text-xs transition">
                                <i class="fa-solid fa-radar text-rose-500 animate-pulse"></i>
                                <span>Track Live</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-truck-medical text-4xl mb-3 text-slate-300"></i>
                            <div class="text-sm font-semibold text-slate-600">No ambulance bookings found</div>
                            <p class="text-xs text-slate-400 mt-1">You haven't requested any ambulance dispatches yet.</p>
                            <a href="{{ route('ambulance.book') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs transition">
                                <i class="fa-solid fa-bolt"></i>
                                <span>Book Emergency Ambulance</span>
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $bookings->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
