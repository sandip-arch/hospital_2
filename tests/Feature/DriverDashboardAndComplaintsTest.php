<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Models\AmbulanceComplaint;
use App\Models\Notification;

class DriverDashboardAndComplaintsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_driver_logging_in_sees_driver_dashboard_with_patient_features_and_complaints_module(): void
    {
        $driverRole = Role::where('name', 'driver')->first();
        $driverUser = User::create([
            'name' => 'Test Driver Dave',
            'username' => 'drv_dave',
            'email' => 'dave.driver@hospital.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        $driverProfile = AmbulanceDriver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'CDL-DAVE-101',
            'contact_number' => '+1 (555) 777-1234',
            'status' => 'on_duty',
        ]);

        $response = $this->actingAs($driverUser)->get('/dashboard');
        $response->assertStatus(200);

        // CDL, username, and identity card
        $response->assertSee('CDL-DAVE-101');
        $response->assertSee('drv_dave');
        $response->assertSee('On Duty');

        // Extra Module: Ambulance complaints & defect reporting
        $response->assertSee('Ambulance Operations');
        $response->assertSee('Defect Reporting Module');
        $response->assertSee('Report Ambulance Issue');

        // Patient Health Portal features
        $response->assertSee('Patient Health Records');
        $response->assertSee('My Upcoming Consultations');
        $response->assertSee('My Medical Prescriptions');
        $response->assertSee('My Diagnostic Reports');
        $response->assertSee('Invoices', false);
    }

    public function test_driver_can_file_ambulance_complaint(): void
    {
        $driverRole = Role::where('name', 'driver')->first();
        $driverUser = User::create([
            'name' => 'Complaint Test Driver',
            'username' => 'drv_complaint',
            'email' => 'complaint.driver@hospital.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        $driverProfile = AmbulanceDriver::create([
            'user_id' => $driverUser->id,
            'license_number' => 'CDL-COMP-999',
            'contact_number' => '+1 (555) 888-9999',
            'status' => 'on_duty',
        ]);

        $ambulance = Ambulance::first();
        $this->assertNotNull($ambulance);

        $payload = [
            'ambulance_id' => $ambulance->id,
            'title' => 'Emergency Siren & Strobe Failure',
            'category' => 'electrical',
            'priority' => 'critical',
            'description' => 'Lightbar short circuit caused fuse blowout during dispatch.',
            'odometer_reading' => 54120,
        ];

        $response = $this->actingAs($driverUser)->post('/ambulance-complaints', $payload);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ambulance_complaints', [
            'ambulance_id' => $ambulance->id,
            'driver_id' => $driverProfile->id,
            'title' => 'Emergency Siren & Strobe Failure',
            'category' => 'electrical',
            'priority' => 'critical',
            'status' => 'submitted',
        ]);

        // Verify that administrators received notification
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertTrue(Notification::where('user_id', $admin->id)->where('title', 'like', '%Ambulance Issue Reported%')->exists());
    }

    public function test_admin_can_view_and_update_ambulance_complaint_status_and_notes(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $driver = AmbulanceDriver::first();
        $ambulance = Ambulance::first();

        $complaint = AmbulanceComplaint::create([
            'ambulance_id' => $ambulance->id,
            'driver_id' => $driver->id,
            'title' => 'Brake Fluid Leakage',
            'category' => 'tyres_brakes',
            'priority' => 'high',
            'description' => 'Pedal spongy and reservoir below minimum line.',
            'status' => 'submitted',
        ]);

        // Admin checks complaints register
        $response = $this->actingAs($admin)->get('/ambulance-complaints');
        $response->assertStatus(200);
        $response->assertSee('Brake Fluid Leakage');

        // Admin updates status to resolved
        $updateResponse = $this->actingAs($admin)->put("/ambulance-complaints/{$complaint->id}", [
            'status' => 'resolved',
            'admin_notes' => 'Master cylinder seals replaced and fluid flushed. Cleared for service.',
        ]);
        $updateResponse->assertSessionHas('success');

        $complaint->refresh();
        $this->assertEquals('resolved', $complaint->status);
        $this->assertEquals('Master cylinder seals replaced and fluid flushed. Cleared for service.', $complaint->admin_notes);
        $this->assertEquals($admin->id, $complaint->resolved_by);
        $this->assertNotNull($complaint->resolved_at);

        // Verify driver was notified
        $this->assertTrue(Notification::where('user_id', $driver->user_id)->where('title', 'like', '%Ambulance Complaint Update%')->exists());
    }

    public function test_driver_chat_roster_only_shows_admins_and_superadmins(): void
    {
        $driverRole = Role::where('name', 'driver')->first();
        $driverUser = User::create([
            'name' => 'Chat Scope Driver',
            'username' => 'drv_chat',
            'email' => 'chat.driver@hospital.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        $superadminUser = User::where('email', 'superadmin@hospital.test')->first();
        $adminUser = User::where('email', 'admin@hospital.test')->first();
        $drSarahUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $receptionistUser = User::where('email', 'receptionist@hospital.test')->first();

        $response = $this->actingAs($driverUser)->get('/messages');
        $response->assertStatus(200);

        // Must see administrative contacts
        $response->assertSee($superadminUser->name);
        $response->assertSee($adminUser->name);

        // Must NOT see patients, regular doctors, or receptionists
        $response->assertDontSee($drSarahUser->name);
        $response->assertDontSee($patientUser->name);
        $response->assertDontSee($receptionistUser->name);
    }

    public function test_driver_cannot_message_unauthorized_users(): void
    {
        $driverRole = Role::where('name', 'driver')->first();
        $driverUser = User::create([
            'name' => 'Restricted Driver',
            'username' => 'drv_restricted',
            'email' => 'restricted.driver@hospital.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $drSarahUser = User::where('email', 'dr.sarah@hospital.test')->first();

        // Trying to message patient
        $response1 = $this->actingAs($driverUser)->post('/messages', [
            'receiver_id' => $patientUser->id,
            'message_body' => 'Hello Patient',
        ]);
        $response1->assertSessionHas('error');

        // Trying to message doctor
        $response2 = $this->actingAs($driverUser)->post('/messages', [
            'receiver_id' => $drSarahUser->id,
            'message_body' => 'Hello Doctor',
        ]);
        $response2->assertSessionHas('error');

        // Navigating via query param to unauthorized user redirects with error
        $response3 = $this->actingAs($driverUser)->get("/messages?user_id={$patientUser->id}");
        $response3->assertRedirect('/messages');
        $response3->assertSessionHas('error');
    }

    public function test_admin_can_message_driver(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();

        $driverRole = Role::where('name', 'driver')->first();
        $driverUser = User::create([
            'name' => 'Contactable Driver',
            'username' => 'drv_contactable',
            'email' => 'contactable.driver@hospital.test',
            'password' => bcrypt('password'),
            'status' => 'active',
        ]);
        $driverUser->roles()->sync([$driverRole->id]);

        // Admin messages driver
        $response = $this->actingAs($admin)->post('/messages', [
            'receiver_id' => $driverUser->id,
            'message_body' => 'Please bring unit AMB-101 into bay 3 for maintenance.',
        ]);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $admin->id,
            'receiver_id' => $driverUser->id,
            'message_body' => 'Please bring unit AMB-101 into bay 3 for maintenance.',
        ]);
    }
}
