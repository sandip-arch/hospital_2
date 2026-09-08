@extends('layouts.app')

@section('title', $patient->full_name . ' - Patient Dossier')
@section('header_title', $patient->full_name)
@section('header_subtitle', 'UPI: ' . $patient->patient_code . ' • 360° Comprehensive Medical Dossier')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'overview' }">

    <!-- Patient Master Profile Banner -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-18 h-18 rounded-3xl bg-gradient-to-tr from-cyan-600 to-blue-700 text-white font-black text-3xl flex items-center justify-center p-4 shadow-lg shadow-cyan-500/20 shrink-0">
                {{ substr($patient->first_name, 0, 1) }}{{ substr($patient->last_name, 0, 1) }}
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h2 class="text-2xl font-black text-slate-900">{{ $patient->full_name }}</h2>
                    <span class="font-mono text-xs font-bold px-3 py-1 bg-cyan-50 text-cyan-700 border border-cyan-200 rounded-full">
                        {{ $patient->patient_code }}
                    </span>
                    @if($patient->blood_type)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <i class="fa-solid fa-droplet mr-1"></i> {{ $patient->blood_type }}
                    </span>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 mt-2">
                    <span><i class="fa-solid fa-cake-candles text-slate-400 mr-1"></i> {{ $patient->age }} yrs ({{ $patient->dob ? $patient->dob->format('M d, Y') : 'N/A' }})</span>
                    <span><i class="fa-solid fa-venus-mars text-slate-400 mr-1"></i> {{ $patient->gender }}</span>
                    <span><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $patient->phone }}</span>
                    <span><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ $patient->email ?? 'No email' }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-calendar-plus"></i> Book Visit
            </a>
            <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-notes-medical"></i> Add EMR Note
            </a>
            <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5">
                <i class="fa-solid fa-prescription"></i> Write Rx
            </a>
            <a href="{{ route('patients.edit', $patient->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-pen"></i> Edit
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex overflow-x-auto gap-2 p-1.5 bg-slate-200/70 rounded-2xl text-xs font-bold text-slate-600 custom-scrollbar">
        <button @click="activeTab = 'overview'" :class="activeTab === 'overview' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-chart-simple"></i> Clinical Overview
        </button>
        <button @click="activeTab = 'appointments'" :class="activeTab === 'appointments' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-calendar-check"></i> Visits ({{ $patient->appointments->count() }})
        </button>
        <button @click="activeTab = 'emr'" :class="activeTab === 'emr' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-notes-medical"></i> EMR Notes ({{ $patient->medicalRecords->count() }})
        </button>
        <button @click="activeTab = 'prescriptions'" :class="activeTab === 'prescriptions' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-prescription"></i> Prescriptions ({{ $patient->prescriptions->count() }})
        </button>
        <button @click="activeTab = 'labs'" :class="activeTab === 'labs' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-flask-vial"></i> Diagnostics ({{ $patient->labReports->count() }})
        </button>
        <button @click="activeTab = 'admissions'" :class="activeTab === 'admissions' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-bed-pulse"></i> Admissions ({{ $patient->admissions->count() }})
        </button>
        <button @click="activeTab = 'billing'" :class="activeTab === 'billing' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-receipt"></i> Invoices ({{ $patient->invoices->count() }})
        </button>
        <button @click="activeTab = 'documents'" :class="activeTab === 'documents' ? 'bg-white text-slate-900 shadow-sm' : 'hover:text-slate-900'" class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-folder-open"></i> Documents ({{ $patient->documents->count() }})
        </button>
    </div>

    <!-- Tab 1: Clinical Overview -->
    <div x-show="activeTab === 'overview'" class="space-y-6" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Demographics & Emergency Contact -->
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-xs space-y-3">
                    <h4 class="font-bold text-slate-900 text-sm pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-address-card text-cyan-600"></i> Contact & Address
                    </h4>
                    <p><strong class="text-slate-700">Phone:</strong> {{ $patient->phone }}</p>
                    <p><strong class="text-slate-700">Email:</strong> {{ $patient->email ?? 'N/A' }}</p>
                    <p><strong class="text-slate-700">Address:</strong> {{ $patient->address ?? 'N/A' }}</p>
                </div>

                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-xs space-y-3">
                    <h4 class="font-bold text-slate-900 text-sm pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i class="fa-solid fa-phone-volume text-rose-600"></i> Emergency Contacts
                    </h4>
                    @forelse($patient->emergencyContacts as $ec)
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <p class="font-bold text-slate-800">{{ $ec->contact_name }} ({{ $ec->relationship }})</p>
                        <p class="text-slate-600 mt-0.5"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ $ec->phone }}</p>
                        @if($ec->alt_phone)<p class="text-slate-500 text-[10px]">Alt: {{ $ec->alt_phone }}</p>@endif
                    </div>
                    @empty
                    <p class="text-slate-400">No emergency contacts registered.</p>
                    @endforelse
                </div>
            </div>

            <!-- Right: Medical History & Latest Vitals -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Medical Background -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <h4 class="font-bold text-slate-900 text-sm mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-notes-medical text-cyan-600"></i> Medical History & Chronic Conditions
                    </h4>
                    <div class="p-4 bg-slate-50 rounded-2xl text-xs text-slate-700 leading-relaxed">
                        {{ $patient->medical_history ?? 'No prior chronic conditions recorded during registration.' }}
                    </div>
                </div>

                <!-- Latest Vital Signs Observations -->
                @if($patient->medicalRecords->isNotEmpty() && $patient->medicalRecords->first()->details->isNotEmpty())
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-heart-pulse text-rose-600"></i> Latest Vital Signs Recorded
                        </h4>
                        <span class="text-xs text-slate-400">{{ $patient->medicalRecords->first()->visit_date->format('M d, Y') }}</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                        @foreach($patient->medicalRecords->first()->details as $vital)
                        <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ $vital->vital_sign_name }}</span>
                            <span class="text-base font-black text-slate-900 mt-0.5 block">{{ $vital->vital_sign_value }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>

        </div>
    </div>

    <!-- Tab 2: Appointments -->
    <div x-show="activeTab === 'appointments'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm" x-cloak>
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h4 class="font-bold text-slate-900 text-base">Appointments History</h4>
            <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="px-3 py-1.5 bg-cyan-600 text-white font-bold text-xs rounded-xl shadow-sm">
                + Book Visit
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200/80">
                        <th class="py-3 px-4">Date & Slot</th>
                        <th class="py-3 px-4">Doctor</th>
                        <th class="py-3 px-4">Department</th>
                        <th class="py-3 px-4">Reason</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patient->appointments as $apt)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $apt->appointment_date->format('M d, Y') }} ({{ $apt->time_slot }})</td>
                        <td class="py-3 px-4">{{ $apt->doctor->full_name }}</td>
                        <td class="py-3 px-4">{{ $apt->department->name ?? 'General' }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $apt->reason ?? 'General Visit' }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $apt->status_badge }}">
                                {{ ucfirst($apt->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-8 text-center text-slate-400">No appointments on record.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 3: EMR Medical Records -->
    <div x-show="activeTab === 'emr'" class="space-y-4" x-cloak>
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-base">Electronic Medical Records (EMR)</h4>
            <a href="{{ route('medical-records.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-cyan-600 text-white font-bold text-xs rounded-xl shadow-sm">
                + New Consultation Note
            </a>
        </div>

        @forelse($patient->medicalRecords as $mr)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-900 text-base">{{ $mr->diagnosis }}</h5>
                    <p class="text-xs text-slate-500">Seen by {{ $mr->doctor->full_name }} &bull; {{ $mr->visit_date->format('M d, Y - h:i A') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('medical-records.show', $mr->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg">View</a>
                    <a href="{{ route('medical-records.print', $mr->id) }}" target="_blank" class="px-3 py-1.5 bg-slate-900 hover:bg-cyan-600 text-white font-bold text-xs rounded-lg"><i class="fa-solid fa-print"></i></a>
                </div>
            </div>

            @if($mr->symptoms)
            <p class="text-xs text-slate-700"><strong class="text-slate-900">Symptoms:</strong> {{ $mr->symptoms }}</p>
            @endif

            @if($mr->notes)
            <div class="p-3.5 bg-slate-50 rounded-xl text-xs text-slate-700 leading-relaxed">
                <strong class="text-slate-900 block mb-1">SOAP Clinical Notes:</strong>
                {{ $mr->notes }}
            </div>
            @endif

            <!-- Vitals Grid -->
            @if($mr->details->isNotEmpty())
            <div class="flex flex-wrap gap-2 pt-2">
                @foreach($mr->details as $vit)
                <span class="px-3 py-1 bg-cyan-50 border border-cyan-100 text-cyan-900 rounded-lg text-xs font-semibold">
                    {{ $vit->vital_sign_name }}: <strong>{{ $vit->vital_sign_value }}</strong>
                </span>
                @endforeach
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            No medical records recorded yet.
        </div>
        @endforelse
    </div>

    <!-- Tab 4: Prescriptions -->
    <div x-show="activeTab === 'prescriptions'" class="space-y-4" x-cloak>
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-base">Prescriptions</h4>
            <a href="{{ route('prescriptions.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-emerald-600 text-white font-bold text-xs rounded-xl shadow-sm">
                + Issue Prescription
            </a>
        </div>

        @forelse($patient->prescriptions as $rx)
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h5 class="font-bold text-slate-900 text-sm">Prescription #Rx-{{ $rx->id }}</h5>
                    <p class="text-xs text-slate-400">Prescribed by {{ $rx->doctor->full_name }} &bull; {{ $rx->prescribed_date->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $rx->status_badge }}">
                        {{ ucfirst($rx->status) }}
                    </span>
                    <a href="{{ route('prescriptions.print', $rx->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold">
                        <i class="fa-solid fa-print"></i> Print Rx
                    </a>
                </div>
            </div>

            <div class="divide-y divide-slate-100 text-xs bg-slate-50 rounded-2xl p-4">
                @foreach($rx->items as $item)
                <div class="py-2 flex items-center justify-between">
                    <div>
                        <span class="font-bold text-slate-900">{{ $item->medicine->name ?? 'Drug' }}</span>
                        <span class="text-slate-500 font-mono ml-2">({{ $item->dosage }})</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $item->frequency }} &bull; {{ $item->duration_days }} Days &bull; Instructions: {{ $item->instructions }}</p>
                    </div>
                    <span class="font-bold text-slate-700">Quantity: {{ $item->quantity_prescribed }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <div class="bg-white rounded-3xl p-12 text-center text-slate-400 border border-slate-200">
            No prescriptions recorded yet.
        </div>
        @endforelse
    </div>

    <!-- Tab 5: Diagnostics & Lab Reports -->
    <div x-show="activeTab === 'labs'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm" x-cloak>
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h4 class="font-bold text-slate-900 text-base">Diagnostic Tests & Laboratory Reports</h4>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($patient->labReports as $lr)
            <div class="py-4 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900 text-sm">{{ $lr->test->test_name }}</span>
                        <span class="font-mono text-[10px] text-slate-400">{{ $lr->test->code }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $lr->status_badge }}">
                            {{ ucfirst($lr->status) }}
                        </span>
                    </div>
                    <p class="text-slate-600 mt-1">Ordered by: {{ $lr->doctor->full_name }} &bull; Ordered {{ $lr->created_at->format('M d, Y') }}</p>
                    @if($lr->result_summary)
                    <p class="text-[11px] text-slate-700 mt-1 bg-slate-50 p-2 rounded-lg font-mono">{{ $lr->result_summary }}</p>
                    @endif
                </div>
                <a href="{{ route('lab.reports.show', $lr->id) }}" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-xl shrink-0">
                    View Report
                </a>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-8 text-center">No diagnostic laboratory tests on file.</p>
            @endforelse
        </div>
    </div>

    <!-- Tab 6: Admissions -->
    <div x-show="activeTab === 'admissions'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm" x-cloak>
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h4 class="font-bold text-slate-900 text-base">Inpatient Hospital Admissions</h4>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            @forelse($patient->admissions as $adm)
            <div class="py-4 flex items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-900">Room {{ $adm->bed->room->room_number }} ({{ $adm->bed->room->room_type }}) &bull; Bed #{{ $adm->bed->bed_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $adm->status_badge }}">
                            {{ ucfirst($adm->status) }}
                        </span>
                    </div>
                    <p class="text-slate-500 mt-1">Admitted {{ $adm->admission_date->format('M d, Y - h:i A') }} &bull; Attending: {{ $adm->doctor->full_name }}</p>
                    @if($adm->admission_reason)<p class="text-slate-600 mt-1">Reason: {{ $adm->admission_reason }}</p>@endif
                </div>
                <span class="font-bold text-slate-700 bg-slate-100 px-3 py-1 rounded-xl">
                    {{ $adm->stay_days }} Day(s)
                </span>
            </div>
            @empty
            <p class="text-xs text-slate-400 py-8 text-center">No inpatient admissions on file.</p>
            @endforelse
        </div>
    </div>

    <!-- Tab 7: Billing -->
    <div x-show="activeTab === 'billing'" class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm" x-cloak>
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h4 class="font-bold text-slate-900 text-base">Invoices & Financial Ledger</h4>
            <a href="{{ route('billing.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-slate-900 text-white font-bold text-xs rounded-xl shadow-sm">
                + Create Custom Bill
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200/80">
                        <th class="py-3 px-4">Invoice #</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Net Total</th>
                        <th class="py-3 px-4">Paid</th>
                        <th class="py-3 px-4">Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($patient->invoices as $inv)
                    <tr class="hover:bg-slate-50">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $inv->invoice_number }}</td>
                        <td class="py-3 px-4">{{ $inv->invoice_date->format('M d, Y') }}</td>
                        <td class="py-3 px-4 font-bold text-slate-900">${{ number_format($inv->net_amount, 2) }}</td>
                        <td class="py-3 px-4 text-emerald-600 font-bold">${{ number_format($inv->paid_amount, 2) }}</td>
                        <td class="py-3 px-4 text-rose-600 font-black">${{ number_format($inv->balance_due, 2) }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                {{ ucfirst($inv->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right space-x-1">
                            <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px]">View</a>
                            <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="px-2.5 py-1 bg-slate-900 text-white font-bold rounded-lg text-[11px]"><i class="fa-solid fa-print"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-8 text-center text-slate-400">No invoices issued.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tab 8: Documents -->
    <div x-show="activeTab === 'documents'" class="space-y-6" x-cloak>
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h4 class="font-bold text-slate-900 text-base">Attached Documents (ID, Insurance, Scans)</h4>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('patients.uploadDocument', $patient->id) }}" method="POST" class="p-4 bg-slate-50 rounded-2xl border border-slate-100 flex flex-wrap items-end gap-3 mb-6">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Document Description / Name *</label>
                    <input type="text" name="file_name" required placeholder="e.g. National ID Card or Insurance Card"
                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Document Type *</label>
                    <select name="document_type" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="ID Card">ID Card</option>
                        <option value="Insurance Policy">Insurance Policy</option>
                        <option value="Diagnostic X-Ray">Diagnostic X-Ray</option>
                        <option value="Prior Health Record">Prior Health Record</option>
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                    <i class="fa-solid fa-cloud-arrow-up mr-1"></i> Attach Document
                </button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                @forelse($patient->documents as $doc)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                    <i class="fa-solid fa-file-pdf text-rose-500 text-2xl"></i>
                    <div class="min-w-0">
                        <p class="font-bold text-slate-800 truncate">{{ $doc->file_name ?? $doc->document_type }}</p>
                        <p class="text-[10px] text-slate-400">{{ $doc->document_type }} &bull; {{ $doc->uploaded_at->format('M d, Y') }}</p>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-6 text-slate-400">No documents attached yet.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
