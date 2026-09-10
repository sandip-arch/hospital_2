@extends('layouts.app')

@section('title', 'Executive Governance Dashboard')
@section('header_title', 'Hospital Executive Dashboard')
@section('header_subtitle', 'System overview, financial analytics, facility occupancy, and clinical throughput')

@section('content')
<div class="space-y-8">

    <!-- Executive Governance & User Identity Header -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-950 to-indigo-950 rounded-3xl p-6 lg:p-7 text-white shadow-xl border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-purple-500/20 border border-purple-400/40 text-purple-300 text-2xl font-black flex items-center justify-center shrink-0 shadow-inner">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-xl lg:text-2xl font-black tracking-tight">{{ Auth::user()->name }}</h3>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/40">
                        {{ Auth::user()->primaryRoleDisplay() }}
                    </span>
                    <!-- Sign-in Username Badge -->
                    <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-slate-900/90 text-cyan-300 border border-cyan-500/40 shadow-xs flex items-center gap-1.5" title="Use this username or email to sign in">
                        <i class="fa-solid fa-at text-cyan-400 text-[11px]"></i>Sign-in Username: <strong class="text-white font-mono font-bold">{{ Auth::user()->username }}</strong>
                    </span>
                </div>
                <p class="text-xs text-slate-300 mt-1.5 flex flex-wrap items-center gap-2">
                    <span><i class="fa-regular fa-envelope text-cyan-400 mr-1"></i>{{ Auth::user()->email }}</span>
                    <span class="text-slate-600">&bull;</span>
                    <span class="text-emerald-400 font-semibold flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse inline-block"></span> Active Admin Session</span>
                    <span class="text-slate-600">&bull;</span>
                    <span class="text-slate-400">Sign in with either email or <code class="text-cyan-300 font-mono font-bold">{{ Auth::user()->username }}</code></span>
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-users-gear text-cyan-400"></i> User Roster
            </a>
            <a href="{{ route('admin.settings.index') }}" class="px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-sliders"></i> Settings
            </a>
        </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Total Revenue -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex items-center justify-between hover:shadow-card transition duration-200">
            <div>
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Total Revenue</p>
                <h3 class="font-heading text-2xl font-black text-slate-900 mt-1">${{ number_format($stats['total_revenue'], 2) }}</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fa-solid fa-arrow-trend-up text-[10px]"></i> Settled Payments
                </p>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner shrink-0">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
        </div>

        <!-- Bed Occupancy -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex items-center justify-between hover:shadow-card transition duration-200">
            <div>
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Bed Occupancy</p>
                <h3 class="font-heading text-2xl font-black text-slate-900 mt-1">{{ $stats['bed_occupancy_rate'] }}%</h3>
                <p class="text-[11px] text-cyan-700 font-bold mt-1">
                    {{ $stats['occupied_beds'] }} of {{ $stats['total_beds'] }} Beds In Use
                </p>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-xl shadow-inner shrink-0">
                <i class="fa-solid fa-bed-pulse"></i>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex items-center justify-between hover:shadow-card transition duration-200">
            <div>
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Today's Visits</p>
                <h3 class="font-heading text-2xl font-black text-slate-900 mt-1">{{ $stats['today_appointments'] }}</h3>
                <p class="text-[11px] text-blue-600 font-bold mt-1">
                    Active Outpatient Queue
                </p>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner shrink-0">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
        </div>

        <!-- Total Patients -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft flex items-center justify-between hover:shadow-card transition duration-200">
            <div>
                <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider">Registered Patients</p>
                <h3 class="font-heading text-2xl font-black text-slate-900 mt-1">{{ $stats['total_patients'] }}</h3>
                <p class="text-[11px] text-purple-600 font-bold mt-1">
                    Unique UPI Identities
                </p>
            </div>
            <div class="w-13 h-13 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shadow-inner shrink-0">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
        </div>

    </div>

    <!-- Quick Action Bar -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-cyan-950 rounded-3xl p-6 lg:p-7 text-white shadow-xl flex flex-wrap items-center justify-between gap-4 border border-slate-800/80">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse"></span>
                <h4 class="font-heading text-base font-bold text-white">Administrator Operations Center</h4>
            </div>
            <p class="text-xs text-slate-400 mt-0.5 font-medium">Quickly launch clinical workflows, manage hospital resources, or review audit logs</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('patients.create') }}" class="px-4 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-extrabold rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-plus"></i> Register Patient
            </a>
            <a href="{{ route('appointments.create') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-plus text-cyan-400"></i> Book Appointment
            </a>
            <a href="{{ route('facilities.bed-tracker') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-bed text-cyan-400"></i> Bed Board
            </a>
            <a href="{{ route('billing.create') }}" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-xl border border-slate-700 transition flex items-center gap-1.5">
                <i class="fa-solid fa-receipt text-emerald-400"></i> Create Invoice
            </a>
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-user-shield"></i> Add User
            </a>
        </div>
    </div>

    <!-- Charts & Analytics Row -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Revenue & Monthly Trends Chart -->
        <div class="lg:col-span-8 bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-heading text-base font-bold text-slate-900">Hospital Department & Financial Throughput</h3>
                    <p class="text-xs text-slate-500 font-medium">Breakdown of patient consultations across clinical departments</p>
                </div>
                <span class="px-3 py-1 bg-slate-100 text-slate-700 text-xs font-bold rounded-full border border-slate-200">Fiscal Year 2026</span>
            </div>
            <div class="h-64">
                <canvas id="departmentChart"></canvas>
            </div>
        </div>

        <!-- Facility & Low Stock Widget -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Bed Occupancy Gauge Widget -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft">
                <h4 class="font-heading text-sm font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-bed-pulse text-cyan-600"></i> Live Ward Capacity
                </h4>
                <div class="space-y-3 text-xs font-medium">
                    <div>
                        <div class="flex justify-between text-slate-600 font-bold mb-1.5">
                            <span>Occupied Beds</span>
                            <span>{{ $stats['occupied_beds'] }} / {{ $stats['total_beds'] }} ({{ $stats['bed_occupancy_rate'] }}%)</span>
                        </div>
                        <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-0.5">
                            <div class="bg-gradient-to-r from-cyan-500 to-blue-600 h-full rounded-full transition-all duration-500" style="width: {{ $stats['bed_occupancy_rate'] }}%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-2 text-[11px] text-slate-500 font-semibold">
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Available: {{ $stats['total_beds'] - $stats['occupied_beds'] }}</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-cyan-600"></span> In Use: {{ $stats['occupied_beds'] }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                    <a href="{{ route('facilities.bed-tracker') }}" class="text-xs font-bold text-cyan-600 hover:underline">
                        Open Visual Bed Board &rarr;
                    </a>
                </div>
            </div>

            <!-- Low Stock Warnings -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="font-heading text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500"></i> Low Stock Alerts
                    </h4>
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-900 text-[10px] font-bold rounded-full">
                        {{ $lowStockMedicines->count() }} Alert(s)
                    </span>
                </div>
                <div class="divide-y divide-slate-100 text-xs">
                    @forelse($lowStockMedicines as $med)
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-slate-900">{{ $med->name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">Threshold: {{ $med->reorder_level }} units</p>
                        </div>
                        <span class="px-2.5 py-1 bg-rose-50 border border-rose-200 text-rose-700 font-bold rounded-xl text-xs">
                            {{ $med->stock_quantity }} Left
                        </span>
                    </div>
                    @empty
                    <p class="text-xs text-slate-400 py-3 text-center">All pharmaceutical inventory is adequately stocked.</p>
                    @endforelse
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 text-center">
                    <a href="{{ route('pharmacy.index', ['low_stock' => 1]) }}" class="text-xs font-bold text-cyan-600 hover:underline">
                        Manage Pharmacy Stock &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Tables Row: Recent Appointments & Security Audit Logs -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- Recent Appointments -->
        <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-heading text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-calendar-days text-cyan-600"></i> Recent Appointments
                </h3>
                <a href="{{ route('appointments.index') }}" class="text-xs font-bold text-cyan-600 hover:underline">View All</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                            <th class="pb-2.5">Patient</th>
                            <th class="pb-2.5">Doctor</th>
                            <th class="pb-2.5">Date & Slot</th>
                            <th class="pb-2.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentAppointments as $apt)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 font-bold text-slate-900">{{ $apt->patient->full_name }}</td>
                            <td class="py-3 text-slate-600 font-medium">{{ $apt->doctor->full_name }}</td>
                            <td class="py-3 text-slate-500 font-medium">{{ $apt->appointment_date->format('M d') }} ({{ $apt->time_slot }})</td>
                            <td class="py-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge }}">
                                    {{ ucfirst($apt->status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Security Audit Trail -->
        <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-heading text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-purple-600"></i> System Audit Stream
                </h3>
                <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-bold text-purple-600 hover:underline">Full Log</a>
            </div>

            <div class="space-y-3">
                @foreach($recentAuditLogs as $log)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3 text-xs">
                    <span class="px-2.5 py-0.5 rounded-lg font-mono text-[10px] font-extrabold uppercase {{ $log->action === 'CREATE' ? 'bg-emerald-100 text-emerald-800' : ($log->action === 'DELETE' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ $log->action }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 truncate">{{ $log->details }}</p>
                        <p class="text-[10px] text-slate-400 mt-0.5 font-medium">By {{ $log->user->name ?? 'System' }} &bull; {{ $log->created_at->diffForHumans() }} &bull; IP: {{ $log->ip_address }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('departmentChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Cardiology', 'Neurology', 'Pediatrics', 'Orthopedics', 'Emergency', 'Radiology', 'Gen Surgery', 'Oncology'],
                    datasets: [{
                        label: 'Patient Consultations',
                        data: [45, 32, 38, 29, 62, 40, 26, 18],
                        backgroundColor: '#0ea5e9',
                        borderRadius: 8,
                    }, {
                        label: 'Inpatient Admissions',
                        data: [14, 8, 10, 12, 28, 6, 16, 9],
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                        x: { grid: { display: false } }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection
