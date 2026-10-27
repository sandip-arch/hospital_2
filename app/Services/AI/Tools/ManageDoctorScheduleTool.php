<?php

namespace App\Services\AI\Tools;

use App\Models\DoctorAvailability;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class ManageDoctorScheduleTool implements ToolInterface
{
    public function name(): string
    {
        return 'manage_doctor_schedule';
    }

    public function description(): string
    {
        return 'Doctor tool: View or modify weekly consultation schedule, working days (Monday-Sunday), operating hours (start and end times), and availability status.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'day_of_week' => [
                    'type' => 'STRING',
                    'description' => 'Target day of week: "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday".',
                ],
                'start_time' => [
                    'type' => 'STRING',
                    'description' => 'Operating start time in 24hr format e.g. "09:00".',
                ],
                'end_time' => [
                    'type' => 'STRING',
                    'description' => 'Operating end time in 24hr format e.g. "17:00".',
                ],
                'is_available' => [
                    'type' => 'BOOLEAN',
                    'description' => 'True to mark on-duty, false to mark as day off.',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['doctor'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $doctor = $authenticatedUser->doctor;

        if (!$doctor) {
            return [
                'status' => 'error',
                'message' => 'No active doctor profile found for this user account.',
            ];
        }

        // If day_of_week is provided with updates, save changes
        if (!empty($arguments['day_of_week'])) {
            $day = ucfirst(strtolower(trim($arguments['day_of_week'])));

            $availability = DoctorAvailability::firstOrNew([
                'doctor_id' => $doctor->id,
                'day_of_week' => $day,
            ]);

            if (isset($arguments['start_time'])) {
                $availability->start_time = $arguments['start_time'] . ':00';
            }
            if (isset($arguments['end_time'])) {
                $availability->end_time = $arguments['end_time'] . ':00';
            }
            if (isset($arguments['is_available'])) {
                $availability->is_available = (bool) $arguments['is_available'];
            }

            $availability->save();

            return [
                'status' => 'success',
                'message' => "Schedule for {$day} updated successfully.",
                'doctor' => $doctor->full_name,
                'day_of_week' => $day,
                'is_available' => (bool) $availability->is_available,
                'hours' => substr($availability->start_time, 0, 5) . ' to ' . substr($availability->end_time, 0, 5),
            ];
        }

        // Return current weekly schedule
        $availabilities = DoctorAvailability::where('doctor_id', $doctor->id)->get();

        return [
            'status' => 'success',
            'doctor' => $doctor->full_name,
            'is_overall_available' => (bool) $doctor->is_available,
            'weekly_schedule' => $availabilities->map(function ($avail) {
                return [
                    'day' => $avail->day_of_week,
                    'is_available' => (bool) $avail->is_available,
                    'start_time' => substr($avail->start_time, 0, 5),
                    'end_time' => substr($avail->end_time, 0, 5),
                ];
            })->all(),
        ];
    }
}
