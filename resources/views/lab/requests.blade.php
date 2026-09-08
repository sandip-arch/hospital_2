@extends('layouts.app')

@section('title', 'Laboratory Diagnostics Queue')
@section('header_title', 'Laboratory & Diagnostics Queue')
@section('header_subtitle', 'Sample tracking, test execution, results entry, and diagnostic report publishing')

@section('content')
<div class="space-y-6" x-data="{ orderModal: false, resultModal: false, selectedReportId: null, selectedReportTest: '', selectedReportStatus: 'completed' }">

    <!-- Actions & Filters -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <form action="{{ route('lab.requests') }}" method="GET" class="flex items-center gap-3">
            <select name="status" onchange="this.form.submit()" class="px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                <option value="">All Test Statuses</option>
                <option value="requested" {{ request('status') == 'requested' ? 'selected' : '' }}>Requested (Pending)</option>
                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed & Published</option>
            </select>
        </form>

        <div class="flex items-center gap-3">
            <a href="{{ route('lab.catalog') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                <i class="fa-solid fa-book-medical mr-1"></i> Tests Catalog
            </a>
            @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
            <button @click="orderModal = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-1.5">
                <i class="fa-solid fa-flask-vial"></i> Order Diagnostic Test
            </button>
            @endif
        </div>
    </div>

    <!-- Requests Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200/80">
                        <th class="py-4 px-6">Test & Code</th>
                        <th class="py-4 px-6">Patient</th>
                        <th class="py-4 px-6">Ordering Physician</th>
                        <th class="py-4 px-6">Technician / Result</th>
                        <th class="py-4 px-6">Status</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($reports as $rep)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 text-sm block">{{ $rep->test->test_name }}</span>
                            <span class="font-mono text-[10px] text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">{{ $rep->test->code }}</span>
                        </td>
                        <td class="py-4 px-6">
                            <a href="{{ route('patients.show', $rep->patient_id) }}" class="font-bold text-slate-900 hover:text-cyan-600">
                                {{ $rep->patient->full_name }}
                            </a>
                            <span class="text-[10px] text-slate-400 font-mono block">{{ $rep->patient->patient_code }}</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">
                            {{ $rep->doctor->full_name }}
                        </td>
                        <td class="py-4 px-6 max-w-xs">
                            @if($rep->result_summary)
                            <p class="text-slate-700 truncate font-mono text-[11px]">{{ $rep->result_summary }}</p>
                            @else
                            <span class="text-slate-400">Awaiting lab results</span>
                            @endif
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $rep->status_badge }}">
                                {{ ucfirst(str_replace('_', ' ', $rep->status)) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-2">
                            @if(Auth::user()->isStaff() || Auth::user()->isAdmin() || Auth::user()->isDoctor())
                            <button @click="selectedReportId = {{ $rep->id }}; selectedReportTest = '{{ addslashes($rep->test->test_name) }}'; resultModal = true"
                                    class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold rounded-lg text-xs transition inline-block">
                                <i class="fa-solid fa-pen-to-square mr-1"></i> Enter Results
                            </button>
                            @endif
                            <a href="{{ route('lab.reports.show', $rep->id) }}" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition inline-block">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-vial text-4xl mb-3 text-slate-300"></i>
                            <p class="font-semibold text-slate-600 text-sm">No laboratory requests found.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $reports->links() }}
        </div>
        @endif
    </div>

    <!-- Order Test Modal (Doctor) -->
    <div x-show="orderModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="orderModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-flask text-purple-600"></i> Order Diagnostic Test
            </h4>

            <form action="{{ route('lab.order') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Patient *</label>
                    <select name="patient_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @foreach($patients as $p)
                        <option value="{{ $p->id }}">{{ $p->full_name }} ({{ $p->patient_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Select Diagnostic Test *</label>
                    <select name="lab_test_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @foreach($tests as $t)
                        <option value="{{ $t->id }}">{{ $t->test_name }} ({{ $t->code }}) - ${{ number_format($t->cost, 2) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ordering Physician *</label>
                    <select name="doctor_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}">{{ $doc->full_name }} ({{ $doc->department->name ?? 'General' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="orderModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md">Submit Order</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enter Results Modal (Lab Tech) -->
    <div x-show="resultModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div @click.outside="resultModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100">
            <h4 class="text-base font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-microscope text-purple-600"></i> Enter Findings for <span x-text="selectedReportTest" class="text-purple-700"></span>
            </h4>

            <form :action="'{{ url('lab/requests') }}/' + selectedReportId + '/result'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status *</label>
                    <select name="status" x-model="selectedReportStatus" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                        <option value="in_progress">In Progress (Sample Processing)</option>
                        <option value="completed">Completed & Verified</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Diagnostic Findings & Result Summary *</label>
                    <textarea name="result_summary" rows="4" required placeholder="Enter pathology parameters, numerical indices, reference ranges, and interpretation..."
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="resultModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs rounded-xl shadow-md">Publish Report</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
