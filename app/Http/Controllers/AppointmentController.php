<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\BillingService;
use App\Services\AuditService;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with(['patient', 'doctor.user', 'department']);

        if ($user->isDoctor() && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->isPatient() && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->orderBy('time_slot')->paginate(15)->withQueryString();
        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $departments = Department::all();

        return view('appointments.index', compact('appointments', 'doctors', 'departments'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        // Auto-detect patient from query, user relation, email, or create on-the-fly
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
        }

        if (!$patient) {
            $patient = $user->patient;
        }

        if (!$patient) {
            $patient = Patient::where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->first();
        }

        if (!$patient) {
            $nameParts = explode(' ', trim($user->name));
            $firstName = $nameParts[0] ?? 'User';
            $lastName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Patient';

            $patient = Patient::create([
                'user_id' => $user->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $user->email,
                'phone' => 'N/A',
                'gender' => 'other',
                'date_of_birth' => now()->subYears(25)->toDateString(),
            ]);
        }

        $doctors = Doctor::with(['user', 'department', 'availabilities'])->where('is_available', true)->get();
        $departments = Department::all();
        $selectedDoctorId = $request->query('doctor_id');

        return view('appointments.create', compact('patient', 'doctors', 'departments', 'selectedDoctorId'));
    }

    public function getAvailableSlots(Request $request)
    {
        $doctorId = (int) $request->query('doctor_id');
        $dateStr = $request->query('date');

        if (!$doctorId || !$dateStr) {
            return response()->json([
                'status' => 'error',
                'message' => 'Both doctor_id and date are required.',
            ], 400);
        }

        try {
            $targetDate = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid date format.',
            ], 400);
        }

        $doctor = Doctor::with(['availabilities'])->find($doctorId);
        if (!$doctor) {
            return response()->json([
                'status' => 'error',
                'message' => 'Doctor not found.',
            ], 404);
        }

        $dayOfWeek = $targetDate->format('l');
        $avail = $doctor->availabilities->firstWhere('day_of_week', $dayOfWeek);

        if (!$avail || !$avail->is_available) {
            return response()->json([
                'status' => 'success',
                'is_available' => false,
                'message' => "{$doctor->full_name} is not on duty on {$dayOfWeek}s.",
                'slots' => [],
            ]);
        }

        $startTimeStr = $avail->start_time ?? '09:00:00';
        $endTimeStr = $avail->end_time ?? '17:00:00';

        // Query booked slots for this doctor on this date
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $targetDate->toDateString())
            ->whereIn('status', ['scheduled', 'completed'])
            ->pluck('time_slot')
            ->map(fn($s) => trim($s))
            ->toArray();

        $start = Carbon::parse($targetDate->toDateString() . ' ' . $startTimeStr);
        $end = Carbon::parse($targetDate->toDateString() . ' ' . $endTimeStr);

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->copy()->addMinutes(30)->lte($end)) {
            $slotEnd = $cursor->copy()->addMinutes(30);
            $slotLabel = $cursor->format('h:i A') . ' - ' . $slotEnd->format('h:i A');
            $slot24 = $cursor->format('H:i') . ' - ' . $slotEnd->format('H:i');
            $legacyLabel = $cursor->format('h:i A');

            $isBooked = in_array($slotLabel, $bookedSlots) || in_array($slot24, $bookedSlots) || in_array($legacyLabel, $bookedSlots);

            $slots[] = [
                'time_slot' => $slotLabel,
                'is_available' => !$isBooked,
                'status' => $isBooked ? 'booked' : 'available',
            ];

            $cursor->addMinutes(30);
        }

        return response()->json([
            'status' => 'success',
            'is_available' => true,
            'day_of_week' => $dayOfWeek,
            'duty_hours' => Carbon::parse($startTimeStr)->format('h:i A') . ' - ' . Carbon::parse($endTimeStr)->format('h:i A'),
            'slots' => $slots,
            'available_count' => count(array_filter($slots, fn($s) => $s['is_available'])),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Auto-detect patient from request or Auth
        $patientId = null;
        if ($request->filled('patient_id')) {
            $patientId = $request->patient_id;
        } elseif ($user->patient) {
            $patientId = $user->patient->id;
        } else {
            $patient = Patient::where('user_id', $user->id)->orWhere('email', $user->email)->first();
            if (!$patient) {
                $nameParts = explode(' ', trim($user->name));
                $patient = Patient::create([
                    'user_id' => $user->id,
                    'first_name' => $nameParts[0] ?? 'User',
                    'last_name' => count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : 'Patient',
                    'email' => $user->email,
                    'phone' => 'N/A',
                    'gender' => 'other',
                    'date_of_birth' => now()->subYears(25)->toDateString(),
                ]);
            }
            $patientId = $patient->id;
        }

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        // Validate doctor availability schedule
        $doctor = Doctor::with('availabilities')->findOrFail($validated['doctor_id']);
        $dayOfWeek = Carbon::parse($validated['appointment_date'])->format('l');
        $avail = $doctor->availabilities->firstWhere('day_of_week', $dayOfWeek);

        if (!$avail || !$avail->is_available) {
            return back()->withInput()->with('error', "{$doctor->full_name} is not on duty on {$dayOfWeek}s. Please choose an available date.");
        }

        // Validate slot is not already booked
        $isBooked = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('time_slot', $validated['time_slot'])
            ->whereIn('status', ['scheduled', 'completed'])
            ->exists();

        if ($isBooked) {
            return back()->withInput()->with('error', "The time slot '{$validated['time_slot']}' on {$validated['appointment_date']} is already booked. Please choose another slot.");
        }

        $appointment = Appointment::create([
            'patient_id' => $patientId,
            'doctor_id' => $validated['doctor_id'],
            'department_id' => $validated['department_id'],
            'appointment_date' => $validated['appointment_date'],
            'time_slot' => $validated['time_slot'],
            'status' => 'scheduled',
            'reason' => $validated['reason'] ?? null,
        ]);

        // Auto-generate consultation invoice
        if ($request->boolean('generate_invoice', true)) {
            BillingService::createForAppointment($appointment);
        }

        AuditService::log('CREATE', 'appointments', $appointment->id, "Booked appointment for Patient ID {$patientId} with Doctor ID {$validated['doctor_id']}");

        return redirect()->route('appointments.index')->with('success', 'Appointment booked successfully!');
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'patient',
            'doctor.user',
            'doctor.department',
            'department',
            'medicalRecord.details',
            'medicalRecord.prescriptions.items.medicine',
            'invoice.items',
            'invoice.payments',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $appointment->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to appointment details.');
        }

        if ($user->isDoctor() && $user->doctor && $appointment->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view their own consultations.');
        }

        return view('appointments.show', compact('appointment'));
    }

    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Only medical and administrative staff can update appointment clinical status.');
        }

        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'doctor_notes' => 'nullable|string',
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'doctor_notes' => $validated['doctor_notes'] ?? $appointment->doctor_notes,
        ]);

        AuditService::log('STATUS_CHANGE', 'appointments', $appointment->id, "Updated appointment #{$appointment->id} status to {$validated['status']}");

        return back()->with('success', "Appointment status updated to {$validated['status']}.");
    }

    public function cancel($id)
    {
        $user = Auth::user();
        $appointment = Appointment::findOrFail($id);

        if ($user->isPatient() && $appointment->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized.');
        }

        $appointment->update(['status' => 'cancelled']);

        AuditService::log('CANCEL', 'appointments', $appointment->id, "Cancelled appointment #{$appointment->id}");

        return back()->with('success', 'Appointment has been cancelled.');
    }
}
