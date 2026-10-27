<?php

namespace App\Services\AI\Tools;

use App\Models\MedicalRecord;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Exception;

class GetPatientMedicalRecordsTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_patient_medical_records';
    }

    public function description(): string
    {
        return 'Retrieve electronic medical records (EMR), historical clinical encounter summaries, diagnoses, presenting symptoms, treatment plans, and measured vital signs. Strictly governed by the Doctor-Patient Isolation Guard.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (required for doctors; discarded for patients to enforce zero-trust self-isolation).',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['doctor', 'patient', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            if ($authenticatedUser->isPatient()) {
                // Self-isolation: Force-bind authenticated user's patient profile
                $patient = RelationshipGuard::resolveEnforcedPatient($authenticatedUser, null);
                $patientId = $patient->id;
            } elseif ($authenticatedUser->isDoctor()) {
                $patientId = (int) ($arguments['patient_id'] ?? 0);
                if (!$patientId) {
                    return [
                        'status' => 'error',
                        'message' => 'Please specify the patient_id to view medical records.',
                    ];
                }
                // Assert Doctor-Patient Relationship
                RelationshipGuard::assertDoctorPatientRelationship($authenticatedUser, $patientId);
            } else {
                $patientId = (int) ($arguments['patient_id'] ?? 0);
                if (!$patientId) {
                    return [
                        'status' => 'error',
                        'message' => 'Patient ID is required.',
                    ];
                }
            }

            $records = MedicalRecord::with(['doctor.user', 'patient', 'details'])
                ->where('patient_id', $patientId)
                ->orderBy('visit_date', 'desc')
                ->take(10)
                ->get();

            if ($records->isEmpty()) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'message' => 'No historical clinical records found for this patient.',
                    'records' => [],
                ];
            }

            $formatted = $records->map(function ($rec) {
                return [
                    'record_id' => $rec->id,
                    'visit_date' => $rec->visit_date ? $rec->visit_date->toFormattedDateString() : 'N/A',
                    'doctor_name' => $rec->doctor?->full_name ?? 'Attending Physician',
                    'diagnosis' => $rec->diagnosis,
                    'symptoms' => $rec->symptoms,
                    'clinical_notes' => $rec->notes,
                    'vital_signs' => $rec->details->map(function ($det) {
                        return [
                            'sign' => $det->vital_sign_name,
                            'value' => $det->value . ' ' . ($det->unit ?? ''),
                        ];
                    })->all(),
                ];
            })->all();

            return [
                'status' => 'success',
                'patient_id' => $patientId,
                'count' => count($formatted),
                'records' => $formatted,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
