<?php

namespace App\Services\AI\Tools;

use App\Models\Prescription;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Exception;

class GetPrescriptionsTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_prescriptions';
    }

    public function description(): string
    {
        return 'Retrieve electronic prescriptions, itemized medication courses, dosages, administration frequencies, and validity. Patients view only their own prescriptions; doctors view prescriptions for their assigned patients.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (applicable for doctors/staff; discarded for patients to prevent IDOR).',
                ],
                'status' => [
                    'type' => 'STRING',
                    'description' => 'Filter by prescription status: "active", "dispensed", "cancelled", or "all". Default is "all".',
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
            $query = Prescription::with(['doctor.user', 'patient', 'items.medicine']);

            if ($authenticatedUser->isPatient()) {
                // Strict self-isolation: discard any supplied patient_id
                $patient = RelationshipGuard::resolveEnforcedPatient($authenticatedUser, null);
                $query->where('patient_id', $patient->id);
            } elseif ($authenticatedUser->isDoctor()) {
                if (!empty($arguments['patient_id'])) {
                    $patientId = (int) $arguments['patient_id'];
                    // Doctor-Patient Isolation Guard
                    RelationshipGuard::assertDoctorPatientRelationship($authenticatedUser, $patientId);
                    $query->where('patient_id', $patientId);
                } else {
                    // Default to prescriptions issued by this doctor
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

            $prescriptions = $query->orderBy('prescribed_date', 'desc')->take(15)->get();

            if ($prescriptions->isEmpty()) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'message' => 'No prescriptions found matching the query.',
                    'prescriptions' => [],
                ];
            }

            $formatted = $prescriptions->map(function ($rx) {
                return [
                    'prescription_id' => $rx->id,
                    'patient_name' => $rx->patient?->full_name ?? 'Unknown',
                    'doctor_name' => $rx->doctor?->full_name ?? 'Dr. Unknown',
                    'prescribed_date' => $rx->prescribed_date ? $rx->prescribed_date->toDateString() : 'N/A',
                    'status' => $rx->status,
                    'clinical_notes' => $rx->notes,
                    'medications' => $rx->items->map(function ($item) {
                        return [
                            'medicine_name' => $item->medicine?->name ?? 'Medication',
                            'generic_formulation' => $item->medicine?->generic_name ?? '',
                            'dosage' => $item->dosage,
                            'frequency' => $item->frequency,
                            'duration_days' => $item->duration_days . ' days',
                            'quantity' => $item->quantity_prescribed,
                            'instructions' => $item->instructions,
                        ];
                    })->all(),
                ];
            })->all();

            return [
                'status' => 'success',
                'count' => count($formatted),
                'prescriptions' => $formatted,
                'disclaimer' => 'CLINICAL REMINDER: Always adhere to the prescribed dosages and instructions given by your physician. Do not alter medication regimens without professional consultation.',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
