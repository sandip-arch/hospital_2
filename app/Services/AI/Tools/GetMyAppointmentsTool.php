<?php

namespace App\Services\AI\Tools;

use App\Models\Appointment;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Carbon\Carbon;

class GetMyAppointmentsTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_my_appointments';
    }

    public function description(): string
    {
        return 'List consultations and appointments. Patients retrieve their own appointments; doctors retrieve their clinical schedule; staff/admin can query facility-wide appointments.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'status' => [
                    'type' => 'STRING',
                    'description' => 'Filter by status: "scheduled", "completed", "cancelled", or "all". Default is "scheduled".',
                ],
                'date' => [
                    'type' => 'STRING',
                    'description' => 'Optional filter for a specific date (YYYY-MM-DD), or "today", "upcoming".',
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
        $query = Appointment::with(['doctor.department', 'patient', 'medicalRecord']);

        // 1. Strict Identity Binding
        if ($authenticatedUser->isPatient()) {
            $patient = $authenticatedUser->patient;
            if (!$patient) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'message' => 'No patient profile found for this account.',
                    'appointments' => [],
                ];
            }
            $query->where('patient_id', $patient->id);
        } elseif ($authenticatedUser->isDoctor()) {
            $doctor = $authenticatedUser->doctor;
            if (!$doctor) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'message' => 'No doctor profile found for this account.',
                    'appointments' => [],
                ];
            }
            $query->where('doctor_id', $doctor->id);
        }

        // 2. Status filter
        $status = strtolower($arguments['status'] ?? 'scheduled');
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // 3. Date filter
        if (!empty($arguments['date'])) {
            $dateArg = strtolower(trim($arguments['date']));
            if ($dateArg === 'today') {
                $query->whereDate('appointment_date', Carbon::today());
            } elseif ($dateArg === 'upcoming') {
                $query->whereDate('appointment_date', '>=', Carbon::today());
            } else {
                $query->whereDate('appointment_date', $dateArg);
            }
        }

        $appointments = $query->orderBy('appointment_date', 'asc')
            ->orderBy('time_slot', 'asc')
            ->take(20)
            ->get();

        $formatted = $appointments->map(function ($apt) use ($authenticatedUser) {
            $item = [
                'appointment_id' => $apt->id,
                'date' => $apt->appointment_date ? $apt->appointment_date->toDateString() : 'N/A',
                'time_slot' => $apt->time_slot,
                'status' => $apt->status,
                'reason' => $apt->reason,
                'doctor_notes' => $apt->doctor_notes,
            ];

            if ($authenticatedUser->isDoctor() || $authenticatedUser->isStaff() || $authenticatedUser->isAdmin()) {
                $item['patient_name'] = $apt->patient?->full_name ?? 'Unknown';
                $item['patient_code'] = $apt->patient?->patient_code ?? 'N/A';
                $item['patient_gender'] = $apt->patient?->gender ?? 'N/A';
                $item['patient_blood_group'] = $apt->patient?->blood_type ?? 'N/A';
            }

            if ($authenticatedUser->isPatient() || $authenticatedUser->isStaff() || $authenticatedUser->isAdmin()) {
                $item['doctor_name'] = $apt->doctor?->full_name ?? 'Dr. Assigned';
                $item['specialization'] = $apt->doctor?->specialization ?? 'General';
                $item['department'] = $apt->doctor?->department?->name ?? 'OPD';
                $item['consultation_fee'] = '$' . number_format($apt->doctor?->consultation_fee ?? 0, 2);
            }

            return $item;
        })->all();

        return [
            'status' => 'success',
            'user_role' => $authenticatedUser->primaryRole(),
            'count' => count($formatted),
            'appointments' => $formatted,
        ];
    }
}
