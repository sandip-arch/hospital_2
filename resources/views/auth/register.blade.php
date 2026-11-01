@extends('layouts.auth')

@section('title', 'Patient Self-Registration')

@section('content')
<!-- GUARANTEED CSS INJECTION FOR PHONE DROPDOWN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/css/intlTelInput.css">
<style>
    /* Force the library to respect Tailwind's layout */
    .iti { width: 100%; display: block; }
    .iti__country-list { margin: 0; padding: 0; text-align: left; }
    .iti__search-input { padding: 8px; border-radius: 8px; border: 1px solid #e2e8f0; outline: none; width: calc(100% - 16px); margin: 8px; }
    input[type="password"]::-ms-reveal,
    input[type="password"]::-ms-clear {
        display: none;
    }
</style>
<div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-800/40 p-8 lg:p-12">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6 pb-6 border-b border-slate-100">
        <div class="flex items-center gap-3">
            @if(!empty($hospitalSettings['hospital_logo']))
            <img src="{{ Str::startsWith($hospitalSettings['hospital_logo'], 'http') ? $hospitalSettings['hospital_logo'] : asset($hospitalSettings['hospital_logo']) }}" 
                 alt="{{ $hospitalSettings['hospital_name'] ?? 'Apex Horizon' }}"
                 class="w-10 h-10 rounded-xl object-contain bg-slate-50 p-1 border border-cyan-200 shadow-md shrink-0">
            @else
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/30 shrink-0">
                <i class="fa-solid fa-hospital-user text-xl"></i>
            </div>
            @endif
            <div>
                <h2 class="font-black text-slate-900 text-lg leading-tight">Patient Portal Registration</h2>
                <p class="text-xs text-cyan-600 font-bold uppercase tracking-wider">{{ $hospitalSettings['hospital_name'] ?? 'Instant UPI Generation' }}</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-cyan-600 font-semibold flex items-center gap-1">
            <i class="fa-solid fa-arrow-left"></i> Back to Login
        </a>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif



    <form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">First Name <span class="text-rose-500 font-bold">*</span></label>
                <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}" required
                    oninput="capitalizeInitials(this)"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm capitalize focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Johnathan">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Last Name <span class="text-rose-500 font-bold">*</span></label>
                <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}" required
                    oninput="capitalizeInitials(this)"
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm capitalize focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="e.g. Doe">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Date of Birth <span class="text-rose-500 font-bold">*</span></label>
                <input type="date" name="dob" value="{{ old('dob') }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Gender <span class="text-rose-500 font-bold">*</span></label>
                <select name="gender" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                    <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Blood Type <span class="text-slate-400 font-normal text-[11px]">(Optional)</span></label>
                <select name="blood_type" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500">
                    
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-','not known'] as $bt)
                    <option value="{{ $bt }}" {{ old('blood_type') == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address <span class="text-rose-500 font-bold">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="john@example.com">
            </div>

            <!-- Phone Number Field -->
<div>
    <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number <span class="text-rose-500 font-bold">*</span></label>
    <div class="relative shadow-sm rounded-xl">
        
        <!-- HIDDEN FIELD: Sends the combined +919876543210 to Laravel -->
        <input type="hidden" name="phone" id="full_phone_input" value="{{ old('phone') }}">

        <!-- VISUAL INPUT: The library takes over this field -->
        <input type="tel" id="phone_input" required
               class="w-full py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500 transition tracking-wide font-medium"
               placeholder="Enter number">
    </div>
</div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Residential Address <span class="text-slate-400 font-normal text-[11px]">(Optional)</span></label>
            <input type="text" name="address" value="{{ old('address') }}"
                   class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" placeholder="Street Address, City, State, ZIP">
        </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Password <span class="text-rose-500 font-bold">*</span></label>
            <div class="relative">
                <input id="password" type="password" name="password" required
                       class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" 
                       placeholder="Minimum 6 characters">
                <span id="toggle-span-reg" class="toggle-password absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer z-10 text-slate-400 hover:text-slate-600 transition" onclick="togglePasswordVisibility('password', 'toggle-icon')">
                    <i id="toggle-icon" class="fa fa-eye text-sm"></i>
                </span>
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password <span class="text-rose-500 font-bold">*</span></label>
            <div class="relative">
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="w-full pl-3.5 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-cyan-500" 
                       placeholder="Repeat password">
                <span id="toggle-span-confirm" class="toggle-password absolute right-3.5 top-1/2 -translate-y-1/2 cursor-pointer z-10 text-slate-400 hover:text-slate-600 transition" onclick="togglePasswordVisibility('password_confirmation', 'toggle-icon-confirm')">
                    <i id="toggle-icon-confirm" class="fa fa-eye text-sm"></i>
                </span>
            </div>
        </div>
    </div>

        <div class="pt-4">
            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white font-bold rounded-2xl shadow-lg shadow-cyan-600/30 transition flex items-center justify-center gap-2">
                <i class="fa-solid fa-id-card"></i> Create Account & Generate UPI
            </button>
        </div>
    </form>

    <div class="mt-6 text-center text-xs text-slate-500">
        Already have a patient or staff profile? <a href="{{ route('login') }}" class="text-cyan-600 font-bold hover:underline">Log in here</a>
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
        
        // Initialize the library
        const iti = window.intlTelInput(phoneInput, {
            initialCountry: "in", // Defaults to India 🇮🇳
            separateDialCode: true, // Puts the +91 outside the input box for a clean look
            strictMode: true, // MAGIC FEATURE: Automatically prevents typing extra numbers based on the country's actual max length!
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.1/build/js/utils.js" // Loads global phone rules
        });
        
        // Every time the user types, update the hidden field for Laravel
        phoneInput.addEventListener('input', function() {
            // .getNumber() automatically combines the code and number (e.g., +919876543210)
            hiddenInput.value = iti.getNumber();
        });

        // If they select a new country, update the hidden field immediately
        phoneInput.addEventListener('countrychange', function() {
            hiddenInput.value = iti.getNumber();
            phoneInput.value = ''; // Clear the field so they can type the new country's number
        });
    });
</script>
@endsection
