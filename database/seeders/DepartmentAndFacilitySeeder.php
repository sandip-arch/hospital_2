<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Room;
use App\Models\Bed;

class DepartmentAndFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Cardiology', 'code' => 'CARD', 'icon' => 'heart-pulse', 'description' => 'Comprehensive cardiac care, catheterization, and heart surgery'],
            ['name' => 'Neurology', 'code' => 'NEUR', 'icon' => 'brain', 'description' => 'Advanced neurosurgery, stroke unit, and neurological rehabilitation'],
            ['name' => 'Pediatrics', 'code' => 'PED', 'icon' => 'baby', 'description' => 'Dedicated child healthcare, neonatal ICU, and adolescent medicine'],
            ['name' => 'Orthopedics', 'code' => 'ORTH', 'icon' => 'bone', 'description' => 'Joint replacement, trauma, sports medicine, and spine care'],
            ['name' => 'Oncology', 'code' => 'ONC', 'icon' => 'ribbon', 'description' => 'Medical and surgical oncology, chemotherapy, and radiation therapy'],
            ['name' => 'General Surgery', 'code' => 'GSURG', 'icon' => 'scalpel', 'description' => 'Minimally invasive laparoscopic, general, and emergency surgery'],
            ['name' => 'Radiology', 'code' => 'RAD', 'icon' => 'x-ray', 'description' => 'Advanced imaging: MRI, 128-slice CT, Ultrasound, Digital X-Ray'],
            ['name' => 'Emergency Medicine', 'code' => 'EMER', 'icon' => 'truck-medical', 'description' => '24/7 Level 1 Trauma center and critical emergency triage'],
        ];

        $deptModels = [];
        foreach ($departments as $dept) {
            $deptModels[$dept['code']] = Department::updateOrCreate(
                ['code' => $dept['code']],
                [
                    'name' => $dept['name'],
                    'icon' => $dept['icon'],
                    'description' => $dept['description'],
                ]
            );
        }

        // Create Rooms & Beds
        $roomConfigs = [
            ['room_number' => 'ICU-101', 'room_type' => 'ICU', 'dept' => 'EMER', 'daily_rate' => 450.00, 'beds' => 4],
            ['room_number' => 'ICU-102', 'room_type' => 'ICU', 'dept' => 'CARD', 'daily_rate' => 500.00, 'beds' => 4],
            ['room_number' => 'PRV-201', 'room_type' => 'Private', 'dept' => 'CARD', 'daily_rate' => 250.00, 'beds' => 1],
            ['room_number' => 'PRV-202', 'room_type' => 'Private', 'dept' => 'NEUR', 'daily_rate' => 250.00, 'beds' => 1],
            ['room_number' => 'PRV-203', 'room_type' => 'Private', 'dept' => 'ORTH', 'daily_rate' => 250.00, 'beds' => 1],
            ['room_number' => 'SEMI-301', 'room_type' => 'Semi-Private', 'dept' => 'PED', 'daily_rate' => 160.00, 'beds' => 2],
            ['room_number' => 'SEMI-302', 'room_type' => 'Semi-Private', 'dept' => 'GSURG', 'daily_rate' => 160.00, 'beds' => 2],
            ['room_number' => 'GEN-401', 'room_type' => 'General Ward', 'dept' => 'GSURG', 'daily_rate' => 80.00, 'beds' => 6],
            ['room_number' => 'GEN-402', 'room_type' => 'General Ward', 'dept' => 'ORTH', 'daily_rate' => 80.00, 'beds' => 6],
            ['room_number' => 'OT-01', 'room_type' => 'Operating Theater', 'dept' => 'GSURG', 'daily_rate' => 600.00, 'beds' => 1],
        ];

        foreach ($roomConfigs as $cfg) {
            $room = Room::updateOrCreate(
                ['room_number' => $cfg['room_number']],
                [
                    'room_type' => $cfg['room_type'],
                    'department_id' => $deptModels[$cfg['dept']]->id,
                    'daily_rate' => $cfg['daily_rate'],
                    'status' => 'available',
                ]
            );

            // Create Beds
            for ($b = 1; $b <= $cfg['beds']; $b++) {
                $bedNum = "B{$b}";
                Bed::firstOrCreate(
                    [
                        'room_id' => $room->id,
                        'bed_number' => $bedNum,
                    ],
                    [
                        'status' => 'available',
                    ]
                );
            }
        }
    }
}
