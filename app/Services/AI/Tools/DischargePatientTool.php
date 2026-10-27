<?php

namespace App\Services\AI\Tools;

use App\Models\Admission;
use App\Models\Notification;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class DischargePatientTool implements ToolInterface
{
    public function name(): string
    {
        return 'discharge_patient';
    }

    public function description(): string
    {
        return 'Inpatient ADT Action: Process clinical discharge for an admitted patient, release bed to sanitization/cleaning, and record physician discharge summary.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'admission_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Active admission ID to discharge.',
                ],
                'discharge_notes' => [
                    'type' => 'STRING',
                    'description' => 'Clinical discharge summary, post-discharge instructions, and follow-up plan.',
                ],
            ],
            'required' => ['admission_id', 'discharge_notes'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $admissionId = (int) ($arguments['admission_id'] ?? 0);
        $admission = Admission::with(['patient', 'bed.room', 'doctor'])->find($admissionId);

        if (!$admission || $admission->status !== 'admitted') {
            return [
                'status' => 'error',
                'message' => "Active admission #{$admissionId} not found.",
            ];
        }

        if (!$authenticatedUser->canDischargeAdmission($admission)) {
            return [
                'status' => 'error',
                'message' => 'Access Denied: Only attending physicians or authorized front desk administrators can process patient discharge.',
            ];
        }

        $dischargeNotes = $arguments['discharge_notes'] ?? 'Discharged in stable clinical condition.';
        $bed = $admission->bed;

        DB::transaction(function () use ($admission, $bed, $dischargeNotes) {
            $admission->update([
                'status' => 'discharged',
                'discharge_date' => Carbon::now(),
                'discharge_notes' => $dischargeNotes,
            ]);

            if ($bed) {
                $bed->update(['status' => 'cleaning']);
            }

            if ($admission->patient?->user_id) {
                Notification::create([
                    'user_id' => $admission->patient->user_id,
                    'title' => 'Discharge Processed',
                    'message' => 'Your inpatient discharge summary has been recorded. Please settle outstanding invoices at front desk.',
                    'type' => 'system',
                    'is_read' => false,
                ]);
            }
        });

        return [
            'status' => 'success',
            'message' => "Patient {$admission->patient?->full_name} has been discharged successfully.",
            'admission_id' => $admission->id,
            'patient_name' => $admission->patient?->full_name,
            'stay_duration' => $admission->stay_days . ' days',
            'released_bed' => $bed?->bed_number ?? 'N/A',
            'bed_status_now' => 'cleaning',
            'discharge_time' => Carbon::now()->toDateTimeString(),
        ];
    }
}
