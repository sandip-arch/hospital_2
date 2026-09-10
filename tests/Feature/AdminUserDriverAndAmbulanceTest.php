<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Doctor;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;

class AdminUserDriverAndAmbulanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_admin_can_view_driver_role_in_user_creation_form(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->get('/admin/users/create');
        $response->assertStatus(200);
        $response->assertSee('Ambulance Driver');
        $response->assertSee('value="driver"', false);
    }

    public function test_admin_can_create_user_with_driver_role_and_driver_profile(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        $driverRole = Role::where('name', 'driver')->first();
        $this->assertNotNull($driverRole);

        $payload = [
            'name' => 'Kennet Lawson',
            'username' => 'drv_kennet_' . uniqid(),
            'email' => 'kennet_' . uniqid() . '@hospital.test',
            'password' => 'password123',
            'role' => 'driver',
            'driver_license_number' => 'CDL-MA-987654',
            'driver_phone' => '+1 (555) 999-8877',
            'driver_status' => 'on_duty',
        ];

        $response = $this->actingAs($admin)->post('/admin/users', $payload);
        $response->assertRedirect('/admin/users');

        $createdUser = User::where('email', $payload['email'])->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->hasRole('driver'));
        $this->assertTrue($createdUser->isDriver());

        $driverProfile = AmbulanceDriver::where('user_id', $createdUser->id)->first();
        $this->assertNotNull($driverProfile);
        $this->assertEquals('CDL-MA-987654', $driverProfile->license_number);
        $this->assertEquals('+1 (555) 999-8877', $driverProfile->contact_number);
        $this->assertEquals('on_duty', $driverProfile->status);
    }

    public function test_admin_can_edit_driver_profile(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $driverRole = Role::where('name', 'driver')->first();

        // Create driver user
        $driverUser = User::create([
            'name' => 'Original Driver',
            'username' => 'drv_orig_' . uniqid(),
            'email' => 'driver_orig_' . uniqid() . '@hospital.test',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        $driverProfile = AmbulanceDriver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'CDL-OLD-111111',
            'contact_number' => '+1 (555) 111-2222',
            'status' => 'on_duty',
        ]);

        $updatePayload = [
            'name' => 'Updated Driver Name',
            'email' => $driverUser->email,
            'username' => $driverUser->username,
            'role' => 'driver',
            'status' => 'active',
            'driver_license_number' => 'CDL-NEW-999999',
            'driver_phone' => '+1 (555) 333-4444',
            'driver_status' => 'off_duty',
        ];

        $response = $this->actingAs($admin)->put("/admin/users/{$driverUser->id}", $updatePayload);
        $response->assertRedirect('/admin/users');

        $driverProfile->refresh();
        $this->assertEquals('CDL-NEW-999999', $driverProfile->license_number);
        $this->assertEquals('+1 (555) 333-4444', $driverProfile->contact_number);
        $this->assertEquals('off_duty', $driverProfile->status);
    }

    public function test_ambulance_create_form_provides_available_drivers_and_doctors(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $doctor = Doctor::with('user')->first();
        $this->assertNotNull($doctor);

        $response = $this->actingAs($admin)->get('/admin/ambulances/create');
        $response->assertStatus(200);
        $response->assertSee('Assign Attending Doctor / Medical Officer (Optional)');
        $response->assertSee('Assign Dedicated Driver (Optional)');
        $response->assertSee($doctor->user->name);
    }

    public function test_admin_can_create_ambulance_with_assigned_driver_and_doctor(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $doctor = Doctor::first();
        $this->assertNotNull($doctor);

        // Find or create an unassigned driver
        $driver = AmbulanceDriver::whereDoesntHave('ambulance')->first();
        if (!$driver) {
            $driverUser = User::create([
                'name' => 'Ambulance Test Driver',
                'username' => 'amb_drv_' . uniqid(),
                'email' => 'amb_drv_' . uniqid() . '@hospital.test',
                'password' => bcrypt('password123'),
                'status' => 'active',
            ]);
            $driverRole = Role::where('name', 'driver')->first();
            $driverUser->roles()->sync([$driverRole->id]);

            $driver = AmbulanceDriver::create([
                'user_id' => $driverUser->id,
                'license_number' => 'CDL-AMB-' . rand(1000, 9999),
                'contact_number' => '+1 (555) 444-5555',
                'status' => 'on_duty',
            ]);
        }

        $vehiclePlate = 'AMB-' . rand(1000, 9999);
        $payload = [
            'vehicle_number' => $vehiclePlate,
            'model' => 'Mercedes-Benz Sprinter 3500 Mobile ICU',
            'type' => 'Advanced_Life_Support',
            'status' => 'available',
            'current_driver_id' => $driver->id,
            'assigned_doctor_id' => $doctor->id,
            'current_latitude' => 42.3375,
            'current_longitude' => -71.1065,
        ];

        $response = $this->actingAs($admin)->post('/admin/ambulances', $payload);
        $response->assertRedirect('/admin/ambulances');

        $ambulance = Ambulance::where('vehicle_number', $vehiclePlate)->first();
        $this->assertNotNull($ambulance);
        $this->assertEquals($driver->id, $ambulance->current_driver_id);
        $this->assertEquals($doctor->id, $ambulance->assigned_doctor_id);
        $this->assertEquals('Advanced_Life_Support', $ambulance->type);
        $this->assertNotNull($ambulance->assignedDoctor);
        $this->assertEquals($doctor->id, $ambulance->assignedDoctor->id);
    }

    public function test_admin_can_update_ambulance_assigned_doctor_and_driver(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $doctors = Doctor::take(2)->get();
        $doc1 = $doctors[0];
        $doc2 = $doctors->count() > 1 ? $doctors[1] : $doctors[0];

        $ambulance = Ambulance::create([
            'vehicle_number' => 'AMB-UPD-' . rand(100, 999),
            'model' => 'Ford Transit T-350',
            'type' => 'Basic',
            'status' => 'available',
            'assigned_doctor_id' => $doc1->id,
            'current_latitude' => 42.3500,
            'current_longitude' => -71.1200,
        ]);

        $updatePayload = [
            'vehicle_number' => $ambulance->vehicle_number,
            'model' => 'Ford Transit T-350 Updated',
            'type' => 'Basic',
            'status' => 'available',
            'current_driver_id' => null,
            'assigned_doctor_id' => $doc2->id,
            'current_latitude' => 42.3600,
            'current_longitude' => -71.1300,
        ];

        $response = $this->actingAs($admin)->put("/admin/ambulances/{$ambulance->id}", $updatePayload);
        $response->assertRedirect('/admin/ambulances');

        $ambulance->refresh();
        $this->assertEquals('Ford Transit T-350 Updated', $ambulance->model);
        $this->assertEquals($doc2->id, $ambulance->assigned_doctor_id);
    }

    public function test_ambulance_index_displays_assigned_driver_and_doctor(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $doctor = Doctor::with('user')->first();

        $driverUser = User::create([
            'name' => 'Display Test Driver',
            'username' => 'disp_drv_' . uniqid(),
            'email' => 'disp_drv_' . uniqid() . '@hospital.test',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);
        $driverRole = Role::where('name', 'driver')->first();
        $driverUser->roles()->sync([$driverRole->id]);

        $driver = AmbulanceDriver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'CDL-DISP-777',
            'contact_number' => '+1 (555) 777-8888',
            'status' => 'on_duty',
        ]);

        $ambulance = Ambulance::create([
            'vehicle_number' => 'AMB-DISP-' . rand(100, 999),
            'model' => 'Freightliner M2 Mobile Surgery Unit',
            'type' => 'Advanced_Life_Support',
            'status' => 'available',
            'current_driver_id' => $driver->id,
            'assigned_doctor_id' => $doctor->id,
            'current_latitude' => 42.3375,
            'current_longitude' => -71.1065,
        ]);

        $response = $this->actingAs($admin)->get('/admin/ambulances');
        $response->assertStatus(200);
        $response->assertSee($ambulance->vehicle_number);
        $response->assertSee('Display Test Driver');
        $response->assertSee($doctor->user->name);
    }
}
