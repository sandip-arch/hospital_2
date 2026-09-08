<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Services\AuditService;

class DoctorScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isDoctor() && $user->doctor) {
            $doctor = $user->doctor;
        } else {
            $doctorId = $request->query('doctor_id', Doctor::first()?->id);
            $doctor = Doctor::with(['user', 'department', 'availabilities'])->find($doctorId);
        }

        $allDoctors = Doctor::with('user')->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return view('doctors.schedule', compact('doctor', 'allDoctors', 'days'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'schedules' => 'required|array',
            'schedules.*.day' => 'required|string',
            'schedules.*.start_time' => 'required|string',
            'schedules.*.end_time' => 'required|string',
            'schedules.*.is_available' => 'nullable|boolean',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        foreach ($validated['schedules'] as $sch) {
            DoctorAvailability::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'day_of_week' => $sch['day'],
                ],
                [
                    'start_time' => $sch['start_time'],
                    'end_time' => $sch['end_time'],
                    'is_available' => isset($sch['is_available']) && $sch['is_available'] == '1',
                ]
            );
        }

        AuditService::log('UPDATE', 'doctor_availabilities', $doctor->id, "Updated weekly roster availability for {$doctor->full_name}");

        return back()->with('success', "Doctor availability schedule updated successfully!");
    }
}
