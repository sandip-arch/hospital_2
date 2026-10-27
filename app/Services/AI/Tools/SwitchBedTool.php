<?php

namespace App\Services\AI\Tools;

use App\Models\Admission;
use App\Models\Bed;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Illuminate\Support\Facades\DB;
use Exception;

class SwitchBedTool implements ToolInterface
{
    public function name(): string
    {
        return 'switch_bed';
    }

    public function description(): string
    {
        return 'Inpatient ADT Action: Transfer an admitted patient to a different available hospital bed. Restricted to attending physicians, receptionists, and hospital administrators.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'admission_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Active admission ID to transfer.',
                ],
                'new_bed_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target new available bed ID.',
                ],
            ],
            'required' => ['admission_id', 'new_bed_id'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $admissionId = (int) ($arguments['admission_id'] ?? 0);
        $newBedId = (int) ($arguments['new_bed_id'] ?? 0);

        $admission = Admission::with(['patient', 'bed.room'])->find($admissionId);
        if (!$admission || $admission->status !== 'admitted') {
            return [
                'status' => 'error',
                'message' => "Active admission #{$admissionId} not found.",
            ];
        }

        if (!$authenticatedUser->canSwitchBed($admission)) {
            return [
                'status' => 'error',
                'message' => 'Access Denied: You do not have authorization to transfer this patient.',
            ];
        }

        $newBed = Bed::with('room')->find($newBedId);
        if (!$newBed || $newBed->status !== 'available') {
            return [
                'status' => 'error',
                'message' => "Target bed #{$newBedId} is not available.",
            ];
        }

        $oldBed = $admission->bed;

        DB::transaction(function () use ($admission, $oldBed, $newBed) {
            if ($oldBed) {
                $oldBed->update(['status' => 'cleaning']);
            }
            $newBed->update(['status' => 'occupied']);
            $admission->update(['bed_id' => $newBed->id]);
        });

        return [
            'status' => 'success',
            'message' => "Patient {$admission->patient?->full_name} transferred successfully from Bed {$oldBed?->bed_number} to Bed {$newBed->bed_number}.",
            'admission_id' => $admission->id,
            'previous_bed' => $oldBed?->bed_number ?? 'N/A',
            'new_bed' => $newBed->bed_number,
            'new_room' => $newBed->room?->room_number,
            'new_daily_charge' => '$' . number_format($newBed->room?->charge_per_day ?? 0, 2),
        ];
    }
}
