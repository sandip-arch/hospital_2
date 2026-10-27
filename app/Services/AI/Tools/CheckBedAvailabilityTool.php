<?php

namespace App\Services\AI\Tools;

use App\Models\Bed;
use App\Models\Room;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class CheckBedAvailabilityTool implements ToolInterface
{
    public function name(): string
    {
        return 'check_bed_availability';
    }

    public function description(): string
    {
        return 'Check real-time inpatient facilities and hospital bed capacity across room tiers (ICU, Emergency, Private, Semi-Private, General Ward), displaying room numbers, daily charges, and ready-to-admit beds.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'room_type' => [
                    'type' => 'STRING',
                    'description' => 'Optional filter: "icu", "emergency", "private", "semi_private", or "general".',
                ],
                'only_available' => [
                    'type' => 'BOOLEAN',
                    'description' => 'Only return ready-to-occupy beds with "available" status. Defaults to true.',
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
        $query = Bed::with(['room.department', 'currentAdmission.patient']);

        $onlyAvailable = !isset($arguments['only_available']) || $arguments['only_available'];
        if ($onlyAvailable) {
            $query->where('status', 'available');
        }

        if (!empty($arguments['room_type'])) {
            $type = strtolower($arguments['room_type']);
            $query->whereHas('room', function ($q) use ($type) {
                $q->where('room_type', $type);
            });
        }

        $beds = $query->take(30)->get();

        $summaryByTier = [];
        $tiers = ['emergency', 'icu', 'private', 'semi_private', 'general'];
        foreach ($tiers as $tier) {
            $totalInTier = Bed::whereHas('room', fn($q) => $q->where('room_type', $tier))->count();
            $availableInTier = Bed::where('status', 'available')->whereHas('room', fn($q) => $q->where('room_type', $tier))->count();
            $summaryByTier[$tier] = [
                'tier' => ucfirst(str_replace('_', ' ', $tier)),
                'total' => $totalInTier,
                'available' => $availableInTier,
                'occupied' => $totalInTier - $availableInTier,
            ];
        }

        $formatted = $beds->map(function ($bed) {
            return [
                'bed_id' => $bed->id,
                'bed_number' => $bed->bed_number,
                'status' => $bed->status,
                'room_number' => $bed->room?->room_number ?? 'N/A',
                'room_type' => ucfirst(str_replace('_', ' ', $bed->room?->room_type ?? 'General')),
                'is_emergency' => (bool) $bed->isEmergency(),
                'daily_charge' => '$' . number_format($bed->room?->charge_per_day ?? 0, 2),
                'department' => $bed->room?->department?->name ?? 'Inpatient ADT',
            ];
        })->all();

        return [
            'status' => 'success',
            'overall_capacity_matrix' => $summaryByTier,
            'result_beds_count' => count($formatted),
            'beds' => $formatted,
        ];
    }
}
