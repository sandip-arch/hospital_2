<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Services\AuditService;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Prescription::with(['patient', 'doctor.user', 'items.medicine']);

        if ($user->isDoctor() && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->isPatient() && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $prescriptions = $query->orderBy('prescribed_date', 'desc')->paginate(12)->withQueryString();

        return view('prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot issue medical prescriptions.');
        }

        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $patients = Patient::orderBy('first_name')->get();
        $medicines = Medicine::where('stock_quantity', '>', 0)->orderBy('name')->get();

        $selectedPatientId = $request->query('patient_id');
        $selectedMedicalRecordId = $request->query('medical_record_id');
        $selectedDoctorId = $user->isDoctor() && $user->doctor ? $user->doctor->id : $request->query('doctor_id');

        return view('prescriptions.create', compact('doctors', 'patients', 'medicines', 'selectedPatientId', 'selectedMedicalRecordId', 'selectedDoctorId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot issue medical prescriptions.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'prescribed_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.dosage' => 'required|string|max:50',
            'items.*.frequency' => 'required|string|max:50',
            'items.*.duration_days' => 'required|integer|min:1',
            'items.*.quantity_prescribed' => 'required|integer|min:1',
            'items.*.instructions' => 'nullable|string',
        ]);

        $prescription = Prescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'prescribed_date' => $validated['prescribed_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'active',
        ]);

        foreach ($validated['items'] as $item) {
            PrescriptionItem::create([
                'prescription_id' => $prescription->id,
                'medicine_id' => $item['medicine_id'],
                'dosage' => $item['dosage'],
                'frequency' => $item['frequency'],
                'duration_days' => $item['duration_days'],
                'quantity_prescribed' => $item['quantity_prescribed'],
                'instructions' => $item['instructions'] ?? null,
            ]);
        }

        AuditService::log('CREATE', 'prescriptions', $prescription->id, "Issued prescription #{$prescription->id} for Patient ID {$validated['patient_id']}");

        return redirect()->route('prescriptions.show', $prescription->id)->with('success', 'Prescription created successfully!');
    }

    public function show($id)
    {
        $prescription = Prescription::with([
            'patient',
            'doctor.user',
            'doctor.department',
            'items.medicine',
            'medicalRecord',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $prescription->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to prescription.');
        }

        if ($user->isDoctor() && $user->doctor && $prescription->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view prescriptions of their assigned patients.');
        }

        return view('prescriptions.show', compact('prescription'));
    }

    public function dispense($id)
    {
        $user = Auth::user();
        if ($user->isPatient() || $user->isDoctor()) {
            abort(403, 'Unauthorized. Only pharmacy staff can dispense medication.');
        }

        $prescription = Prescription::with('items.medicine')->findOrFail($id);

        if ($prescription->status === 'dispensed') {
            return back()->with('info', 'This prescription has already been dispensed.');
        }

        // Decrement stock for each item
        foreach ($prescription->items as $item) {
            if ($item->medicine) {
                $newStock = max(0, $item->medicine->stock_quantity - $item->quantity_prescribed);
                $item->medicine->update(['stock_quantity' => $newStock]);
            }
        }

        $prescription->update(['status' => 'dispensed']);
        AuditService::log('DISPENSE', 'prescriptions', $prescription->id, "Dispensed medication for Prescription #{$prescription->id}");

        return back()->with('success', 'Medications dispensed successfully and inventory stock updated!');
    }

    public function print($id)
    {
        $prescription = Prescription::with([
            'patient',
            'doctor.user',
            'doctor.department',
            'items.medicine',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $prescription->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to prescription.');
        }

        if ($user->isDoctor() && $user->doctor && $prescription->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view prescriptions of their assigned patients.');
        }

        return view('prescriptions.print', compact('prescription'));
    }
}
