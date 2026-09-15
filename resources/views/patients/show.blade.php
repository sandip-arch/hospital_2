@extends('layouts.app')

@section('title', $patient->full_name . ' - Patient Dossier')
@section('header_title', $patient->full_name)
@section('header_subtitle', 'UPI: ' . $patient->patient_code . ' • 360° Comprehensive Medical Dossier')

@section('content')
<div class="space-y-6" x-data="{ activeTab: '{{ request('tab', 'overview') }}', billingFilter: '{{ in_array(request('status'), ['paid', 'unpaid', 'partially_paid', 'checking', 'cancelled']) ? request('status') : 'all' }}', billingSearch: '' }">

    @if(request('from') === 'billing')
    <!-- Back to Billing Desk Navigation Banner -->
    <div class="bg-gradient-to-r from-cyan-600 to-blue-700 rounded-3xl p-5 text-white shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-white text-xl shrink-0">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div>
                <h3 class="font-black text-base">Patient Billing Dossier & Ledger</h3>
                <p class="text-xs text-cyan-100 mt-0.5">Reviewing complete invoice records, payment receipts, and financial history for {{ $patient->full_name }}</p>
            </div>
        </div>
        <a href="{{ route('billing.index') }}" class="px-4 py-2 bg-white text-slate-900 hover:bg-cyan-50 font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2 shrink-0">
            <i class="fa-solid fa-arrow-left"></i> Back to Billing Desk
        </a>
    </div>
    @endif

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

                <!-- Quick Financial Health Widget -->
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm text-xs space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-receipt text-cyan-600"></i> Billing & Balance
                        </h4>
                        <button type="button" @click="activeTab = 'billing'" class="text-[11px] font-bold text-cyan-600 hover:underline">
                            View Ledger &rarr;
                        </button>
                    </div>
                    @php
                        $overviewBilled = (float) $patient->invoices->sum('net_amount');
                        $overviewPaid = (float) $patient->invoices->sum(fn($i) => $i->paid_amount);
                        $overviewDue = max(0.00, $overviewBilled - $overviewPaid);
                    @endphp
                    <div class="p-3.5 bg-slate-50 rounded-2xl space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Total Invoiced:</span>
                            <span class="font-bold text-slate-800">${{ number_format($overviewBilled, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">Total Settled:</span>
                            <span class="font-bold text-emerald-600">${{ number_format($overviewPaid, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                            <span class="font-bold text-slate-700">Outstanding:</span>
                            <span class="font-black {{ $overviewDue > 0 ? 'text-rose-600' : 'text-emerald-600' }} text-sm">${{ number_format($overviewDue, 2) }}</span>
                        </div>
                    </div>
                    <button type="button" @click="activeTab = 'billing'" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-file-invoice-dollar text-cyan-600"></i> Open Patient Ledger ({{ $patient->invoices->count() }})
                    </button>
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

    <!-- Tab 7: Billing & Financial History -->
    <div x-show="activeTab === 'billing'" class="space-y-6" x-cloak>
        @php
            $patientTotalInvoiced = (float) $patient->invoices->sum('net_amount');
            $patientTotalPaid = (float) $patient->invoices->sum(fn($i) => $i->paid_amount);
            $patientBalanceDue = max(0.00, $patientTotalInvoiced - $patientTotalPaid);
            $patientUnpaidCount = $patient->invoices->whereIn('status', ['unpaid', 'checking', 'partially_paid'])->count();
            $patientPayments = $patient->invoices->flatMap->payments->sortByDesc('payment_date');
        @endphp

        <!-- Patient Financial Metrics KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Billed</p>
                    <h4 class="text-2xl font-black text-slate-900 mt-1">${{ number_format($patientTotalInvoiced, 2) }}</h4>
                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $patient->invoices->count() }} Total Invoices</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Settled</p>
                    <h4 class="text-2xl font-black text-emerald-600 mt-1">${{ number_format($patientTotalPaid, 2) }}</h4>
                    <p class="text-[10px] text-emerald-600 mt-0.5 font-semibold">Completed Payments</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Outstanding Balance</p>
                    <h4 class="text-2xl font-black text-rose-600 mt-1">${{ number_format($patientBalanceDue, 2) }}</h4>
                    <p class="text-[10px] text-rose-600 mt-0.5 font-semibold">Pending Patient Clearance</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Invoices</p>
                    <h4 class="text-2xl font-black text-amber-600 mt-1">{{ $patientUnpaidCount }}</h4>
                    <p class="text-[10px] text-amber-600 mt-0.5 font-semibold">Awaiting Settlement</p>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
        </div>

        <!-- Invoices & Billing Ledger Container -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-cyan-600"></i> Patient Invoices & Ledger
                    </h4>
                    <p class="text-xs text-slate-400 mt-0.5">Filter by payment status or search specific invoices and treatments</p>
                </div>
                @if(!Auth::user()->isPatient())
                <a href="{{ route('billing.create', ['patient_id' => $patient->id]) }}" class="px-3.5 py-2 bg-slate-900 hover:bg-cyan-600 text-white font-bold text-xs rounded-xl shadow-sm transition flex items-center gap-1.5 shrink-0">
                    <i class="fa-solid fa-file-circle-plus"></i> Create Custom Bill
                </a>
                @endif
            </div>

            <!-- Filter Bar: Statuses & Search -->
            <div class="space-y-3">
                <!-- Status Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold custom-scrollbar">
                    <button type="button" @click="billingFilter = 'all'"
                            :class="billingFilter === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <span>All Invoices</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'all' ? 'bg-slate-800 text-cyan-300' : 'bg-white text-slate-700'">
                            {{ $patient->invoices->count() }}
                        </span>
                    </button>

                    <button type="button" @click="billingFilter = 'paid'"
                            :class="billingFilter === 'paid' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Paid</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'paid' ? 'bg-emerald-700 text-white' : 'bg-emerald-100 text-emerald-800'">
                            {{ $patient->invoices->where('status', 'paid')->count() }}
                        </span>
                    </button>

                    <button type="button" @click="billingFilter = 'unpaid'"
                            :class="billingFilter === 'unpaid' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span>Unpaid</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'unpaid' ? 'bg-rose-700 text-white' : 'bg-rose-100 text-rose-800'">
                            {{ $patient->invoices->where('status', 'unpaid')->count() }}
                        </span>
                    </button>

                    <button type="button" @click="billingFilter = 'partially_paid'"
                            :class="billingFilter === 'partially_paid' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-percent text-[10px]"></i>
                        <span>Partially Paid</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'partially_paid' ? 'bg-blue-700 text-white' : 'bg-blue-100 text-blue-800'">
                            {{ $patient->invoices->where('status', 'partially_paid')->count() }}
                        </span>
                    </button>

                    <button type="button" @click="billingFilter = 'checking'"
                            :class="billingFilter === 'checking' ? 'bg-amber-500 text-white shadow-sm' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                        <span>Under Verification</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'checking' ? 'bg-amber-600 text-white' : 'bg-amber-100 text-amber-800'">
                            {{ $patient->invoices->where('status', 'checking')->count() }}
                        </span>
                    </button>

                    <button type="button" @click="billingFilter = 'cancelled'"
                            :class="billingFilter === 'cancelled' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200'"
                            class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 shrink-0">
                        <span>Cancelled</span>
                        <span class="px-1.5 py-0.5 rounded-md text-[10px]" :class="billingFilter === 'cancelled' ? 'bg-slate-800 text-slate-300' : 'bg-white text-slate-700'">
                            {{ $patient->invoices->where('status', 'cancelled')->count() }}
                        </span>
                    </button>
                </div>

                <!-- Instant Search Input -->
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" x-model="billingSearch" placeholder="Search patient's invoices by invoice #, doctor, service or item description..."
                           class="w-full pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <button type="button" x-show="billingSearch" @click="billingSearch = ''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs" title="Clear filter">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                            <th class="py-3.5 px-4">Invoice #</th>
                            <th class="py-3.5 px-4">Date & Due</th>
                            <th class="py-3.5 px-4">Consultant / Service</th>
                            <th class="py-3.5 px-4">Billed Items</th>
                            <th class="py-3.5 px-4">Net Total</th>
                            <th class="py-3.5 px-4">Settled</th>
                            <th class="py-3.5 px-4">Balance</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($patient->invoices->sortByDesc('invoice_date') as $inv)
                        @php
                            $searchKeywords = strtolower($inv->invoice_number . ' ' . ($inv->appointment?->doctor?->user?->name ?? '') . ' ' . ($inv->appointment?->department?->name ?? '') . ' ' . $inv->items->pluck('item_description')->join(' ') . ' ' . $inv->notes);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition"
                            x-show="(billingFilter === 'all' || billingFilter === '{{ $inv->status }}') && (billingSearch === '' || {{ json_encode($searchKeywords) }}.includes(billingSearch.toLowerCase()))">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                <a href="{{ route('billing.show', $inv->id) }}" class="text-cyan-700 hover:underline">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                <div>{{ $inv->invoice_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-slate-400">Due: {{ $inv->due_date->format('M d, Y') }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700">
                                <div class="font-semibold text-slate-900">{{ $inv->appointment?->doctor?->user?->name ?? 'Hospital Service' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $inv->appointment?->department?->name ?? 'General' }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-[180px]">
                                @if($inv->items->isNotEmpty())
                                <div class="truncate font-medium text-slate-800" title="{{ $inv->items->pluck('item_description')->join(', ') }}">
                                    {{ $inv->items->first()->item_description }}
                                    @if($inv->items->count() > 1)
                                    <span class="text-[10px] font-bold text-cyan-600">+{{ $inv->items->count() - 1 }} more</span>
                                    @endif
                                </div>
                                @else
                                <span class="text-slate-400 italic">Hospital Care</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900">${{ number_format($inv->net_amount, 2) }}</td>
                            <td class="py-3.5 px-4 text-emerald-600 font-bold">${{ number_format($inv->paid_amount, 2) }}</td>
                            <td class="py-3.5 px-4 text-rose-600 font-black">${{ number_format($inv->balance_due, 2) }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inv->status_badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $inv->status)) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                @if(!Auth::user()->isPatient() && in_array($inv->status, ['unpaid', 'partially_paid', 'checking']))
                                <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-lg text-[11px] shadow-sm transition inline-block">
                                    <i class="fa-solid fa-cash-register mr-1"></i> Settle
                                </a>
                                @else
                                <a href="{{ route('billing.show', $inv->id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] transition inline-block">
                                    View
                                </a>
                                @endif
                                <a href="{{ route('billing.print', $inv->id) }}" target="_blank" class="p-1.5 bg-slate-900 hover:bg-cyan-600 text-white rounded-lg text-[11px] transition inline-block" title="Print Receipt">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-600 text-xs">No invoices issued for this patient yet.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Payment & Transaction Receipts History Section -->
            <div class="pt-6 border-t border-slate-200/80">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-cyan-600"></i> Payment & Transaction Receipts History
                        </h4>
                        <p class="text-xs text-slate-400 mt-0.5">Individual settled receipts, UPI transaction IDs, card payments, and counter collections</p>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 text-slate-700 rounded-full font-bold text-xs">
                        {{ $patientPayments->count() }} Transactions Recorded
                    </span>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                                <th class="py-3 px-4">Receipt #</th>
                                <th class="py-3 px-4">Invoice #</th>
                                <th class="py-3 px-4">Date & Time</th>
                                <th class="py-3 px-4">Payment Method</th>
                                <th class="py-3 px-4">Txn Ref / Cheque</th>
                                <th class="py-3 px-4">Amount Paid</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($patientPayments as $pmt)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $pmt->payment_number }}</td>
                                <td class="py-3 px-4 font-mono">
                                    <a href="{{ route('billing.show', $pmt->invoice_id) }}" class="text-cyan-600 font-bold hover:underline">
                                        {{ $pmt->invoice->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-3 px-4 text-slate-600">{{ $pmt->payment_date->format('M d, Y - h:i A') }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold {{ $pmt->method_badge }}">
                                        {{ strtoupper(str_replace('_', ' ', $pmt->payment_method)) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">{{ $pmt->transaction_reference ?? '-' }}</td>
                                <td class="py-3 px-4 font-black text-emerald-600 text-sm">${{ number_format($pmt->amount_paid, 2) }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $pmt->status_badge }}">
                                        {{ ucfirst($pmt->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <a href="{{ route('billing.show', $pmt->invoice_id) }}" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-[11px] transition inline-block">
                                        View Bill
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                                    <p class="font-semibold text-xs">No payment transaction receipts recorded yet for this patient.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
