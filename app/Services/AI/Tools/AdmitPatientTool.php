<?php

namespace App\Services\AI\Tools;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AdmitPatientTool implements ToolInterface
{
    public function name(): string
    {
        return 'admit_patient';
    }

    public function description(): string
    {
        return 'Inpatient ADT Action: Admit a patient to an available hospital bed, assign an attending physician, and initiate inpatient stay care.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'bed_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target available bed ID.',
                ],
                'doctor_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Attending physician ID (defaults to primary doctor or on-duty physician).',
                ],
                'admission_reason' => [
                    'type' => 'STRING',
                    'description' => 'Clinical reason for admission, symptoms, or primary diagnosis.',
                ],
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Patient ID (required for staff/doctors; discarded for patients to enforce zero-trust self-isolation).',
                ],
            ],
            'required' => ['bed_id', 'admission_reason'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            $bedId = (int) ($arguments['bed_id'] ?? 0);
            $bed = Bed::with('room')->find($bedId);

            if (!$bed) {
                return [
                    'status' => 'error',
                    'message' => "Bed #{$bedId} not found in facility registry.",
                ];
            }

            if ($bed->status !== 'available') {
                return [
                    'status' => 'error',
                    'message' => "Bed {$bed->bed_number} is currently '{$bed->status}' and not available for new admission.",
                ];
            }

            // Zero-Trust Patient Resolution
            $patient = RelationshipGuard::resolveEnforcedPatient(
                $authenticatedUser,
                isset($arguments['patient_id']) ? (int) $arguments['patient_id'] : null
            );

            // Check if patient can book this bed
            if (!$bed->canBeBookedBy($authenticatedUser)) {
                return [
                    'status' => 'error',
                    'message' => 'Policy Restriction: Patients may only directly request admission into designated Emergency beds, provided they do not already hold an active admission.',
                ];
            }

            // Check if patient already has an active admission
            $activeAdmission = Admission::where('patient_id', $patient->id)
                ->where('status', 'admitted')
                ->first();

            if ($activeAdmission) {
                return [
                    'status' => 'error',
                    'message' => "Patient {$patient->full_name} is already admitted in Bed #{$activeAdmission->bed_id}.",
                ];
            }

            // Resolve Attending Doctor
            $doctorId = (int) ($arguments['doctor_id'] ?? 0);
            if (!$doctorId && $authenticatedUser->isDoctor() && $authenticatedUser->doctor) {
                $doctorId = $authenticatedUser->doctor->id;
            } elseif (!$doctorId) {
                $doctor = Doctor::where('is_available', true)->first() ?? Doctor::first();
                $doctorId = $doctor?->id ?? 1;
            }

            $admission = DB::transaction(function () use ($patient, $bed, $doctorId, $arguments) {
                $adm = Admission::create([
                    'patient_id' => $patient->id,
                    'bed_id' => $bed->id,
                    'doctor_id' => $doctorId,
                    'admission_date' => Carbon::now(),
                    'admission_reason' => $arguments['admission_reason'] ?? 'Inpatient Medical Care',
                    'status' => 'admitted',
                ]);

                $bed->update(['status' => 'occupied']);

                // Notify patient
                if ($patient->user_id) {
                    Notification::create([
                        'user_id' => $patient->user_id,
                        'title' => 'Inpatient Admission Registered',
                        'message' => "You have been admitted to Bed {$bed->bed_number} (Room {$bed->room?->room_number}).",
                        'type' => 'system',
                        'is_read' => false,
                    ]);
                }

                return $adm;
            });

            return [
                'status' => 'success',
                'message' => "Patient {$patient->full_name} admitted successfully to Bed {$bed->bed_number}.",
                'admission_id' => $admission->id,
                'patient_name' => $patient->full_name,
                'patient_code' => $patient->patient_code,
                'room_number' => $bed->room?->room_number,
                'bed_number' => $bed->bed_number,
                'room_type' => $bed->room?->room_type,
                'daily_rate' => '$' . number_format($bed->room?->charge_per_day ?? 0, 2),
                'admission_date' => Carbon::now()->toDateTimeString(),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
