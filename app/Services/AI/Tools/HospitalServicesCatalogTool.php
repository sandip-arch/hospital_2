<?php

namespace App\Services\AI\Tools;

use App\Models\Department;
use App\Models\LabTest;
use App\Models\Room;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;

class HospitalServicesCatalogTool implements ToolInterface
{
    public function name(): string
    {
        return 'hospital_services_catalog';
    }

    public function description(): string
    {
        return 'Hospital Information Desk: Query hospital departments, medical specialties, diagnostic laboratory test price lists, inpatient room tiers, and 24/7 emergency hotline details.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'category' => [
                    'type' => 'STRING',
                    'description' => 'Target catalog domain: "departments", "lab_tests", "rooms", "emergency_info", or "all". Default is "all".',
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
        $category = strtolower($arguments['category'] ?? 'all');
        $result = [
            'status' => 'success',
            'hospital_name' => SystemSetting::get('hospital_name', 'Apex Horizon Medical Center'),
            'emergency_hotline' => SystemSetting::get('emergency_hotline', '+1 (800) 555-APEX / 911'),
            'address' => SystemSetting::get('hospital_address', '742 Healthcare Boulevard, Medical District'),
            'visiting_hours' => '10:00 AM - 12:00 PM & 04:00 PM - 07:00 PM Daily',
        ];

        if ($category === 'all' || $category === 'departments') {
            $result['departments'] = Department::withCount(['doctors', 'rooms'])->get()->map(function ($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->name,
                    'code' => $dept->code,
                    'description' => $dept->description,
                    'active_doctors' => $dept->doctors_count,
                    'inpatient_rooms' => $dept->rooms_count,
                ];
            })->all();
        }

        if ($category === 'all' || $category === 'lab_tests') {
            $result['diagnostic_lab_catalog'] = LabTest::take(20)->get()->map(function ($test) {
                return [
                    'id' => $test->id,
                    'test_name' => $test->name,
                    'code' => $test->test_code,
                    'category' => $test->category,
                    'standard_charge' => '$' . number_format($test->price, 2),
                    'normal_range' => $test->normal_range,
                    'unit' => $test->unit,
                ];
            })->all();
        }

        if ($category === 'all' || $category === 'rooms') {
            $result['inpatient_room_rates'] = [
                ['tier' => 'Emergency Room', 'charge_per_day' => '$350.00', 'features' => 'Immediate trauma triage, resuscitation, life support'],
                ['tier' => 'Intensive Care Unit (ICU)', 'charge_per_day' => '$500.00', 'features' => 'Continuous telemetry, ventilator support, 1:1 nurse ratio'],
                ['tier' => 'Private Room', 'charge_per_day' => '$250.00', 'features' => 'En-suite bath, companion bed, smart climate control'],
                ['tier' => 'Semi-Private Room', 'charge_per_day' => '$150.00', 'features' => '2-patient occupancy with privacy curtains'],
                ['tier' => 'General Ward', 'charge_per_day' => '$80.00', 'features' => 'Multi-bed ward with 24/7 central nursing station'],
            ];
        }

        return $result;
    }
}
