<?php

namespace App\Services\AI\Tools;

use App\Models\AmbulanceBooking;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class TrackAmbulanceBookingTool implements ToolInterface
{
    public function name(): string
    {
        return 'track_ambulance_booking';
    }

    public function description(): string
    {
        return 'Check live telemetry, ETA, driver contact, and GPS status for an active ambulance dispatch booking.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'booking_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Optional booking ID. If omitted for a patient, checks their most recent active booking.',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'staff', 'admin', 'superadmin', 'driver'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $bookingId = (int) ($arguments['booking_id'] ?? 0);

        if ($bookingId > 0) {
            $booking = AmbulanceBooking::with(['ambulance.currentDriver.user', 'patient', 'destinationDepartment'])->find($bookingId);
        } else {
            // Find latest booking for this patient
            if ($authenticatedUser->isPatient() && $authenticatedUser->patient) {
                $booking = AmbulanceBooking::with(['ambulance.currentDriver.user', 'patient', 'destinationDepartment'])
                    ->where('patient_id', $authenticatedUser->patient->id)
                    ->latest('id')
                    ->first();
            } else {
                $booking = AmbulanceBooking::with(['ambulance.currentDriver.user', 'patient', 'destinationDepartment'])
                    ->latest('id')
                    ->first();
            }
        }

        if (!$booking) {
            return [
                'status' => 'not_found',
                'message' => 'No active ambulance dispatch booking found.',
            ];
        }

        // Authorization check for patients
        if ($authenticatedUser->isPatient()) {
            $patient = $authenticatedUser->patient;
            if ($patient && $booking->patient_id !== $patient->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You cannot track ambulance bookings belonging to another individual.',
                ];
            }
        }

        $ambulance = $booking->ambulance;
        $driver = $ambulance?->currentDriver;

        return [
            'status' => 'success',
            'booking_id' => $booking->id,
            'booking_status' => $booking->booking_status,
            'status_display' => $booking->statusDisplay(),
            'progress_step' => $booking->stepIndex() . ' of 5',
            'vehicle_number' => $ambulance?->vehicle_number ?? 'Ambulance Assigned',
            'vehicle_type' => $ambulance?->typeDisplay() ?? 'Emergency Vehicle',
            'driver_name' => $driver?->user?->name ?? 'On Duty Paramedic',
            'driver_phone' => $driver?->phone ?? $booking->contact_phone,
            'pickup_address' => $booking->pickup_address,
            'hospital_destination' => $booking->destinationDepartment?->name ?? 'Emergency Ward',
            'live_latitude' => $ambulance?->current_latitude ?? $booking->pickup_latitude,
            'live_longitude' => $ambulance?->current_longitude ?? $booking->pickup_longitude,
            'tracking_url' => route('ambulance.track', ['id' => $booking->id]),
            'booking_time' => $booking->booking_time ? $booking->booking_time->diffForHumans() : 'Just now',
        ];
    }
}
