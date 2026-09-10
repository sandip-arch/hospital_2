<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Message;

class PatientChatAndDashboardUsernameTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_patient_chat_list_only_contains_appointed_doctors_and_receptionists()
    {
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $this->assertNotNull($patientUser);
        $patient = $patientUser->patient;
        $this->assertNotNull($patient);

        // John has appointments seeded with Dr. Sarah Jenkins
        $drSarahUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $receptionistUser = User::where('email', 'receptionist@hospital.test')->first();
        $superadminUser = User::where('email', 'superadmin@hospital.test')->first();
        $adminUser = User::where('email', 'admin@hospital.test')->first();
        $labtechUser = User::where('email', 'labtech@hospital.test')->first();
        $pharmacistUser = User::where('email', 'pharmacist@hospital.test')->first();

        // Doctors who were NEVER appointed by John
        $drJamesUser = User::where('email', 'dr.james@hospital.test')->first();

        $response = $this->actingAs($patientUser)->get('/messages');
        $response->assertStatus(200);

        // Appointed doctor and receptionist MUST be present in the chat list
        $response->assertSee($drSarahUser->name);
        $response->assertSee($receptionistUser->name);

        // Disallowed users MUST NOT be shown in the chat roster
        $response->assertDontSee($superadminUser->name);
        $response->assertDontSee($adminUser->name);
        $response->assertDontSee($labtechUser->name);
        $response->assertDontSee($pharmacistUser->name);
        $response->assertDontSee($drJamesUser->name);
    }

    public function test_patient_cannot_message_unauthorized_personnel()
    {
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $superadminUser = User::where('email', 'superadmin@hospital.test')->first();
        $labtechUser = User::where('email', 'labtech@hospital.test')->first();

        // Attempting to navigate directly to unauthorized user chat should redirect with error
        $response = $this->actingAs($patientUser)->get('/messages?user_id=' . $superadminUser->id);
        $response->assertRedirect('/messages');
        $response->assertSessionHas('error', 'Patients can only chat with their appointed doctors or hospital receptionists.');

        // Attempting to send message to lab assistant directly via POST should be rejected
        $postResponse = $this->actingAs($patientUser)->post('/messages', [
            'receiver_id' => $labtechUser->id,
            'message_body' => 'Hello lab assistant, this should not be allowed.',
        ]);
        $postResponse->assertSessionHas('error', 'Unauthorized. Patients can only message their appointed doctors or hospital receptionists.');

        $this->assertDatabaseMissing('messages', [
            'sender_id' => $patientUser->id,
            'receiver_id' => $labtechUser->id,
        ]);
    }

    public function test_patient_can_message_appointed_doctor_and_receptionist()
    {
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $drSarahUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $receptionistUser = User::where('email', 'receptionist@hospital.test')->first();

        // Send to appointed doctor
        $responseDoc = $this->actingAs($patientUser)->post('/messages', [
            'receiver_id' => $drSarahUser->id,
            'message_body' => 'Hello Dr. Sarah, inquiring about my cardiology visit.',
        ]);
        $responseDoc->assertRedirect('/messages?user_id=' . $drSarahUser->id);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $patientUser->id,
            'receiver_id' => $drSarahUser->id,
            'message_body' => 'Hello Dr. Sarah, inquiring about my cardiology visit.',
        ]);

        // Send to receptionist
        $responseRec = $this->actingAs($patientUser)->post('/messages', [
            'receiver_id' => $receptionistUser->id,
            'message_body' => 'Hello Front Desk, I would like to confirm my consultation slot.',
        ]);
        $responseRec->assertRedirect('/messages?user_id=' . $receptionistUser->id);

        $this->assertDatabaseHas('messages', [
            'sender_id' => $patientUser->id,
            'receiver_id' => $receptionistUser->id,
            'message_body' => 'Hello Front Desk, I would like to confirm my consultation slot.',
        ]);
    }

    public function test_dashboard_displays_signin_username_for_all_roles()
    {
        // 1. Superadmin
        $superadmin = User::where('email', 'superadmin@hospital.test')->first();
        $response = $this->actingAs($superadmin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sign-in Username');
        $response->assertSee($superadmin->username);

        // 2. Doctor
        $doctorUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $response = $this->actingAs($doctorUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sign-in Username');
        $response->assertSee($doctorUser->username);

        // 3. Staff / Receptionist
        $staffUser = User::where('email', 'receptionist@hospital.test')->first();
        $response = $this->actingAs($staffUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sign-in Username');
        $response->assertSee($staffUser->username);

        // 4. Patient
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $response = $this->actingAs($patientUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Sign-in Username');
        $response->assertSee($patientUser->username);
    }
}
