<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\AmbulanceDriver;
use App\Models\Ambulance;

class AmbulanceSeeder extends Seeder
{
    public function run(): void
    {
        $driverRole = Role::where('name', 'driver')->first() ?? Role::where('name', 'staff')->first();

        // 1. Create Driver Users & Driver Profiles
        $driverProfiles = [
            [
                'name' => 'Marcus Vance',
                'username' => 'driver_marcus',
                'email' => 'driver1@hospital.test',
                'license_number' => 'MA-CDL-894021',
                'contact_number' => '+1 (555) 301-4411',
            ],
            [
                'name' => 'Carlos Mendez',
                'username' => 'driver_carlos',
                'email' => 'driver2@hospital.test',
                'license_number' => 'MA-CDL-712890',
                'contact_number' => '+1 (555) 301-5522',
            ],
            [
                'name' => 'Sarah O\'Connor',
                'username' => 'driver_sarah',
                'email' => 'driver3@hospital.test',
                'license_number' => 'MA-CDL-998314',
                'contact_number' => '+1 (555) 301-6633',
            ],
            [
                'name' => 'Ahmed Khan',
                'username' => 'driver_ahmed',
                'email' => 'driver4@hospital.test',
                'license_number' => 'MA-CDL-445102',
                'contact_number' => '+1 (555) 301-7744',
            ],
        ];

        $driverModels = [];
        foreach ($driverProfiles as $profile) {
            $user = User::firstOrCreate(
                ['email' => $profile['email']],
                [
                    'name' => $profile['name'],
                    'username' => $profile['username'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );

            if ($driverRole && !$user->roles()->where('roles.id', $driverRole->id)->exists()) {
                $user->roles()->sync([$driverRole->id]);
            }

            $driver = AmbulanceDriver::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'license_number' => $profile['license_number'],
                    'contact_number' => $profile['contact_number'],
                    'status' => 'on_duty',
                ]
            );

            $driverModels[] = $driver;
        }

        // 2. Initial Ambulances stationed around Boston
        $ambulances = [
            [
                'vehicle_number' => 'AMB-101',
                'model' => 'Mercedes-Benz Sprinter 3500 Mobile ICU',
                'type' => 'Advanced_Life_Support',
                'current_driver_id' => $driverModels[0]->id ?? null,
                'status' => 'available',
                'current_latitude' => 42.3412000,
                'current_longitude' => -71.0990000,
                'last_location_update' => now(),
            ],
            [
                'vehicle_number' => 'AMB-102',
                'model' => 'Ford Transit T-350 Emergency Unit',
                'type' => 'Basic',
                'current_driver_id' => $driverModels[1]->id ?? null,
                'status' => 'available',
                'current_latitude' => 42.3485000,
                'current_longitude' => -71.0820000,
                'last_location_update' => now(),
            ],
            [
                'vehicle_number' => 'AMB-103',
                'model' => 'Dodge Ram 4500 Heavy-Duty Rescue',
                'type' => 'Advanced_Life_Support',
                'current_driver_id' => $driverModels[2]->id ?? null,
                'status' => 'available',
                'current_latitude' => 42.3320000,
                'current_longitude' => -71.0710000,
                'last_location_update' => now(),
            ],
            [
                'vehicle_number' => 'AMB-104',
                'model' => 'Chevrolet Express 3500 Patient Shuttle',
                'type' => 'Patient_Transport',
                'current_driver_id' => $driverModels[3]->id ?? null,
                'status' => 'available',
                'current_latitude' => 42.3615000,
                'current_longitude' => -71.0920000,
                'last_location_update' => now(),
            ],
            [
                'vehicle_number' => 'AMB-105',
                'model' => 'Ford E-450 Super Duty Medic',
                'type' => 'Basic',
                'current_driver_id' => null,
                'status' => 'maintenance',
                'current_latitude' => 42.3350000,
                'current_longitude' => -71.1090000,
                'last_location_update' => now(),
            ],
        ];

        foreach ($ambulances as $data) {
            Ambulance::updateOrCreate(
                ['vehicle_number' => $data['vehicle_number']],
                $data
            );
        }
    }
}
