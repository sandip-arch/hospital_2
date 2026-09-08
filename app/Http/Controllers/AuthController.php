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

        AuditService::log('REGISTER', 'users', $user->id, "New patient {$user->name} registered with UPI {$upi}");

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
        return view('auth.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['new_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password does not match.']);
            }
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();
        AuditService::log('UPDATE', 'users', $user->id, 'Updated user profile information');

        return back()->with('success', 'Profile updated successfully!');
    }
}
