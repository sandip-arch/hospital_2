<?php

namespace App\Services\AI\Tools;

use App\Models\LabReport;
use App\Models\LabTest;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Carbon\Carbon;
use Exception;

class OrderLabTestTool implements ToolInterface
{
    public function name(): string
    {
        return 'order_lab_test';
    }

    public function description(): string
    {
        return 'Doctor tool: Order a diagnostic laboratory pathology test (e.g., Complete Blood Count, Lipid Panel, Liver Function Test, Renal Function) for an assigned patient.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (must be under this doctor\'s care).',
                ],
                'lab_test_id' => [
                    'type' => 'INTEGER',
                    'description' => 'ID of the diagnostic test from hospital lab catalog.',
                ],
                'clinical_notes' => [
                    'type' => 'STRING',
                    'description' => 'Clinical justification or suspected diagnosis for the test order.',
                ],
            ],
            'required' => ['patient_id', 'lab_test_id'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['doctor', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            $patientId = (int) ($arguments['patient_id'] ?? 0);
            $labTestId = (int) ($arguments['lab_test_id'] ?? 0);

            $patient = Patient::find($patientId);
            if (!$patient) {
                return [
                    'status' => 'error',
                    'message' => "Patient #{$patientId} not found.",
                ];
            }

            // Assert Doctor-Patient Relationship
            RelationshipGuard::assertDoctorPatientRelationship($authenticatedUser, $patientId);

            $labTest = LabTest::find($labTestId);
            if (!$labTest) {
                return [
                    'status' => 'error',
                    'message' => "Diagnostic test with ID #{$labTestId} not found in hospital catalog.",
                ];
            }

            $doctorId = $authenticatedUser->doctor?->id ?? 1;

            $report = LabReport::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctorId,
                'lab_test_id' => $labTest->id,
                'technician_id' => null,
                'status' => 'requested',
                'report_date' => Carbon::now(),
                'result_summary' => null,
            ]);

            if ($patient->user_id) {
                Notification::create([
                    'user_id' => $patient->user_id,
                    'title' => 'Diagnostic Lab Test Ordered',
                    'message' => "Your doctor ordered {$labTest->name} ({$labTest->test_code}). Please visit the diagnostic lab.",
                    'type' => 'lab_result',
                    'is_read' => false,
                ]);
            }

            return [
                'status' => 'success',
                'message' => "Diagnostic test '{$labTest->name}' ordered successfully for patient {$patient->full_name}.",
                'report_id' => $report->id,
                'patient_name' => $patient->full_name,
                'test_name' => $labTest->name,
                'test_code' => $labTest->test_code,
                'standard_charge' => '$' . number_format($labTest->price, 2),
                'order_status' => 'requested',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
