<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Patient;
use App\Models\EmergencyContact;
use App\Models\PatientDocument;
use App\Models\User;
use App\Services\AuditService;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            return redirect()->route('dashboard')->with('error', 'Access denied. The global Patient Directory is restricted to medical and administrative staff.');
        }

        $query = Patient::with(['emergencyContacts', 'appointments']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_code', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('blood_type')) {
            $query->where('blood_type', $request->blood_type);
        }

        $patients = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('patients.index', compact('patients'));
    }

    public function create()
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }
        return view('patients.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
            // Emergency Contact
            'contact_name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'contact_phone' => 'required|string|max:20',
            'alt_phone' => 'nullable|string|max:20',
        ]);

        $count = Patient::count() + 1;
        $upi = 'PAT-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $patient = Patient::create([
            'patient_code' => $upi,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'dob' => $validated['dob'],
            'gender' => $validated['gender'],
            'blood_type' => $validated['blood_type'] ?? null,
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
        ]);

        EmergencyContact::create([
            'patient_id' => $patient->id,
            'contact_name' => $validated['contact_name'],
            'relationship' => $validated['relationship'],
            'phone' => $validated['contact_phone'],
            'alt_phone' => $validated['alt_phone'] ?? null,
        ]);

        AuditService::log('CREATE', 'patients', $patient->id, "Registered patient {$patient->full_name} (UPI: {$upi})");

        return redirect()->route('patients.show', $patient->id)->with('success', "Patient {$patient->full_name} registered successfully with UPI {$upi}");
    }

    public function show($id)
    {
        $user = Auth::user();
        $patient = Patient::with([
            'emergencyContacts',
            'documents',
            'appointments.doctor.user',
            'appointments.department',
            'medicalRecords.doctor.user',
            'medicalRecords.details',
            'prescriptions.doctor.user',
            'prescriptions.items.medicine',
            'labReports.test',
            'labReports.doctor.user',
            'admissions.bed.room',
            'admissions.doctor.user',
            'invoices.items',
            'invoices.payments',
        ])->findOrFail($id);

        if ($user->isPatient() && $user->patient?->id !== $patient->id && $patient->user_id !== $user->id) {
            abort(403, 'Unauthorized access to patient profile.');
        }

        return view('patients.show', compact('patient'));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $patient = Patient::with('emergencyContacts')->findOrFail($id);

        if ($user->isPatient() && $user->patient?->id !== $patient->id && $patient->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $patient = Patient::findOrFail($id);

        if ($user->isPatient() && $user->patient?->id !== $patient->id && $patient->user_id !== $user->id) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'dob' => 'required|date',
            'gender' => 'required|in:Male,Female,Other',
            'blood_type' => 'nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string',
        ]);

        $patient->update($validated);
        AuditService::log('UPDATE', 'patients', $patient->id, "Updated demographics for patient {$patient->full_name}");

        return redirect()->route('patients.show', $patient->id)->with('success', 'Patient updated successfully!');
    }

    public function destroy($id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }

        $patient = Patient::findOrFail($id);
        $name = $patient->full_name;
        $patient->delete();

        AuditService::log('DELETE', 'patients', $id, "Deleted patient record {$name}");

        return redirect()->route('patients.index')->with('success', "Patient record {$name} deleted successfully.");
    }

    public function uploadDocument(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);

        $validated = $request->validate([
            'document_type' => 'required|string|max:50',
            'file_name' => 'required|string|max:100',
        ]);

        PatientDocument::create([
            'patient_id' => $patient->id,
            'document_type' => $validated['document_type'],
            'file_name' => $validated['file_name'],
            'file_path' => 'documents/' . uniqid() . '_' . str_replace(' ', '_', $validated['file_name']),
            'uploaded_at' => now(),
        ]);

        AuditService::log('UPLOAD', 'patient_documents', $patient->id, "Uploaded {$validated['document_type']} document for {$patient->full_name}");

        return back()->with('success', 'Patient document uploaded successfully!');
    }
}
