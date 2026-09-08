<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Staff;
use App\Models\Manager;

class UserAndStaffSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $doctorRole = Role::where('name', 'doctor')->first();
        $staffRole = Role::where('name', 'staff')->first();

        $cardiology = Department::where('code', 'CARD')->first();
        $neurology = Department::where('code', 'NEUR')->first();
        $pediatrics = Department::where('code', 'PED')->first();
        $orthopedics = Department::where('code', 'ORTH')->first();
        $emergency = Department::where('code', 'EMER')->first();

        // 1. Superadmin User
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@hospital.test'],
            [
                'name' => 'Dr. Alexander Vance (Superadmin)',
                'username' => 'superadmin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $superadmin->roles()->sync([$superadminRole->id]);

        // 2. Admin User (Hospital Manager)
        $admin = User::updateOrCreate(
            ['email' => 'admin@hospital.test'],
            [
                'name' => 'Elena Rostova (Admin)',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );
        $admin->roles()->sync([$adminRole->id]);
        Manager::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'department_id' => $cardiology->id,
                'title' => 'Chief Operations Officer',
            ]
        );

        // 3. Doctors
        $doctorsData = [
            [
                'name' => 'Dr. Sarah Jenkins',
                'email' => 'dr.sarah@hospital.test',
                'username' => 'dr_sarah',
                'department' => $cardiology,
                'specialization' => 'Senior Interventional Cardiologist',
                'license_number' => 'MED-CARD-98421',
                'fee' => 120.00,
                'phone' => '+1 (555) 234-5678',
                'bio' => 'Board-certified cardiologist with 15+ years experience in complex coronary interventions and echocardiography.',
            ],
            [
                'name' => 'Dr. James Wilson',
                'email' => 'dr.james@hospital.test',
                'username' => 'dr_james',
                'department' => $neurology,
                'specialization' => 'Consultant Neurologist & Neurophysiologist',
                'license_number' => 'MED-NEUR-77219',
                'fee' => 150.00,
                'phone' => '+1 (555) 345-6789',
                'bio' => 'Specializing in acute ischemic stroke, epilepsy management, and neuromuscular disorders.',
            ],
            [
                'name' => 'Dr. Emily Chang',
                'email' => 'dr.emily@hospital.test',
                'username' => 'dr_emily',
                'department' => $pediatrics,
                'specialization' => 'Pediatrician & Neonatal Specialist',
                'license_number' => 'MED-PED-55102',
                'fee' => 95.00,
                'phone' => '+1 (555) 456-7890',
                'bio' => 'Passionate child care specialist with focus on developmental pediatrics and newborn intensive care.',
            ],
            [
                'name' => 'Dr. Robert Taylor',
                'email' => 'dr.robert@hospital.test',
                'username' => 'dr_robert',
                'department' => $orthopedics,
                'specialization' => 'Orthopedic & Joint Reconstruction Surgeon',
                'license_number' => 'MED-ORTH-33891',
                'fee' => 135.00,
                'phone' => '+1 (555) 567-8901',
                'bio' => 'Expert in arthroscopic joint reconstruction, total hip/knee arthroplasty, and trauma fixation.',
            ],
        ];

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        foreach ($doctorsData as $doc) {
            $user = User::updateOrCreate(
                ['email' => $doc['email']],
                [
                    'name' => $doc['name'],
                    'username' => $doc['username'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );
            $user->roles()->sync([$doctorRole->id]);

            $doctor = Doctor::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $doc['department']->id,
                    'specialization' => $doc['specialization'],
                    'license_number' => $doc['license_number'],
                    'consultation_fee' => $doc['fee'],
                    'phone' => $doc['phone'],
                    'bio' => $doc['bio'],
                    'is_available' => true,
                ]
            );

            // Seed availability schedule
            foreach ($days as $day) {
                DoctorAvailability::firstOrCreate(
                    [
                        'doctor_id' => $doctor->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '17:00:00',
                        'is_available' => true,
                    ]
                );
            }
        }

        // 4. Staff members (Receptionist, Nurse, Pharmacist, Lab Tech, Cashier)
        $staffData = [
            [
                'name' => 'Rachel Adams (Receptionist)',
                'email' => 'receptionist@hospital.test',
                'username' => 'receptionist',
                'job_title' => 'Chief Receptionist / Front Desk Officer',
                'phone' => '+1 (555) 601-1122',
                'dept' => $emergency,
            ],
            [
                'name' => 'Nurse Clara Nightingale',
                'email' => 'nurse@hospital.test',
                'username' => 'nurse',
                'job_title' => 'Senior Head Nurse - Inpatient Care',
                'phone' => '+1 (555) 602-2233',
                'dept' => $cardiology,
            ],
            [
                'name' => 'Michael Chen (Pharmacist)',
                'email' => 'pharmacist@hospital.test',
                'username' => 'pharmacist',
                'job_title' => 'Lead Hospital Pharmacist',
                'phone' => '+1 (555) 603-3344',
                'dept' => $emergency,
            ],
            [
                'name' => 'David Miller (Lab Tech)',
                'email' => 'labtech@hospital.test',
                'username' => 'labtech',
                'job_title' => 'Senior Laboratory Technologist',
                'phone' => '+1 (555) 604-4455',
                'dept' => Department::where('code', 'RAD')->first() ?? $emergency,
            ],
            [
                'name' => 'Sophia Patel (Cashier & Billing)',
                'email' => 'cashier@hospital.test',
                'username' => 'cashier',
                'job_title' => 'Billing Officer & Cashier',
                'phone' => '+1 (555) 605-5566',
                'dept' => $emergency,
            ],
        ];

        foreach ($staffData as $st) {
            $user = User::updateOrCreate(
                ['email' => $st['email']],
                [
                    'name' => $st['name'],
                    'username' => $st['username'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );
            $user->roles()->sync([$staffRole->id]);

            Staff::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'department_id' => $st['dept']->id ?? null,
                    'job_title' => $st['job_title'],
                    'phone' => $st['phone'],
                    'hire_date' => now()->subYears(2)->toDateString(),
                ]
            );
        }
    }
}
