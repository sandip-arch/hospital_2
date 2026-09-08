<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordDetail;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Services\AuditService;

class MedicalRecordController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = MedicalRecord::with(['patient', 'doctor.user', 'details']);

        if ($user->isDoctor() && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->isPatient() && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->orderBy('visit_date', 'desc')->paginate(12)->withQueryString();

        return view('medical-records.index', compact('records'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot create EMR consultation records.');
        }

        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $patients = Patient::orderBy('first_name')->get();

        $selectedPatientId = $request->query('patient_id');
        $selectedAppointmentId = $request->query('appointment_id');
        $selectedDoctorId = $user->isDoctor() && $user->doctor ? $user->doctor->id : $request->query('doctor_id');

        return view('medical-records.create', compact('doctors', 'patients', 'selectedPatientId', 'selectedAppointmentId', 'selectedDoctorId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot create EMR consultation records.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'visit_date' => 'required|date',
            'diagnosis' => 'required|string',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            // Vitals
            'vital_bp' => 'nullable|string|max:50',
            'vital_hr' => 'nullable|string|max:50',
            'vital_spo2' => 'nullable|string|max:50',
            'vital_temp' => 'nullable|string|max:50',
            'vital_resp' => 'nullable|string|max:50',
            'vital_weight' => 'nullable|string|max:50',
        ]);

        $record = MedicalRecord::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'visit_date' => $validated['visit_date'],
            'diagnosis' => $validated['diagnosis'],
            'symptoms' => $validated['symptoms'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // If linked to appointment, mark appointment as completed
        if (!empty($validated['appointment_id'])) {
            Appointment::where('id', $validated['appointment_id'])->update(['status' => 'completed']);
        }

        // Save Vital Signs
        $vitalsMap = [
            'Blood Pressure' => $validated['vital_bp'] ?? null,
            'Heart Rate' => $validated['vital_hr'] ?? null,
            'SpO2' => $validated['vital_spo2'] ?? null,
            'Temperature' => $validated['vital_temp'] ?? null,
            'Respiratory Rate' => $validated['vital_resp'] ?? null,
            'Body Weight' => $validated['vital_weight'] ?? null,
        ];

        foreach ($vitalsMap as $name => $val) {
            if (!empty($val)) {
                MedicalRecordDetail::create([
                    'medical_record_id' => $record->id,
                    'vital_sign_name' => $name,
                    'vital_sign_value' => $val,
                ]);
            }
        }

        AuditService::log('CREATE', 'medical_records', $record->id, "Created EMR consultation record for Patient ID {$validated['patient_id']}");

        return redirect()->route('medical-records.show', $record->id)->with('success', 'Medical consultation note recorded successfully!');
    }

    public function show($id)
    {
        $record = MedicalRecord::with([
            'patient.emergencyContacts',
            'doctor.user',
            'doctor.department',
            'appointment',
            'details',
            'prescriptions.items.medicine',
            'labReports.test',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $record->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to medical record.');
        }

        if ($user->isDoctor() && $user->doctor && $record->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view records of their own consultations.');
        }

        return view('medical-records.show', compact('record'));
    }

    public function print($id)
    {
        $record = MedicalRecord::with([
            'patient',
            'doctor.user',
            'doctor.department',
            'details',
            'prescriptions.items.medicine',
            'labReports.test',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $record->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to medical record.');
        }

        if ($user->isDoctor() && $user->doctor && $record->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view records of their own consultations.');
        }

        return view('medical-records.print', compact('record'));
    }
}
