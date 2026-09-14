<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HospitalLocationAndDriverRosterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_driver_roster_candidate_dropdown_only_contains_driver_role_users(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        // Create doctor user
        $doctorUser = User::factory()->create(['name' => 'Dr. Gregory House', 'email' => 'house@hospital.test']);
        $doctorUser->roles()->sync(Role::where('name', 'doctor')->first());

        // Create patient user
        $patientUser = User::factory()->create(['name' => 'John Patient', 'email' => 'john@patient.test']);
        $patientUser->roles()->sync(Role::where('name', 'patient')->first());

        // Create driver user without profile yet
        $driverUser = User::factory()->create(['name' => 'Dave Driver', 'email' => 'dave@ambulance.test']);
        $driverUser->roles()->sync(Role::where('name', 'driver')->first());

        $response = $this->actingAs($admin)->get(route('admin.ambulances.drivers'));
        $response->assertStatus(200);

        // Assert that driver user is in the candidateUsers collection
        $candidateUsers = $response->viewData('candidateUsers');
        $this->assertTrue($candidateUsers->contains('id', $driverUser->id));
        $this->assertFalse($candidateUsers->contains('id', $doctorUser->id), 'Doctor must not appear in driver roster candidates');
        $this->assertFalse($candidateUsers->contains('id', $patientUser->id), 'Patient must not appear in driver roster candidates');

        // View assertions: dropdown displays Dave Driver, but not Dr. House or John Patient as select options
        $response->assertSee('Dave Driver (dave@ambulance.test) [Driver]');
        $response->assertDontSee('house@hospital.test) [Driver]');
        $response->assertDontSee('john@patient.test) [Driver]');
    }

    public function test_driver_roster_supports_search_and_status_filters(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        $user1 = User::factory()->create(['name' => 'Frank Fast']);
        $user1->roles()->sync(Role::where('name', 'driver')->first());
        $driver1 = AmbulanceDriver::create([
            'user_id' => $user1->id,
            'license_number' => 'CDL-FAST-01',
            'contact_number' => '+15551110001',
            'status' => 'on_duty',
        ]);

        $user2 = User::factory()->create(['name' => 'Sam Slow']);
        $user2->roles()->sync(Role::where('name', 'driver')->first());
        $driver2 = AmbulanceDriver::create([
            'user_id' => $user2->id,
            'license_number' => 'CDL-SLOW-02',
            'contact_number' => '+15551110002',
            'status' => 'off_duty',
        ]);

        // Filter by on_duty
        $responseOnDuty = $this->actingAs($admin)->get(route('admin.ambulances.drivers', ['status' => 'on_duty']));
        $responseOnDuty->assertStatus(200);
        $responseOnDuty->assertSee('Frank Fast');
        $responseOnDuty->assertDontSee('Sam Slow');

        // Search by license number
        $responseSearch = $this->actingAs($admin)->get(route('admin.ambulances.drivers', ['search' => 'CDL-SLOW']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Sam Slow');
        $responseSearch->assertDontSee('Frank Fast');
    }

    public function test_admin_can_add_user_with_driver_role_and_assign_ambulance(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        $ambulance = Ambulance::create([
            'vehicle_number' => 'AMB-TEST-777',
            'model' => 'Ford Transit Emergency',
            'type' => 'Advanced_Life_Support',
            'status' => 'available',
        ]);

        $driverRole = Role::where('name', 'driver')->first();

        // Check create form returns available ambulances
        $responseForm = $this->actingAs($admin)->get(route('admin.users.create'));
        $responseForm->assertStatus(200);
        $responseForm->assertSee('AMB-TEST-777');

        // Post store user with driver role and ambulance_id
        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Marcus Emergency',
            'username' => 'marcus_amb',
            'email' => 'marcus@rapidcare.test',
            'password' => 'SecurePass123!',
            'role_id' => $driverRole->id,
            'status' => 'active',
            'driver_license_number' => 'MA-CDL-9090',
            'driver_phone' => '+1 (555) 909-8800',
            'driver_status' => 'on_duty',
            'ambulance_id' => $ambulance->id,
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'marcus@rapidcare.test')->first();
        $this->assertNotNull($createdUser);
        $this->assertTrue($createdUser->isDriver());

        $driverProfile = AmbulanceDriver::where('user_id', $createdUser->id)->first();
        $this->assertNotNull($driverProfile);
        $this->assertEquals('MA-CDL-9090', $driverProfile->license_number);
        $this->assertEquals('on_duty', $driverProfile->status);

        // Verify ambulance has been assigned to this driver
        $ambulance->refresh();
        $this->assertEquals($driverProfile->id, $ambulance->current_driver_id);
    }

    public function test_system_settings_stores_dynamic_hospital_coordinates(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin)->from('/admin/settings')->post(route('admin.settings.update'), [
            'hospital_name' => 'Metropolitan Apex Health',
            'hospital_phone' => '+1 (555) 800-1122',
            'hospital_email' => 'contact@metropolitan.test',
            'hospital_address' => '750 Longwood Avenue, Boston, MA 02115',
            'hospital_latitude' => '42.3389000',
            'hospital_longitude' => '-71.1072000',
            'tax_rate_percent' => '5.5',
            'currency_symbol' => '$',
            'appointment_slot_duration_minutes' => 30,
            'emergency_contact_number' => '911',
        ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success');

        $this->assertEquals('42.3389000', SystemSetting::get('hospital_latitude'));
        $this->assertEquals('-71.1072000', SystemSetting::get('hospital_longitude'));
        $this->assertEquals('750 Longwood Avenue, Boston, MA 02115', SystemSetting::get('hospital_address'));
    }

    public function test_updating_system_settings_repositions_ambulance_fleet_and_track_view_shows_updated_hospital(): void
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $this->assertNotNull($admin);

        // Update settings to Kolkata coordinates
        $response = $this->actingAs($admin)->from('/admin/settings')->post(route('admin.settings.update'), [
            'hospital_name' => 'Metro Care Hospital Kolkata',
            'hospital_phone' => '+91 33 2500 0000',
            'hospital_email' => 'kolkata@metrocare.test',
            'hospital_address' => '100 EM Bypass, Salt Lake, Kolkata, WB 700098',
            'hospital_latitude' => '22.5726460',
            'hospital_longitude' => '88.3638950',
            'tax_rate_percent' => '5.0',
            'currency_symbol' => '₹',
            'appointment_slot_duration_minutes' => 20,
            'emergency_contact_number' => '108',
        ]);

        $response->assertRedirect('/admin/settings');
        $response->assertSessionHas('success');

        // 1. Verify fleet ambulances were repositioned around new base (~22.57, ~88.36), not Boston (42.33, -71.10)
        $ambulance = Ambulance::first();
        $this->assertNotNull($ambulance);
        $this->assertEqualsWithDelta(22.57, $ambulance->current_latitude, 0.1, 'Ambulance latitude must be near Kolkata base');
        $this->assertEqualsWithDelta(88.36, $ambulance->current_longitude, 0.1, 'Ambulance longitude must be near Kolkata base');

        // 2. Book an ambulance and test track view & API
        $bookResponse = $this->post(route('ambulance.book.submit'), [
            'contact_phone' => '+91 9876543210',
            'pickup_address' => 'Park Street, Kolkata, WB 700016',
            'pickup_latitude' => '22.5532000',
            'pickup_longitude' => '88.3512000',
            'patient_name' => 'Amit Sen',
        ]);

        $booking = \App\Models\AmbulanceBooking::latest('id')->first();
        $this->assertNotNull($booking);
        $bookResponse->assertRedirect(route('ambulance.track', $booking->id));

        // 3. Track view must show Kolkata address, and MUST NOT show Boston
        $trackPage = $this->get(route('ambulance.track', $booking->id));
        $trackPage->assertStatus(200);
        $trackPage->assertSee('Metro Care Hospital Kolkata');
        $trackPage->assertSee('100 EM Bypass, Salt Lake, Kolkata, WB 700098');
        $trackPage->assertDontSee('500 Health Sciences Blvd, Boston MA');

        // 4. API track must also return updated hospital info
        $apiTrack = $this->getJson(route('ambulance.api.track', $booking->id));
        $apiTrack->assertStatus(200);
        $apiTrack->assertJsonPath('hospital.name', 'Metro Care Hospital Kolkata');
        $apiTrack->assertJsonPath('hospital.address', '100 EM Bypass, Salt Lake, Kolkata, WB 700098');
        $this->assertEqualsWithDelta(22.5726460, $apiTrack->json('hospital.lat'), 0.0001);
        $this->assertEqualsWithDelta(88.3638950, $apiTrack->json('hospital.lng'), 0.0001);
    }
}
