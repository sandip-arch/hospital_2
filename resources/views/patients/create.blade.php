@extends('layouts.app')

@section('title', 'Register New Patient')
@section('header_title', 'Patient Registration & Onboarding')
@section('header_subtitle', 'Assign Unique Patient Identifier (UPI), demographics, medical history, and emergency contacts')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('patients.index') }}" class="text-xs font-bold text-slate-500 hover:text-cyan-600 flex items-center gap-1.5 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Patient Directory
        </a>
    </div>

    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        
        <form action="{{ route('patients.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Demographics Section -->
            <div>
                <h4 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-id-card text-cyan-600"></i> Primary Demographics & Medical Identity
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">First Name *</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Johnathan">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Last Name *</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Doe">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth *</label>
                        <input type="date" name="dob" value="{{ old('dob') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Gender *</label>
                        <select name="gender" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Blood Type</label>
                        <select name="blood_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                            <option value="">Unknown</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt)
                            <option value="{{ $bt }}" {{ old('blood_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number *</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="+1 (555) 000-0000">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="patient@example.com">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Residential Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Street, City, State, ZIP">
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Initial Medical History / Chronic Conditions / Allergies</label>
                    <textarea name="medical_history" rows="2"
                              class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Hypertension, Penicillin Allergy, Prior Appendectomy">{{ old('medical_history') }}</textarea>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div class="pt-4 border-t border-slate-100">
                <h4 class="text-sm font-bold text-slate-900 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i class="fa-solid fa-phone-volume text-rose-600"></i> Emergency Contact Information
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Contact Name *</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Mary Doe">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Relationship *</label>
                        <input type="text" name="relationship" value="{{ old('relationship') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Spouse, Parent, Sibling">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Primary Phone *</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone') }}" required
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="+1 (555) 000-0000">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alternate Phone</label>
                        <input type="text" name="alt_phone" value="{{ old('alt_phone') }}"
                               class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="optional">
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end gap-3">
                <a href="{{ route('patients.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i> Register Patient & Generate UPI
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
