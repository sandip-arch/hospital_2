<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\Notification;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Carbon\Carbon;
use Exception;

class RescheduleAppointmentTool implements ToolInterface
{
    public function name(): string
    {
        return 'reschedule_appointment';
    }

    public function description(): string
    {
        return 'Reschedule an existing hospital appointment to a new date and time slot with automated conflict checking.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'appointment_id' => [
                    'type' => 'INTEGER',
                    'description' => 'The ID of the existing appointment to reschedule.',
                ],
                'new_date' => [
                    'type' => 'STRING',
                    'description' => 'The new target consultation date in YYYY-MM-DD format.',
                ],
                'new_time_slot' => [
                    'type' => 'STRING',
                    'description' => 'The new time slot string, e.g. "10:30 - 11:00".',
                ],
            ],
            'required' => ['appointment_id', 'new_date', 'new_time_slot'],
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

        // Ownership authorization check
        if ($authenticatedUser->isPatient()) {
            $patient = $authenticatedUser->patient;
            if (!$patient || $appointment->patient_id !== $patient->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You cannot reschedule appointments belonging to another patient.',
                ];
            }
        } elseif ($authenticatedUser->isDoctor()) {
            $doctor = $authenticatedUser->doctor;
            if (!$doctor || $appointment->doctor_id !== $doctor->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You cannot reschedule appointments assigned to another doctor.',
                ];
            }
        }

        if ($appointment->status === 'completed') {
            return [
                'status' => 'error',
                'message' => 'Completed appointments cannot be rescheduled.',
            ];
        }

        try {
            $newDate = Carbon::parse($arguments['new_date']);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid date format. Use YYYY-MM-DD.',
            ];
        }

        if ($newDate->isPast() && !$newDate->isToday()) {
            return [
                'status' => 'error',
                'message' => 'Cannot reschedule an appointment to a past date.',
            ];
        }

        $newSlot = trim($arguments['new_time_slot']);

        // Check for collision on the doctor's calendar
        $collision = Appointment::where('doctor_id', $appointment->doctor_id)
            ->where('id', '!=', $appointment->id)
            ->whereDate('appointment_date', $newDate->toDateString())
            ->where('time_slot', $newSlot)
            ->whereIn('status', ['scheduled', 'completed'])
            ->exists();

        if ($collision) {
            return [
                'status' => 'error',
                'message' => "The slot '{$newSlot}' on {$newDate->toDateString()} is already occupied. Please query available slots first.",
            ];
        }

        $oldDate = $appointment->appointment_date->toDateString();
        $oldSlot = $appointment->time_slot;

        $appointment->update([
            'appointment_date' => $newDate->toDateString(),
            'time_slot' => $newSlot,
            'status' => 'scheduled',
        ]);

        if ($appointment->patient?->user_id) {
            Notification::create([
                'user_id' => $appointment->patient->user_id,
                'title' => 'Appointment Rescheduled',
                'message' => "Your appointment with {$appointment->doctor?->full_name} has been rescheduled to {$newDate->toFormattedDateString()} at {$newSlot}.",
                'type' => 'appointment',
                'is_read' => false,
            ]);
        }

        return [
            'status' => 'success',
            'message' => "Appointment #{$appointmentId} has been successfully rescheduled.",
            'appointment_id' => $appointment->id,
            'doctor' => $appointment->doctor?->full_name,
            'previous_schedule' => "{$oldDate} at {$oldSlot}",
            'new_schedule' => "{$newDate->toDateString()} at {$newSlot}",
        ];
    }
}
