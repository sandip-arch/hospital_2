<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Http\Controllers\AmbulanceController;

class AmbulanceDeviceLocationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
        
        // Seed driver user & ambulance for testing
        $user = User::create([
            'name' => 'Driver John',
            'email' => 'driver1@test.com',
            'password' => bcrypt('secret'),
            'role' => 'ambulance_driver',
        ]);

        $driver = AmbulanceDriver::create([
            'user_id' => $user->id,
            'license_number' => 'MED-12345',
            'contact_number' => '+15551234567',
            'status' => 'on_duty',
        ]);

        Ambulance::create([
            'vehicle_number' => 'AMB-TEST-01',
            'model' => 'Ford Transit Medic',
            'type' => 'Advanced_Life_Support',
            'current_driver_id' => $driver->id,
            'status' => 'available',
            'current_latitude' => 51.5074,
            'current_longitude' => -0.1278,
        ]);
    }

    public function test_haversine_distance_calculation()
    {
        // Boston to New York is ~306 km
        $dist = AmbulanceController::calculateDistanceKm(42.3601, -71.0589, 40.7128, -74.0060);
        $this->assertGreaterThan(300, $dist);
        $this->assertLessThan(320, $dist);

        // Identical points should be 0 km
        $distZero = AmbulanceController::calculateDistanceKm(42.3601, -71.0589, 42.3601, -71.0589);
        $this->assertEquals(0.0, $distZero);
    }

    public function test_ambulance_discovery_page_loads_with_device_coordinates()
    {
        $response = $this->get('/ambulance?lat=28.6139&lng=77.2090&radius=25');
        $response->assertStatus(200);
        $response->assertSee('Live Ambulance Radar Map');
        $response->assertSee('Your Device Location');
    }

    public function test_api_locations_returns_distances_relative_to_device_coordinates()
    {
        $response = $this->getJson('/ambulance/api/locations?lat=51.5000&lng=-0.1200');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'hospital',
            'user_location' => ['lat', 'lng'],
            'ambulances' => [
                '*' => [
                    'id',
                    'vehicle_number',
                    'status',
                    'lat',
                    'lng',
                    'distance_km',
                    'eta_minutes',
                ]
            ]
        ]);

        $data = $response->json();
        $this->assertNotNull($data['user_location']);
        $this->assertEquals(51.5, $data['user_location']['lat']);
        $this->assertNotEmpty($data['ambulances']);
        $this->assertNotNull($data['ambulances'][0]['distance_km']);
    }

    public function test_api_reposition_near_device_stations_fleet_around_gps()
    {
        $deviceLat = 28.6139;
        $deviceLng = 77.2090;

        $response = $this->postJson('/ambulance/api/reposition-near-device', [
            'lat' => $deviceLat,
            'lng' => $deviceLng,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'center' => [
                'lat' => $deviceLat,
                'lng' => $deviceLng,
            ]
        ]);

        $firstAmbulance = Ambulance::first();
        $this->assertNotNull($firstAmbulance);
        $dist = AmbulanceController::calculateDistanceKm($deviceLat, $deviceLng, $firstAmbulance->current_latitude, $firstAmbulance->current_longitude);
        $this->assertLessThan(10.0, $dist);
    }

    public function test_booking_page_prefills_device_coordinates_from_query()
    {
        $response = $this->get('/ambulance/book?pickup_lat=28.6139000&pickup_lng=77.2090000&pickup_address=' . urlencode('Connaught Place, New Delhi'));
        $response->assertStatus(200);
        $response->assertSee('28.6139');
        $response->assertSee('77.2090');
        $response->assertSee('Connaught Place, New Delhi');
    }

    public function test_store_booking_with_device_coordinates_dispatches_nearest_ambulance()
    {
        $testPhone = '+15559876543';

        $response = $this->post('/ambulance/book', [
            'patient_name' => 'Device GPS Test Patient',
            'contact_phone' => $testPhone,
            'pickup_address' => 'Device Coordinates Sector 42',
            'pickup_latitude' => 51.5080,
            'pickup_longitude' => -0.1280,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
    }

    public function test_admin_create_ambulance_page_loads_with_device_location_features()
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin_test@hospital.test',
            'password' => bcrypt('secret'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'admin'], ['slug' => 'admin', 'description' => 'Administrator']);
        $admin->roles()->attach($role->id);

        $response = $this->actingAs($admin)->get('/admin/ambulances/create');
        $response->assertStatus(200);
        $response->assertSee('Use My Device Location');
        $response->assertSee('Station Base Latitude');
    }

    public function test_admin_can_store_ambulance_with_device_coordinates()
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin_test@hospital.test',
            'password' => bcrypt('secret'),
            'role' => 'superadmin',
            'status' => 'active',
        ]);

        $role = \App\Models\Role::firstOrCreate(['name' => 'superadmin'], ['slug' => 'superadmin', 'description' => 'Superadmin']);
        $admin->roles()->attach($role->id);

        $response = $this->actingAs($admin)->post('/admin/ambulances', [
            'vehicle_number' => 'AMB-GPS-99',
            'model' => 'Mercedes Mobile ICU 2026',
            'type' => 'Advanced_Life_Support',
            'status' => 'available',
            'current_latitude' => 28.6139,
            'current_longitude' => 77.2090,
        ]);

        $response->assertRedirect('/admin/ambulances');
        $this->assertDatabaseHas('ambulances', [
            'vehicle_number' => 'AMB-GPS-99',
            'current_latitude' => 28.6139,
            'current_longitude' => 77.2090,
        ]);
    }
}
