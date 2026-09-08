<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use App\Services\AuditService;

class PublicController extends Controller
{
    public function home()
    {
        $departments = Department::withCount('doctors')->get();
        $doctors = Doctor::with(['user', 'department'])->where('is_available', true)->take(4)->get();
        $stats = [
            'departments_count' => Department::count(),
            'doctors_count' => Doctor::count(),
            'beds_count' => Room::sum('daily_rate') > 0 ? \App\Models\Bed::count() : 50,
            'patients_count' => Patient::count(),
        ];

        return view('public.home', compact('departments', 'doctors', 'stats'));
    }

    public function doctors(Request $request)
    {
        $departments = Department::all();
        $query = Doctor::with(['user', 'department', 'availabilities'])->where('is_available', true);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('specialization', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $doctors = $query->paginate(8)->withQueryString();

        return view('public.doctors', compact('doctors', 'departments'));
    }

    public function departments()
    {
        $departments = Department::with(['doctors.user', 'rooms'])->get();
        return view('public.departments', compact('departments'));
    }

    public function bookRequest(Request $request)
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        // Find or create patient
        $nameParts = explode(' ', $validated['patient_name'], 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'Walk-in';

        $patient = null;
        if (!empty($validated['email'])) {
            $patient = Patient::where('email', $validated['email'])->first();
        }

        if (!$patient) {
            $count = Patient::count() + 1;
            $upi = 'PAT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $patient = Patient::create([
                'patient_code' => $upi,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'dob' => '1990-01-01',
                'gender' => 'Other',
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? null,
                'address' => 'Online Request',
            ]);
        }

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $validated['doctor_id'],
            'department_id' => $validated['department_id'],
            'appointment_date' => $validated['appointment_date'],
            'time_slot' => $validated['time_slot'],
            'status' => 'scheduled',
            'reason' => $validated['reason'] ?? 'Online public booking request',
        ]);

        AuditService::log('BOOK', 'appointments', $appointment->id, "Public booking by {$patient->full_name} for Doctor ID {$validated['doctor_id']}");

        return redirect()->route('public.home')->with('success', "Appointment scheduled successfully! Your UPI is {$patient->patient_code}. Our team will contact you shortly.");
    }
}
