<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Admission;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\BillingService;
use App\Services\AuditService;

class FacilityController extends Controller
{
    public function bedTracker(Request $request)
    {
        $query = Room::with(['department', 'beds.currentAdmission.patient', 'beds.currentAdmission.doctor.user']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        $rooms = $query->orderBy('room_number')->get();
        $departments = Department::all();
        $patients = Patient::whereDoesntHave('admissions', function ($q) {
            $q->where('status', 'admitted');
        })->orderBy('first_name')->get();
        $doctors = Doctor::with('user')->where('is_available', true)->get();

        $stats = [
            'total_beds' => Bed::count(),
            'available_beds' => Bed::where('status', 'available')->count(),
            'occupied_beds' => Bed::where('status', 'occupied')->count(),
            'cleaning_beds' => Bed::where('status', 'cleaning')->count(),
            'maintenance_beds' => Bed::where('status', 'maintenance')->count(),
        ];

        return view('facilities.bed-tracker', compact('rooms', 'departments', 'patients', 'doctors', 'stats'));
    }

    public function admissions(Request $request)
    {
        $user = Auth::user();
        $query = Admission::with(['patient', 'bed.room.department', 'doctor.user']);

        if ($user->isDoctor() && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->isPatient() && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $admissions = $query->orderBy('admission_date', 'desc')->paginate(12)->withQueryString();

        return view('facilities.admissions', compact('admissions'));
    }

    public function admit(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            if (!$user->patient) {
                return back()->with('error', 'Patient profile not linked to user account.');
            }
            $patientId = $user->patient->id;
        } else {
            $patientId = $request->patient_id;
        }

        $validated = $request->validate([
            'patient_id' => $user->isPatient() ? 'nullable' : 'required|exists:patients,id',
            'bed_id' => 'required|exists:beds,id',
            'doctor_id' => 'required|exists:doctors,id',
            'admission_date' => 'required|date',
            'admission_reason' => 'nullable|string',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return back()->with('error', 'The selected bed is not currently available for admission.');
        }

        $admission = Admission::create([
            'patient_id' => $patientId,
            'bed_id' => $bed->id,
            'doctor_id' => $validated['doctor_id'],
            'admission_date' => $validated['admission_date'],
            'admission_reason' => $validated['admission_reason'] ?? null,
            'status' => 'admitted',
        ]);

        $bed->update(['status' => 'occupied']);

        AuditService::log('ADMIT', 'admissions', $admission->id, "Admitted Patient ID {$patientId} to Bed #{$bed->bed_number} (Room {$bed->room->room_number})");

        return back()->with('success', $user->isPatient() ? "Admission request submitted for Bed {$bed->bed_number} in Room {$bed->room->room_number}!" : "Patient admitted successfully to Bed {$bed->bed_number}!");
    }

    public function discharge(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Only medical and nursing staff can discharge patients.');
        }

        $admission = Admission::with('bed.room')->findOrFail($id);

        $validated = $request->validate([
            'discharge_notes' => 'nullable|string',
            'generate_invoice' => 'nullable|boolean',
        ]);

        $admission->update([
            'status' => 'discharged',
            'discharge_date' => now(),
            'discharge_notes' => $validated['discharge_notes'] ?? 'Patient clinically stable for discharge.',
        ]);

        // Set bed to cleaning
        $admission->bed->update(['status' => 'cleaning']);

        // Generate final admission invoice
        if ($request->boolean('generate_invoice', true)) {
            BillingService::createForAdmission($admission);
        }

        AuditService::log('DISCHARGE', 'admissions', $admission->id, "Discharged patient from Admission #{$admission->id}");

        return back()->with('success', 'Patient has been discharged and final billing invoice generated.');
    }

    public function updateBedStatus(Request $request, $bedId)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Only medical and facility staff can update bed status.');
        }

        $bed = Bed::findOrFail($bedId);

        $validated = $request->validate([
            'status' => 'required|in:available,occupied,cleaning,maintenance',
        ]);

        $bed->update(['status' => $validated['status']]);
        AuditService::log('UPDATE', 'beds', $bed->id, "Updated Bed #{$bed->bed_number} status to {$validated['status']}");

        return back()->with('success', "Bed #{$bed->bed_number} status updated to {$validated['status']}.");
    }

    public function storeRoom(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized. Only administrators can configure rooms and beds.');
        }

        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms',
            'room_type' => 'required|in:ICU,Private,Semi-Private,General Ward,Operating Theater',
            'department_id' => 'required|exists:departments,id',
            'daily_rate' => 'required|numeric|min:0',
            'beds_count' => 'required|integer|min:1|max:20',
        ]);

        $room = Room::create([
            'room_number' => $validated['room_number'],
            'room_type' => $validated['room_type'],
            'department_id' => $validated['department_id'],
            'daily_rate' => $validated['daily_rate'],
            'status' => 'available',
        ]);

        for ($i = 1; $i <= $validated['beds_count']; $i++) {
            Bed::create([
                'room_id' => $room->id,
                'bed_number' => "B{$i}",
                'status' => 'available',
            ]);
        }

        AuditService::log('CREATE', 'rooms', $room->id, "Added Room {$room->room_number} with {$validated['beds_count']} beds");

        return back()->with('success', "Room {$room->room_number} with {$validated['beds_count']} beds created successfully.");
    }
}
