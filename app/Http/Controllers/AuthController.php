<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Patient;
use App\Services\AuditService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $credentials['login'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->withErrors(['login' => "Your account is {$user->status}. Please contact administration."]);
            }

            $request->session()->regenerate();
            AuditService::log('LOGIN', 'users', $user->id, "User {$user->email} logged in successfully");

            return redirect()->intended(route('dashboard'))->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->onlyInput('login');
    }

    /**
     * One-Click Quick Demo Login for instant evaluation
     */
    public function demoLogin(string $role)
    {
        $email = match ($role) {
            'superadmin' => 'superadmin@hospital.test',
            'admin' => 'admin@hospital.test',
            'doctor' => 'dr.sarah@hospital.test',
            'receptionist' => 'receptionist@hospital.test',
            'nurse' => 'nurse@hospital.test',
            'pharmacist' => 'pharmacist@hospital.test',
            'labtech' => 'labtech@hospital.test',
            'cashier' => 'cashier@hospital.test',
            'patient' => 'patient.john@hospital.test',
            default => 'superadmin@hospital.test',
        };

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            AuditService::log('LOGIN', 'users', $user->id, "Demo 1-Click login as {$role}");
            return redirect()->route('dashboard')->with('success', "Logged in as {$user->name} ({$role})");
        }

        return redirect()->route('login')->with('error', "Demo account for {$role} not found.");
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:100|unique:users',
            'phone' => 'required|string|max:20',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $validated['first_name'] = ucwords(strtolower(trim($validated['first_name'])));
        $validated['last_name'] = ucwords(strtolower(trim($validated['last_name'])));

        $username = 'pat_' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $validated['first_name'])) . rand(100, 999);

        $user = User::create([
            'name' => "{$validated['first_name']} {$validated['last_name']}",
            'username' => $username,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $patientRole = Role::where('name', 'patient')->first();
        if ($patientRole) {
            $user->roles()->sync([$patientRole->id]);
        }

        // Check if an existing patient record exists with this email or phone
        $existingPatient = Patient::where('email', $validated['email'])
            ->orWhere('phone', $validated['phone'])
            ->first();

        if ($existingPatient) {
            $existingPatient->update([
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'blood_type' => $validated['blood_type'] ?? $existingPatient->blood_type,
                'address' => $validated['address'] ?? $existingPatient->address,
            ]);
            $upi = $existingPatient->patient_code;
        } else {
            // Generate UPI
            $count = Patient::count() + 1;
            $upi = 'PAT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            Patient::create([
                'user_id' => $user->id,
                'patient_code' => $upi,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'dob' => $validated['dob'],
                'gender' => $validated['gender'],
                'blood_type' => $validated['blood_type'] ?? null,
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'address' => $validated['address'] ?? null,
            ]);
        }

        AuditService::log('REGISTER', 'users', $user->id, "Patient account {$user->name} linked to UPI {$upi}");

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration successful! Welcome to your patient portal.');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditService::log('LOGOUT', 'users', Auth::id(), 'User logged out');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }

    public function profile()
    {
        $user = Auth::user();

        // Resolve demographic details from patient profile or fallback to user/related models
        $patient = $user->patient;
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $patient->first_name ?? $nameParts[0] ?? '';
        $lastName = $patient->last_name ?? $nameParts[1] ?? '';
        $dob = $patient && $patient->dob ? $patient->dob->format('Y-m-d') : '';
        $gender = $patient->gender ?? '';
        $bloodType = $patient->blood_type ?? '';
        $phone = $patient->phone ?? $user->doctor->phone ?? $user->staff->phone ?? $user->ambulanceDriver->phone ?? '';
        $address = $patient->address ?? '';

        return view('auth.profile', compact(
            'user',
            'firstName',
            'lastName',
            'dob',
            'gender',
            'bloodType',
            'phone',
            'address'
        ));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Check if user is attempting to change password: if any of the 3 fields is provided, all 3 are required
        $isChangingPassword = $request->filled('current_password') 
                           || $request->filled('new_password') 
                           || $request->filled('new_password_confirmation');

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:50',
            'last_name' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female,Other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-,not known',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'current_password' => $isChangingPassword ? 'required|string' : 'nullable',
            'new_password' => $isChangingPassword ? 'required|string|min:6|confirmed' : 'nullable',
            'new_password_confirmation' => $isChangingPassword ? 'required|string' : 'nullable',
        ], [
            'current_password.required' => 'To change your password, you must enter your current password.',
            'new_password.required' => 'Please provide your new password (minimum 6 characters).',
            'new_password_confirmation.required' => 'Please confirm your new password.',
            'new_password.confirmed' => 'New password confirmation does not match.',
        ]);

        $firstName = !empty($validated['first_name']) ? ucwords(strtolower(trim($validated['first_name']))) : null;
        $lastName = !empty($validated['last_name']) ? ucwords(strtolower(trim($validated['last_name']))) : null;

        if ($firstName || $lastName) {
            $user->name = trim("{$firstName} {$lastName}");
        }

        // Update Patient profile if exists or create if user has patient role
        $patient = $user->patient;
        if ($patient) {
            $patientUpdate = [];
            if ($firstName !== null) $patientUpdate['first_name'] = $firstName;
            if ($lastName !== null) $patientUpdate['last_name'] = $lastName;
            if (array_key_exists('dob', $validated) && $validated['dob'] !== null) $patientUpdate['dob'] = $validated['dob'];
            if (!empty($validated['gender'])) $patientUpdate['gender'] = $validated['gender'];
            if (array_key_exists('blood_type', $validated)) $patientUpdate['blood_type'] = $validated['blood_type'];
            if (!empty($validated['phone'])) $patientUpdate['phone'] = $validated['phone'];
            if (array_key_exists('address', $validated)) $patientUpdate['address'] = $validated['address'];

            if (!empty($patientUpdate)) {
                $patient->update($patientUpdate);
            }
        } elseif ($user->hasRole('patient') && ($firstName || $lastName)) {
            $count = Patient::count() + 1;
            $upi = 'PAT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            Patient::create([
                'user_id' => $user->id,
                'patient_code' => $upi,
                'first_name' => $firstName ?? 'Patient',
                'last_name' => $lastName ?? 'User',
                'dob' => $validated['dob'] ?? '2000-01-01',
                'gender' => $validated['gender'] ?? 'Other',
                'blood_type' => $validated['blood_type'] ?? null,
                'phone' => $validated['phone'] ?? '+1 0000000000',
                'email' => $user->email,
                'address' => $validated['address'] ?? null,
            ]);
        }

        // Update phone on role-specific records if applicable
        if (!empty($validated['phone'])) {
            if ($user->doctor) {
                $user->doctor->update(['phone' => $validated['phone']]);
            }
            if ($user->staff) {
                $user->staff->update(['phone' => $validated['phone']]);
            }
            if ($user->ambulanceDriver) {
                $user->ambulanceDriver->update(['phone' => $validated['phone']]);
            }
        }

        // Handle password update if requested
        if ($isChangingPassword) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match our records.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();
        AuditService::log('UPDATE', 'users', $user->id, 'Updated user profile information');

        return back()->with('success', 'Profile updated successfully!');
    }
}
