<?php

namespace App\Services\AI\Tools;

use App\Models\LabReport;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Exception;

class GetLabReportsTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_lab_reports';
    }

    public function description(): string
    {
        return 'Retrieve pathology diagnostic laboratory reports, test statuses, diagnostic values, and findings. Patients view only their own reports; doctors view reports for their assigned patients.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (applicable for clinicians; discarded for patients to prevent IDOR).',
                ],
                'status' => [
                    'type' => 'STRING',
                    'description' => 'Filter by report status: "requested", "in_progress", "completed", or "all". Default is "all".',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            $query = LabReport::with(['test', 'patient', 'doctor.user', 'technician']);

            if ($authenticatedUser->isPatient()) {
                $patient = RelationshipGuard::resolveEnforcedPatient($authenticatedUser, null);
                $query->where('patient_id', $patient->id);
            } elseif ($authenticatedUser->isDoctor()) {
                if (!empty($arguments['patient_id'])) {
                    $patientId = (int) $arguments['patient_id'];
                    RelationshipGuard::assertDoctorPatientRelationship($authenticatedUser, $patientId);
                    $query->where('patient_id', $patientId);
                } else {
                    $doctor = $authenticatedUser->doctor;
                    if ($doctor) {
                        $query->where('doctor_id', $doctor->id);
                    }
                }
            } elseif (!empty($arguments['patient_id'])) {
                $query->where('patient_id', (int) $arguments['patient_id']);
            }

            $status = strtolower($arguments['status'] ?? 'all');
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $reports = $query->orderBy('report_date', 'desc')->take(15)->get();

            if ($reports->isEmpty()) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'message' => 'No laboratory diagnostic reports found.',
                    'reports' => [],
                ];
            }

            $formatted = $reports->map(function ($rep) {
                return [
                    'report_id' => $rep->id,
                    'test_name' => $rep->test?->name ?? 'Diagnostic Test',
                    'test_code' => $rep->test?->test_code ?? 'LAB',
                    'category' => $rep->test?->category ?? 'Pathology',
                    'status' => $rep->status,
                    'patient_name' => $rep->patient?->full_name ?? 'Patient',
                    'requesting_doctor' => $rep->doctor?->full_name ?? 'Dr. Assigned',
                    'report_date' => $rep->report_date ? $rep->report_date->toFormattedDateString() : 'Pending',
                    'results_summary' => $rep->result_summary ?? 'Awaiting pathology analysis / in progress.',
                    'normal_range' => $rep->test?->normal_range,
                    'unit' => $rep->test?->unit,
                ];
            })->all();

            return [
                'status' => 'success',
                'count' => count($formatted),
                'reports' => $formatted,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
