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
use App\Models\Notification;
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

        $availableBeds = Bed::with('room.department')
            ->where('status', 'available')
            ->orderBy('room_id')
            ->orderBy('bed_number')
            ->get();

        return view('facilities.bed-tracker', compact('rooms', 'departments', 'patients', 'doctors', 'stats', 'availableBeds'));
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

        $availableBeds = Bed::with('room.department')
            ->where('status', 'available')
            ->orderBy('room_id')
            ->orderBy('bed_number')
            ->get();

        return view('facilities.admissions', compact('admissions', 'availableBeds'));
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

        $bed = Bed::with('room.department')->findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return back()->with('error', 'The selected bed is not currently available for admission.');
        }

        // Strict Inpatient Admission Rule:
        // Patients can only book Emergency beds by themselves.
        // Doctors, Admins, Superadmins, and Staff can book ICU beds and all other beds for any patient.
        if ($user->isPatient() && !$bed->isEmergency()) {
            return back()->with('error', 'Inpatient Access Restriction: Patients can only book Emergency beds directly. ICU, Private, and General Ward beds must be assigned by an attending physician or hospital administration.');
        }

        // Inpatient Concurrency Rule:
        // If a patient already has a bed booked (by himself or any doctor, admin, or superadmin) other than emergency,
        // the patient cannot book an emergency bed.
        if ($user->isPatient()) {
            $existingNonEmer = $user->currentNonEmergencyAdmission();
            if ($existingNonEmer && $existingNonEmer->bed) {
                $bedDesc = "Bed #{$existingNonEmer->bed->bed_number}" . ($existingNonEmer->bed->room ? " (Room {$existingNonEmer->bed->room->room_number})" : '');
                return back()->with('error', "You already have a bed booked in {$bedDesc}");
            }
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

    public function switchBed(Request $request, $id)
    {
        $user = Auth::user();

        // 1. Resolve admission
        $admission = Admission::with(['patient.user', 'bed.room.department', 'doctor.user'])->findOrFail($id);

        // 2. Validate user authorization
        if (!$user->canSwitchBed($admission)) {
            abort(403, 'Unauthorized. Only the assigned attending doctor, a receptionist, or an administrator can switch beds for this patient.');
        }

        // 3. Ensure admission is currently active
        if ($admission->status !== 'admitted') {
            return back()->with('error', "Cannot switch bed. Admission #ADM-{$admission->id} is {$admission->status}.");
        }

        $validated = $request->validate([
            'target_bed_id' => 'required|exists:beds,id',
            'switch_reason' => 'nullable|string|max:500',
        ]);

        if ((int)$validated['target_bed_id'] === (int)$admission->bed_id) {
            return back()->with('error', 'The destination bed must be different from the patient\'s current bed.');
        }

        $newBed = Bed::with('room.department')->findOrFail($validated['target_bed_id']);
        if ($newBed->status !== 'available') {
            return back()->with('error', "Selected Bed #{$newBed->bed_number} (Room {$newBed->room->room_number}) is not currently available for transfer.");
        }

        $oldBed = $admission->bed;

        // 4. Release old bed to cleaning
        if ($oldBed) {
            $oldBed->update(['status' => 'cleaning']);
        }

        // 5. Occupy new bed
        $newBed->update(['status' => 'occupied']);

        // 6. Update admission record
        $switchNote = "\n[Bed Switch " . now()->format('Y-m-d H:i') . " by {$user->name}]: Transferred from Bed #" . ($oldBed?->bed_number ?? 'N/A') . " (Room " . ($oldBed?->room?->room_number ?? 'N/A') . ") to Bed #{$newBed->bed_number} (Room {$newBed->room->room_number}). " . ($validated['switch_reason'] ? "Reason: {$validated['switch_reason']}" : '');
        $admission->update([
            'bed_id' => $newBed->id,
            'admission_reason' => trim(($admission->admission_reason ?? '') . $switchNote),
        ]);

        // 7. Audit log
        AuditService::log('TRANSFER', 'admissions', $admission->id, "Transferred Patient {$admission->patient->full_name} from Bed #" . ($oldBed?->bed_number ?? 'N/A') . " (Room " . ($oldBed?->room?->room_number ?? 'N/A') . ") to Bed #{$newBed->bed_number} (Room {$newBed->room->room_number})");

        // 8. Dispatch notification to the patient (if user account exists)
        if ($admission->patient && $admission->patient->user) {
            $deptName = $newBed->room->department->name ?? 'General';
            $noteText = !empty($validated['switch_reason']) ? " Note: {$validated['switch_reason']}" : '';
            Notification::create([
                'user_id' => $admission->patient->user->id,
                'title' => 'Inpatient Bed Transfer Update',
                'message' => "Your hospital bed has been switched to Bed #{$newBed->bed_number} in Room {$newBed->room->room_number} ({$newBed->room->room_type}, {$deptName}).{$noteText}",
                'type' => 'system',
                'is_read' => false,
            ]);
        }

        return back()->with('success', "Patient {$admission->patient->full_name} successfully transferred to Bed #{$newBed->bed_number} (Room {$newBed->room->room_number})! Previous bed marked for cleaning.");
    }

    public function discharge(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Only medical and nursing staff can discharge patients.');
        }

        // 1. Resolve admission by Admission ID first
        $admission = Admission::with(['bed.room', 'invoice'])->find($id);

        // 2. If not found by Admission ID, attempt resolution as Bed ID (or via request payload)
        if (!$admission) {
            $targetBedId = $id ?: $request->input('bed_id');
            $bed = Bed::with(['room', 'currentAdmission'])->find($targetBedId);

            if ($bed) {
                $admission = $bed->currentAdmission;

                // Handle case where bed is marked occupied without an active admission record
                if (!$admission && $bed->status === 'occupied') {
                    if (!$user->canReleaseBed($bed)) {
                        abort(403, 'Unauthorized. Only the assigned doctor, a receptionist, or an administrator can release this bed.');
                    }
                    $bed->update(['status' => 'cleaning']);
                    AuditService::log('DISCHARGE', 'beds', $bed->id, "Released occupied Bed #{$bed->bed_number} (Room {$bed->room->room_number}) to cleaning");
                    return back()->with('success', "Bed #{$bed->bed_number} released and marked for cleaning.");
                }
            }
        }

        if (!$admission) {
            return back()->with('error', 'Active inpatient admission record not found.');
        }

        // Strict Role Authorization: Only Superadmin, Admin, Receptionist, or the Assigned Attending Doctor
        if (!$user->canDischargeAdmission($admission)) {
            abort(403, 'Unauthorized. Only the assigned attending doctor (' . ($admission->doctor?->full_name ?? 'Doctor') . '), a receptionist, or an administrator can discharge this patient.');
        }

        // 3. Prevent discharging an already discharged admission (graceful error handling)
        if ($admission->status !== 'admitted') {
            return back()->with('error', "Admission #ADM-{$admission->id} is already {$admission->status} and cannot be discharged again.");
        }

        $validated = $request->validate([
            'discharge_notes' => 'nullable|string',
            'generate_invoice' => 'nullable|boolean',
        ]);

        $admission->update([
            'status' => 'discharged',
            'discharge_date' => now(),
            'discharge_notes' => $validated['discharge_notes'] ?? 'Patient clinically stable for discharge.',
        ]);

        // 4. Set bed to cleaning
        if ($admission->bed) {
            $admission->bed->update(['status' => 'cleaning']);
        }

        // 5. Generate final admission invoice if requested and not yet generated
        if ($request->boolean('generate_invoice', true) && !$admission->invoice) {
            BillingService::createForAdmission($admission);
        }

        AuditService::log('DISCHARGE', 'admissions', $admission->id, "Discharged patient from Admission #{$admission->id}");

        return back()->with('success', 'Patient has been discharged and bed marked for cleaning.');
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
            'room_type' => 'required|in:Emergency,ICU,Private,Semi-Private,General Ward,Operating Theater',
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
