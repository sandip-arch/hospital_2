<?php

namespace App\Services\AI\Tools;

use App\Models\Ambulance;
use App\Models\AmbulanceBooking;
use App\Models\AmbulanceDriver;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class DriverDutyAndVehicleTool implements ToolInterface
{
    public function name(): string
    {
        return 'driver_duty_and_vehicle';
    }

    public function description(): string
    {
        return 'Ambulance Driver tool: Check assigned emergency vehicle specifications, view active dispatch trip orders, or toggle duty availability (on_duty / off_duty).';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'toggle_duty' => [
                    'type' => 'STRING',
                    'description' => 'Optional change duty status: "on_duty" or "off_duty".',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['driver', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $driver = $authenticatedUser->ambulanceDriver;

        if (!$driver && !$authenticatedUser->isAdmin()) {
            return [
                'status' => 'error',
                'message' => 'No ambulance driver profile is associated with this account.',
            ];
        }

        if (!$driver) {
            $driver = AmbulanceDriver::first();
        }

        if (!empty($arguments['toggle_duty'])) {
            $newStatus = strtolower(trim($arguments['toggle_duty']));
            if (in_array($newStatus, ['on_duty', 'off_duty'])) {
                $driver->update(['status' => $newStatus]);
            }
        }

        $ambulance = Ambulance::where('current_driver_id', $driver->id)->first();
        $activeTrip = null;

        if ($ambulance) {
            $activeTrip = AmbulanceBooking::with(['patient', 'destinationDepartment'])
                ->where('ambulance_id', $ambulance->id)
                ->whereIn('booking_status', ['requested', 'assigned', 'en_route', 'arrived'])
                ->latest('id')
                ->first();
        }

        return [
            'status' => 'success',
            'driver_name' => $driver->user?->name ?? 'Paramedic Driver',
            'duty_status' => $driver->status,
            'license_number' => $driver->license_number,
            'assigned_ambulance' => $ambulance ? [
                'vehicle_number' => $ambulance->vehicle_number,
                'model' => $ambulance->model,
                'type' => $ambulance->typeDisplay(),
                'status' => $ambulance->status,
                'live_coordinates' => "{$ambulance->current_latitude}, {$ambulance->current_longitude}",
            ] : 'No vehicle currently assigned',
            'active_dispatch' => $activeTrip ? [
                'booking_id' => $activeTrip->id,
                'status' => $activeTrip->statusDisplay(),
                'patient_name' => $activeTrip->patient?->full_name ?? 'Emergency Caller',
                'pickup_address' => $activeTrip->pickup_address,
                'caller_phone' => $activeTrip->contact_phone,
                'destination' => $activeTrip->destinationDepartment?->name ?? 'Emergency Ward',
            ] : 'No pending or active dispatches. Standing by.',
        ];
    }
}
