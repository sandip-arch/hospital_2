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
        $patients = Patient::orderBy('first_name')->get();
        $doctors = Doctor::with(['user', 'department', 'availabilities'])->where('is_available', true)->get();
        $departments = Department::all();
        $selectedPatientId = $request->query('patient_id');

        return view('appointments.create', compact('patients', 'doctors', 'departments', 'selectedPatientId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ];

        if ($user->isPatient() && $user->patient) {
            $patientId = $user->patient->id;
        } else {
            $rules['patient_id'] = 'required|exists:patients,id';
            $patientId = $request->patient_id;
        }

        $validated = $request->validate($rules);

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
