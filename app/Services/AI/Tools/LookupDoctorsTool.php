<?php

namespace App\Services\AI\Tools;

use App\Models\Doctor;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class LookupDoctorsTool implements ToolInterface
{
    public function name(): string
    {
        return 'lookup_doctors';
    }

    public function description(): string
    {
        return 'Search hospital physicians by medical specialization, department, name, or availability. Returns doctor names, departments, consultation fees, and profile IDs.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'specialization' => [
                    'type' => 'STRING',
                    'description' => 'Medical field or specialty, e.g., Cardiology, Neurology, Pediatrics, Orthopedics.',
                ],
                'department_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Optional ID of the hospital department.',
                ],
                'search' => [
                    'type' => 'STRING',
                    'description' => 'Doctor name keyword search, e.g. "Taylor", "Wilson", "Jenkins", "Chang".',
                ],
                'only_available' => [
                    'type' => 'BOOLEAN',
                    'description' => 'Filter only doctors marked as currently available.',
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
        $query = Doctor::with(['user', 'department', 'availabilities']);

        if (!empty($arguments['specialization'])) {
            $query->where('specialization', 'like', '%' . $arguments['specialization'] . '%');
        }

        if (!empty($arguments['department_id'])) {
            $query->where('department_id', (int) $arguments['department_id']);
        }

        if (!empty($arguments['search'])) {
            $search = trim($arguments['search']);
            // Remove common prefixes
            $cleanSearch = preg_replace('/^(dr\.?|doctor)\s+/i', '', $search);

            $query->where(function ($sub) use ($search, $cleanSearch) {
                $sub->whereHas('user', function ($q) use ($search, $cleanSearch) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('name', 'like', '%' . $cleanSearch . '%');
                })
                ->orWhere('specialization', 'like', '%' . $search . '%')
                ->orWhereHas('department', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if (isset($arguments['only_available']) && $arguments['only_available']) {
            $query->where('is_available', true);
        }

        $doctors = $query->take(15)->get();

        if ($doctors->isEmpty()) {
            return [
                'status' => 'success',
                'count' => 0,
                'message' => 'No doctors found matching the specified criteria.',
                'doctors' => [],
            ];
        }

        $formatted = $doctors->map(function ($doc) {
            $days = $doc->availabilities->where('is_available', true)->pluck('day_of_week')->values()->all();
            return [
                'doctor_id' => $doc->id,
                'name' => $doc->full_name,
                'department' => $doc->department?->name ?? 'General',
                'specialization' => $doc->specialization,
                'consultation_fee' => '$' . number_format($doc->consultation_fee, 2),
                'fee_numeric' => (float) $doc->consultation_fee,
                'is_available' => (bool) $doc->is_available,
                'working_days' => !empty($days) ? implode(', ', $days) : 'By appointment',
                'bio' => $doc->bio,
            ];
        })->all();

        return [
            'status' => 'success',
            'count' => count($formatted),
            'doctors' => $formatted,
        ];
    }
}
