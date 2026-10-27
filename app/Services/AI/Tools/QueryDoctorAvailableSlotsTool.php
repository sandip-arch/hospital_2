<?php

namespace App\Services\AI\Tools;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Carbon\Carbon;
use Exception;

class QueryDoctorAvailableSlotsTool implements ToolInterface
{
    public function name(): string
    {
        return 'query_doctor_available_slots';
    }

    public function description(): string
    {
        return 'Query a doctor\'s working schedule, operating hours, and available 30-minute consultation time slots for a given date, subtracting existing bookings.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'doctor_id' => [
                    'type' => 'INTEGER',
                    'description' => 'The ID of the doctor.',
                ],
                'date' => [
                    'type' => 'STRING',
                    'description' => 'Target date in YYYY-MM-DD format (e.g. 2026-10-27).',
                ],
            ],
            'required' => ['doctor_id', 'date'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'doctor', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $doctorId = (int) ($arguments['doctor_id'] ?? 0);
        $dateStr = $arguments['date'] ?? null;

        if (!$doctorId || !$dateStr) {
            return [
                'status' => 'error',
                'message' => 'Both doctor_id and date (YYYY-MM-DD) are required.',
            ];
        }

        try {
            $targetDate = Carbon::parse($dateStr);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid date format. Please supply YYYY-MM-DD.',
            ];
        }

        $doctor = Doctor::with(['user', 'department', 'availabilities'])->find($doctorId);
        if (!$doctor) {
            return [
                'status' => 'error',
                'message' => "Doctor with ID #{$doctorId} not found.",
            ];
        }

        $dayOfWeek = $targetDate->format('l'); // e.g. "Monday"

        // Find availability for this day of week
        $availability = $doctor->availabilities
            ->firstWhere('day_of_week', $dayOfWeek);

        if (!$availability || !$availability->is_available) {
            return [
                'status' => 'success',
                'doctor' => $doctor->full_name,
                'date' => $targetDate->toDateString(),
                'day_of_week' => $dayOfWeek,
                'is_working' => false,
                'message' => "{$doctor->full_name} is not on duty on {$dayOfWeek}s.",
                'available_slots' => [],
            ];
        }

        $startTimeStr = $availability->start_time ?? '09:00:00';
        $endTimeStr = $availability->end_time ?? '17:00:00';

        // Query booked slots on this date
        $bookedSlots = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $targetDate->toDateString())
            ->whereIn('status', ['scheduled', 'completed'])
            ->pluck('time_slot')
            ->map(fn($s) => trim($s))
            ->toArray();

        // Generate 30 min intervals
        $start = Carbon::parse($targetDate->toDateString() . ' ' . $startTimeStr);
        $end = Carbon::parse($targetDate->toDateString() . ' ' . $endTimeStr);

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->copy()->addMinutes(30)->lte($end)) {
            $slotEnd = $cursor->copy()->addMinutes(30);
            $slotLabel = $cursor->format('H:i') . ' - ' . $slotEnd->format('H:i');

            $isBooked = in_array($slotLabel, $bookedSlots);

            $slots[] = [
                'time_slot' => $slotLabel,
                'is_available' => !$isBooked,
                'status' => $isBooked ? 'booked' : 'available',
            ];

            $cursor->addMinutes(30);
        }

        $availableOnly = array_values(array_filter($slots, fn($s) => $s['is_available']));

        return [
            'status' => 'success',
            'doctor' => $doctor->full_name,
            'specialization' => $doctor->specialization,
            'consultation_fee' => '$' . number_format($doctor->consultation_fee, 2),
            'date' => $targetDate->toDateString(),
            'day_of_week' => $dayOfWeek,
            'duty_hours' => substr($startTimeStr, 0, 5) . ' to ' . substr($endTimeStr, 0, 5),
            'total_slots' => count($slots),
            'available_slots_count' => count($availableOnly),
            'available_slots' => array_column($availableOnly, 'time_slot'),
            'all_slots' => $slots,
        ];
    }
}
