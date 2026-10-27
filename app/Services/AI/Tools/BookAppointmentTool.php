<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookAppointmentTool implements ToolInterface
{
    public function name(): string
    {
        return 'book_appointment';
    }

    public function description(): string
    {
        return 'Schedule a new medical consultation appointment with a doctor for a selected date and 30-minute time slot. Automatically validates doctor schedule and assigns the patient.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'doctor_id' => [
                    'type' => 'INTEGER',
                    'description' => 'The ID of the doctor to consult.',
                ],
                'appointment_date' => [
                    'type' => 'STRING',
                    'description' => 'Date of consultation in YYYY-MM-DD format (must be today or future).',
                ],
                'time_slot' => [
                    'type' => 'STRING',
                    'description' => 'Consultation time slot string, e.g. "09:00 - 09:30" or "14:30 - 15:00".',
                ],
                'reason' => [
                    'type' => 'STRING',
                    'description' => 'Chief complaint or reason for visit (e.g., chest discomfort, routine checkup, skin rash).',
                ],
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (applicable only for staff/admin; discarded for patient users to prevent IDOR).',
                ],
            ],
            'required' => ['doctor_id', 'appointment_date', 'time_slot'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            // 1. Resolve Patient with IDOR guard
            $patient = RelationshipGuard::resolveEnforcedPatient(
                $authenticatedUser,
                isset($arguments['patient_id']) ? (int) $arguments['patient_id'] : null
            );

            $doctorId = (int) ($arguments['doctor_id'] ?? 0);
            $doctor = Doctor::with(['user', 'department', 'availabilities'])->find($doctorId);

            if (!$doctor) {
                return [
                    'status' => 'error',
                    'message' => "Doctor with ID #{$doctorId} was not found.",
                ];
            }

            $date = Carbon::parse($arguments['appointment_date']);
            if ($date->isPast() && !$date->isToday()) {
                return [
                    'status' => 'error',
                    'message' => 'Cannot book appointments for past dates.',
                ];
            }

            $timeSlot = trim($arguments['time_slot']);

            // 2. Check for slot collision
            $existingAppointment = Appointment::where('doctor_id', $doctorId)
                ->whereDate('appointment_date', $date->toDateString())
                ->where('time_slot', $timeSlot)
                ->whereIn('status', ['scheduled', 'completed'])
                ->first();

            if ($existingAppointment) {
                return [
                    'status' => 'error',
                    'message' => "The time slot '{$timeSlot}' on {$date->toDateString()} is already booked with {$doctor->full_name}. Please choose another available slot.",
                ];
            }

            // 3. Create Appointment safely in transaction
            $appointment = DB::transaction(function () use ($patient, $doctor, $date, $timeSlot, $arguments) {
                $apt = Appointment::create([
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'department_id' => $doctor->department_id,
                    'appointment_date' => $date->toDateString(),
                    'time_slot' => $timeSlot,
                    'status' => 'scheduled',
                    'reason' => $arguments['reason'] ?? 'General Consultation',
                    'doctor_notes' => null,
                ]);

                // Create notification for patient's user if exists
                if ($patient->user_id) {
                    Notification::create([
                        'user_id' => $patient->user_id,
                        'title' => 'Appointment Confirmed',
                        'message' => "Your appointment with {$doctor->full_name} is confirmed for {$date->toFormattedDateString()} at {$timeSlot}.",
                        'type' => 'appointment',
                        'is_read' => false,
                    ]);
                }

                // Create notification for doctor's user
                if ($doctor->user_id) {
                    Notification::create([
                        'user_id' => $doctor->user_id,
                        'title' => 'New Patient Appointment',
                        'message' => "Patient {$patient->full_name} has scheduled an appointment on {$date->toFormattedDateString()} at {$timeSlot}.",
                        'type' => 'appointment',
                        'is_read' => false,
                    ]);
                }

                return $apt;
            });

            return [
                'status' => 'success',
                'message' => "Appointment successfully booked with {$doctor->full_name}!",
                'appointment_id' => $appointment->id,
                'patient_name' => $patient->full_name,
                'patient_code' => $patient->patient_code,
                'doctor_name' => $doctor->full_name,
                'department' => $doctor->department?->name ?? 'General',
                'date' => $date->toDateString(),
                'time_slot' => $timeSlot,
                'consultation_fee' => '$' . number_format($doctor->consultation_fee, 2),
                'reason' => $appointment->reason,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Failed to book appointment: ' . $e->getMessage(),
            ];
        }
    }
}
