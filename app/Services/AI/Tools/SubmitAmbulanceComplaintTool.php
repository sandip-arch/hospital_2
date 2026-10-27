<?php

namespace App\Services\AI\Tools;

use App\Models\Ambulance;
use App\Models\AmbulanceComplaint;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class SubmitAmbulanceComplaintTool implements ToolInterface
{
    public function name(): string
    {
        return 'submit_ambulance_complaint';
    }

    public function description(): string
    {
        return 'Fleet Maintenance Action: Report a mechanical breakdown, medical equipment fault, or maintenance ticket for an ambulance.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'ambulance_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target vehicle ID (optional if driver has an assigned vehicle).',
                ],
                'title' => [
                    'type' => 'STRING',
                    'description' => 'Brief headline of the defect (e.g. Brake vibration, Defibrillator battery warning).',
                ],
                'category' => [
                    'type' => 'STRING',
                    'description' => 'Defect category: "mechanical", "electrical", "medical_equipment", or "general".',
                ],
                'priority' => [
                    'type' => 'STRING',
                    'description' => 'Urgency tier: "low", "medium", "high", or "critical".',
                ],
                'description' => [
                    'type' => 'STRING',
                    'description' => 'Detailed clinical/mechanical description of the problem.',
                ],
                'odometer_reading' => [
                    'type' => 'INTEGER',
                    'description' => 'Current vehicle odometer reading in kilometers or miles.',
                ],
            ],
            'required' => ['title', 'category', 'priority', 'description'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['driver', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $driver = $authenticatedUser->ambulanceDriver;
        $driverId = $driver?->id ?? 1;

        $ambulanceId = (int) ($arguments['ambulance_id'] ?? 0);
        if (!$ambulanceId && $driver) {
            $assigned = Ambulance::where('current_driver_id', $driver->id)->first();
            $ambulanceId = $assigned?->id ?? 0;
        }

        if (!$ambulanceId) {
            $firstAmb = Ambulance::first();
            $ambulanceId = $firstAmb?->id ?? 1;
        }

        $ambulance = Ambulance::find($ambulanceId);

        $complaint = AmbulanceComplaint::create([
            'ambulance_id' => $ambulanceId,
            'driver_id' => $driverId,
            'title' => trim($arguments['title']),
            'category' => strtolower($arguments['category'] ?? 'general'),
            'priority' => strtolower($arguments['priority'] ?? 'medium'),
            'description' => trim($arguments['description']),
            'odometer_reading' => (int) ($arguments['odometer_reading'] ?? 0),
            'status' => 'submitted',
        ]);

        return [
            'status' => 'success',
            'message' => "Fleet defect ticket #{$complaint->id} submitted successfully.",
            'ticket_id' => $complaint->id,
            'vehicle_number' => $ambulance?->vehicle_number ?? "Ambulance #{$ambulanceId}",
            'title' => $complaint->title,
            'category' => $complaint->category,
            'priority' => $complaint->priority,
            'ticket_status' => 'submitted',
        ];
    }
}
