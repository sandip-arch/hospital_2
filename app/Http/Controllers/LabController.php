<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\LabTest;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Services\AuditService;

class LabController extends Controller
{
    public function index(Request $request)
    {
        $tests = LabTest::withCount('reports')->orderBy('test_name')->paginate(12);
        return view('lab.catalog', compact('tests'));
    }

    public function requests(Request $request)
    {
        $user = Auth::user();
        $query = LabReport::with(['patient', 'test', 'doctor.user', 'technician']);

        if ($user->isDoctor() && $user->doctor) {
            $query->where('doctor_id', $user->doctor->id);
        } elseif ($user->isPatient() && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();
        $tests = LabTest::all();
        $doctors = Doctor::with('user')->where('is_available', true)->get();
        $patients = Patient::orderBy('first_name')->get();

        return view('lab.requests', compact('reports', 'tests', 'doctors', 'patients'));
    }

    public function order(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Laboratory tests must be ordered by medical staff.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'doctor_id' => 'required|exists:doctors,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
        ]);

        $report = LabReport::create([
            'patient_id' => $validated['patient_id'],
            'lab_test_id' => $validated['lab_test_id'],
            'doctor_id' => $validated['doctor_id'],
            'medical_record_id' => $validated['medical_record_id'] ?? null,
            'status' => 'requested',
        ]);

        AuditService::log('ORDER', 'lab_reports', $report->id, "Ordered lab test ID {$validated['lab_test_id']} for Patient ID {$validated['patient_id']}");

        return back()->with('success', 'Diagnostic lab test ordered successfully!');
    }

    public function storeResult(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }

        $report = LabReport::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'result_summary' => 'required|string',
        ]);

        $report->update([
            'status' => $validated['status'],
            'result_summary' => $validated['result_summary'],
            'technician_id' => Auth::id(),
            'report_date' => $validated['status'] === 'completed' ? now() : null,
        ]);

        AuditService::log('UPDATE', 'lab_reports', $report->id, "Updated lab report #{$report->id} result (Status: {$validated['status']})");

        return back()->with('success', 'Lab report results updated successfully!');
    }

    public function showReport($id)
    {
        $report = LabReport::with([
            'patient',
            'test',
            'doctor.user',
            'doctor.department',
            'technician',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $report->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to lab report.');
        }

        if ($user->isDoctor() && $user->doctor && $report->doctor_id !== $user->doctor->id) {
            abort(403, 'Unauthorized. Doctors can only view lab reports of their assigned patients.');
        }

        return view('lab.report', compact('report'));
    }

    public function storeTest(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'test_name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:lab_tests',
            'cost' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        LabTest::create($validated);
        AuditService::log('CREATE', 'lab_tests', null, "Added new lab test {$validated['test_name']}");

        return back()->with('success', 'New diagnostic test added to catalog.');
    }
}
