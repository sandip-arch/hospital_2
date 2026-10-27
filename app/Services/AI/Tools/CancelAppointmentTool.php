<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Exception;

class CancelAppointmentTool implements ToolInterface
{
    public function name(): string
    {
        return 'cancel_appointment';
    }

    public function description(): string
    {
        return 'Cancel an existing hospital appointment. Patients can cancel their own bookings; doctors and staff can cancel scheduled appointments in their care.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'appointment_id' => [
                    'type' => 'INTEGER',
                    'description' => 'The ID of the appointment to cancel.',
                ],
                'cancellation_reason' => [
                    'type' => 'STRING',
                    'description' => 'Reason for the cancellation.',
                ],
            ],
            'required' => ['appointment_id'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $appointmentId = (int) ($arguments['appointment_id'] ?? 0);
        $appointment = Appointment::with(['patient', 'doctor.user'])->find($appointmentId);

        if (!$appointment) {
            return [
                'status' => 'error',
                'message' => "Appointment #{$appointmentId} does not exist.",
            ];
        }

        // Enforce strict authorization
        if ($authenticatedUser->isPatient()) {
            $patient = $authenticatedUser->patient;
            if (!$patient || $appointment->patient_id !== $patient->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You are not authorized to cancel another patient\'s appointment.',
                ];
            }
        } elseif ($authenticatedUser->isDoctor()) {
            $doctor = $authenticatedUser->doctor;
            if (!$doctor || $appointment->doctor_id !== $doctor->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You can only cancel appointments scheduled with yourself.',
                ];
            }
        }

        if ($appointment->status === 'cancelled') {
            return [
                'status' => 'info',
                'message' => "Appointment #{$appointmentId} is already cancelled.",
            ];
        }

        if ($appointment->status === 'completed') {
            return [
                'status' => 'error',
                'message' => "Completed consultations cannot be cancelled.",
            ];
        }

        $reason = $arguments['cancellation_reason'] ?? 'Cancelled via AI Assistant';
        $appointment->update([
            'status' => 'cancelled',
            'doctor_notes' => trim(($appointment->doctor_notes ?? '') . " [Cancellation Reason: {$reason}]"),
        ]);

        // Notify other party
        if ($authenticatedUser->isPatient() && $appointment->doctor?->user_id) {
            Notification::create([
                'user_id' => $appointment->doctor->user_id,
                'title' => 'Appointment Cancelled',
                'message' => "Appointment with {$appointment->patient?->full_name} on {$appointment->appointment_date} ({$appointment->time_slot}) was cancelled.",
                'type' => 'appointment',
                'is_read' => false,
            ]);
        }

        return [
            'status' => 'success',
            'message' => "Appointment #{$appointmentId} has been successfully cancelled.",
            'appointment_id' => $appointment->id,
            'status_now' => 'cancelled',
            'doctor' => $appointment->doctor?->full_name,
            'date' => $appointment->appointment_date->toDateString(),
            'slot' => $appointment->time_slot,
        ];
    }
}
