<?php

namespace App\Services\AI\Tools;

use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class WritePrescriptionTool implements ToolInterface
{
    public function name(): string
    {
        return 'write_prescription';
    }

    public function description(): string
    {
        return 'Doctor tool: Issue an official signed electronic prescription for an assigned patient, adding itemized medications, dosage strengths, administration frequency, duration, and clinical instructions.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (must be assigned to this doctor\'s care).',
                ],
                'notes' => [
                    'type' => 'STRING',
                    'description' => 'Clinical notes and dietary/lifestyle guidance for the patient.',
                ],
                'medications' => [
                    'type' => 'ARRAY',
                    'description' => 'List of medicines to prescribe.',
                    'items' => [
                        'type' => 'OBJECT',
                        'properties' => [
                            'medicine_id' => [
                                'type' => 'INTEGER',
                                'description' => 'ID of the medicine from formulary.',
                            ],
                            'dosage' => [
                                'type' => 'STRING',
                                'description' => 'Strength e.g. "500mg" or "1 tablet".',
                            ],
                            'frequency' => [
                                'type' => 'STRING',
                                'description' => 'Daily schedule e.g. "Twice daily after meals" or "Once daily at bedtime".',
                            ],
                            'duration_days' => [
                                'type' => 'INTEGER',
                                'description' => 'Total course duration in days (e.g. 5, 7, 14, 30).',
                            ],
                            'quantity' => [
                                'type' => 'INTEGER',
                                'description' => 'Total quantity units to dispense.',
                            ],
                            'instructions' => [
                                'type' => 'STRING',
                                'description' => 'Specific patient instructions (e.g., Take with abundant water).',
                            ],
                        ],
                        'required' => ['medicine_id', 'dosage', 'frequency', 'duration_days'],
                    ],
                ],
            ],
            'required' => ['patient_id', 'medications'],
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
            $patient = Patient::find($patientId);

            if (!$patient) {
                return [
                    'status' => 'error',
                    'message' => "Patient #{$patientId} not found.",
                ];
            }

            // Enforce Doctor-Patient Isolation Guard
            RelationshipGuard::assertDoctorPatientRelationship($authenticatedUser, $patientId);

            $doctor = $authenticatedUser->doctor;
            if (!$doctor && !$authenticatedUser->isAdmin()) {
                return [
                    'status' => 'error',
                    'message' => 'Only verified medical practitioners can author prescriptions.',
                ];
            }

            $doctorId = $doctor ? $doctor->id : 1;
            $medications = $arguments['medications'] ?? [];

            if (empty($medications)) {
                return [
                    'status' => 'error',
                    'message' => 'At least one medication item is required to issue a prescription.',
                ];
            }

            $prescription = DB::transaction(function () use ($patient, $doctorId, $medications, $arguments) {
                $rx = Prescription::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctorId,
                    'medical_record_id' => null,
                    'prescribed_date' => Carbon::now()->toDateString(),
                    'status' => 'active',
                    'notes' => $arguments['notes'] ?? 'Issued via AI Assistant',
                ]);

                foreach ($medications as $item) {
                    $med = Medicine::find($item['medicine_id'] ?? 0);
                    PrescriptionItem::create([
                        'prescription_id' => $rx->id,
                        'medicine_id' => $med?->id ?? (int) ($item['medicine_id'] ?? 1),
                        'dosage' => $item['dosage'] ?? '1 dose',
                        'frequency' => $item['frequency'] ?? 'Daily',
                        'duration_days' => (int) ($item['duration_days'] ?? 7),
                        'quantity_prescribed' => (int) ($item['quantity'] ?? ($item['duration_days'] ?? 7)),
                        'instructions' => $item['instructions'] ?? 'Follow doctor\'s orders.',
                    ]);
                }

                // Notification to patient
                if ($patient->user_id) {
                    Notification::create([
                        'user_id' => $patient->user_id,
                        'title' => 'New Prescription Issued',
                        'message' => "Your doctor has issued a new electronic prescription (#{$rx->id}).",
                        'type' => 'system',
                        'is_read' => false,
                    ]);
                }

                return $rx;
            });

            return [
                'status' => 'success',
                'message' => "Prescription #{$prescription->id} successfully signed and issued to patient {$patient->full_name}.",
                'prescription_id' => $prescription->id,
                'patient_name' => $patient->full_name,
                'patient_code' => $patient->patient_code,
                'items_count' => count($medications),
                'prescribed_date' => Carbon::now()->toDateString(),
                'status_now' => 'active',
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
