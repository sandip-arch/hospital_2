@extends('layouts.app')

@section('title', 'My Profile & Security')
@section('header_title', 'Account Profile')
@section('header_subtitle', 'Manage personal credentials and security preferences')

@section('content')
<!-- Phone Input Library & Password styling -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/css/intlTelInput.css">
<style>
    .iti { width: 100%; display: block; }
    .iti__country-list { margin: 0; padding: 0; text-align: left; }
    .iti__search-input { padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; outline: none; width: calc(100% - 16px); margin: 8px; }
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none;
    }
</style>

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Profile Overview Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm flex flex-col sm:flex-row items-center gap-6">
        <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white text-3xl font-extrabold shadow-lg shadow-cyan-500/20 shrink-0">
            {{ substr($user->name, 0, 1) }}
        </div>
        <div class="flex-1 text-center sm:text-left min-w-0">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h3 class="text-xl font-bold text-slate-900">{{ $user->name }}</h3>
                <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $user->roleBadgeColor() }}">
                    {{ $user->primaryRoleDisplay() }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Username: <span class="font-mono text-slate-700 font-bold">{{ $user->username }}</span> &bull; Email: {{ $user->email }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Member since {{ $user->created_at->format('M d, Y') }} &bull; Account Status: <span class="text-emerald-600 font-bold uppercase">{{ $user->status }}</span></p>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-user-pen text-cyan-600"></i> Update Profile Information
            </h4>
        </div>

        @if($errors->any())
        <div class="mb-5 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Name Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $firstName) }}"
                           oninput="capitalizeInitials(this)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm capitalize focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Johnathan">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $lastName) }}"
                           oninput="capitalizeInitials(this)"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm capitalize focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Doe">
                </div>
            </div>

            <!-- Demographics (DOB, Gender, Blood Type) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" value="{{ old('dob', $dob) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Gender</label>
                    <select name="gender" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">Select Gender</option>
                        <option value="Male" {{ old('gender', $gender) == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $gender) == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ old('gender', $gender) == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Blood Type</label>
                    <select name="blood_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                        <option value="">Select Blood Type</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'not known'] as $bt)
                        <option value="{{ $bt }}" {{ old('blood_type', $bloodType) == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Phone Number & Residential Address (Email removed) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                    <div class="relative shadow-sm rounded-xl">
                        <input type="hidden" name="phone" id="full_phone_input" value="{{ old('phone', $phone) }}">
                        <input type="tel" id="phone_input" value="{{ old('phone', $phone) }}"
                               class="w-full py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition tracking-wide font-medium"
                               placeholder="Enter number">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Residential Address</label>
                    <input type="text" name="address" value="{{ old('address', $address) }}"
                           class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Street Address, City, State, ZIP">
                </div>
            </div>

            <!-- Change Security Password with Eye Icons -->
            <div class="pt-5 border-t border-slate-100">
                <div class="mb-3">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-700">Change Security Password</h5>
                    <p class="text-[11px] text-slate-500 font-medium">To change your password, all 3 fields below are required. Otherwise, leave them blank.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Current Password</label>
                        <div class="relative">
                            <input id="current_password" type="password" name="current_password"
                                   class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="••••••••">
                            <span class="toggle-password absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer z-10 text-slate-400 hover:text-slate-600 transition" onclick="togglePasswordVisibility('current_password', 'toggle-icon-current')">
                                <i id="toggle-icon-current" class="fa fa-eye text-sm"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">New Password</label>
                        <div class="relative">
                            <input id="new_password" type="password" name="new_password"
                                   class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Min. 6 chars">
                            <span class="toggle-password absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer z-10 text-slate-400 hover:text-slate-600 transition" onclick="togglePasswordVisibility('new_password', 'toggle-icon-new')">
                                <i id="toggle-icon-new" class="fa fa-eye text-sm"></i>
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password</label>
                        <div class="relative">
                            <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                                   class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Repeat password">
                            <span class="toggle-password absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer z-10 text-slate-400 hover:text-slate-600 transition" onclick="togglePasswordVisibility('new_password_confirmation', 'toggle-icon-confirm')">
                                <i id="toggle-icon-confirm" class="fa fa-eye text-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                </button>
            </div>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/js/intlTelInput.min.js"></script>
<script>
    function capitalizeInitials(input) {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        input.value = input.value.replace(/(?:^|\s|-)\S/g, function(char) {
            return char.toUpperCase();
        });
        input.setSelectionRange(start, end);
    }

    function togglePasswordVisibility(inputId, iconId) {
        const passwordInput = document.getElementById(inputId);
        const toggleIcon = document.getElementById(iconId);

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const phoneInput = document.querySelector("#phone_input");
        const hiddenInput = document.querySelector("#full_phone_input");
        
        if (phoneInput && window.intlTelInput) {
            const iti = window.intlTelInput(phoneInput, {
                initialCountry: "in",
                separateDialCode: true,
                strictMode: true,
                utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/js/utils.js"
            });
            
            // Set initial hidden value
            if (hiddenInput && !hiddenInput.value && phoneInput.value) {
                hiddenInput.value = iti.getNumber() || phoneInput.value;
            }

            phoneInput.addEventListener('input', function() {
                hiddenInput.value = iti.getNumber();
            });

            phoneInput.addEventListener('countrychange', function() {
                hiddenInput.value = iti.getNumber();
            });
        }
    });
</script>
@endsection
