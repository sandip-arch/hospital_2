<?php

namespace App\Services\AI\Tools;

use App\Models\Ambulance;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class DiscoverNearbyAmbulancesTool implements ToolInterface
{
    public function name(): string
    {
        return 'discover_nearby_ambulances';
    }

    public function description(): string
    {
        return 'Discover emergency ambulance vehicles in the hospital fleet, including real-time status (available, dispatched, in_transit), equipment capability (ALS/ICU, Basic, Patient Transport), assigned drivers, and current GPS coordinates.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'type' => [
                    'type' => 'STRING',
                    'description' => 'Filter by capability: "Advanced_Life_Support", "Basic", or "Patient_Transport".',
                ],
                'only_available' => [
                    'type' => 'BOOLEAN',
                    'description' => 'Only show ready-to-dispatch ambulances with "available" status.',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin', 'driver'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $query = Ambulance::with(['currentDriver.user', 'assignedDoctor.user']);

        if (!empty($arguments['type'])) {
            $query->where('type', $arguments['type']);
        }

        if (isset($arguments['only_available']) && $arguments['only_available']) {
            $query->where('status', 'available');
        }

        $ambulances = $query->get();

        $formatted = $ambulances->map(function ($amb) {
            return [
                'ambulance_id' => $amb->id,
                'vehicle_number' => $amb->vehicle_number,
                'model' => $amb->model,
                'type' => $amb->typeDisplay(),
                'status' => $amb->status,
                'is_available' => $amb->isAvailable(),
                'driver_name' => $amb->currentDriver?->user?->name ?? 'On Call',
                'driver_phone' => $amb->currentDriver?->phone ?? 'Hospital Dispatch',
                'assigned_doctor' => $amb->assignedDoctor ? $amb->assignedDoctor->full_name : 'Paramedic Squad',
                'latitude' => $amb->current_latitude,
                'longitude' => $amb->current_longitude,
                'last_ping' => $amb->last_location_update ? $amb->last_location_update->diffForHumans() : 'Live',
            ];
        })->all();

        return [
            'status' => 'success',
            'emergency_hotline' => '+1 (800) 555-APEX / 911',
            'total_fleet' => count($formatted),
            'available_count' => count(array_filter($formatted, fn($a) => $a['is_available'])),
            'ambulances' => $formatted,
        ];
    }
}
