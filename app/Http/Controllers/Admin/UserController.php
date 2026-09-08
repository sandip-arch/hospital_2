<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Staff;
use App\Services\AuditService;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'doctor.department', 'staff.department', 'patient']);

        if ($request->filled('role')) {
            $role = $request->role;
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,suspended',
            // Doctor fields
            'specialization' => 'nullable|string|max:100',
            'license_number' => 'nullable|string|max:50|unique:doctors,license_number',
            'consultation_fee' => 'nullable|numeric|min:0',
            'doctor_phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id',
            // Staff fields
            'job_title' => 'nullable|string|max:100',
            'staff_phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => $validated['status'],
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $user->roles()->sync([$role->id]);

        if ($role->name === 'doctor') {
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'] ?? null,
                'specialization' => $validated['specialization'] ?? 'General Physician',
                'license_number' => $validated['license_number'] ?? ('LIC-' . uniqid()),
                'consultation_fee' => $validated['consultation_fee'] ?? 50.00,
                'phone' => $validated['doctor_phone'] ?? '+1 (555) 000-0000',
                'is_available' => true,
            ]);

            // Default weekday availability
            foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'] as $day) {
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '17:00:00',
                    'is_available' => true,
                ]);
            }
        } elseif ($role->name === 'staff') {
            Staff::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'] ?? null,
                'job_title' => $validated['job_title'] ?? 'Staff Member',
                'phone' => $validated['staff_phone'] ?? '+1 (555) 000-0000',
                'hire_date' => now()->toDateString(),
            ]);
        }

        AuditService::log('CREATE', 'users', $user->id, "Admin created user {$user->name} with role {$role->name}");

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} created successfully with role {$role->display_name}.");
    }

    public function edit($id)
    {
        $user = User::with(['roles', 'doctor', 'staff'])->findOrFail($id);
        $roles = Role::all();
        $departments = Department::all();
        return view('admin.users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|in:active,inactive,suspended',
            'password' => 'nullable|min:6',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->status = $validated['status'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $user->roles()->sync([$validated['role_id']]);

        AuditService::log('UPDATE', 'users', $user->id, "Updated user account {$user->name}");

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} updated successfully.");
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        AuditService::log('STATUS_CHANGE', 'users', $user->id, "Changed status of {$user->name} to {$newStatus}");

        return back()->with('success', "User account {$user->name} is now {$newStatus}.");
    }
}
