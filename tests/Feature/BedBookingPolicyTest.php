<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Patient;

class BedBookingPolicyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_bed_tracker_renders_for_patient_with_policy_banner()
    {
        $patientUser = User::whereHas('patient')->first();
        $this->assertNotNull($patientUser);

        $response = $this->actingAs($patientUser)->get('/facilities/bed-tracker');
        $response->assertStatus(200);
        $response->assertSee('Patient Inpatient Policy: Emergency Beds Only');
        $response->assertSee('Doctor / Staff Referral Only');
    }

    public function test_patient_cannot_book_non_emergency_icu_bed()
    {
        $patientUser = User::whereHas('patient')->first();
        $this->assertNotNull($patientUser);

        // Find an available non-emergency bed (e.g. ICU, Private, Ward)
        $nonEmergencyBed = Bed::whereHas('room', function ($q) {
            $q->where('room_type', '!=', 'Emergency')
              ->whereHas('department', function ($dq) {
                  $dq->where('code', '!=', 'EMER');
              });
        })->where('status', 'available')->first();

        $this->assertNotNull($nonEmergencyBed);
        $this->assertFalse($nonEmergencyBed->isEmergency());

        $doctor = Doctor::first();

        $response = $this->actingAs($patientUser)->post('/facilities/admit', [
            'bed_id' => $nonEmergencyBed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->toDateTimeString(),
            'admission_reason' => 'Patient self admission attempt on non-emergency bed',
        ]);

        $response->assertSessionHas('error');
        $response->assertSessionHas('error', function ($msg) {
            return str_contains($msg, 'Patients can only book Emergency beds directly');
        });

        // Ensure bed was NOT set to occupied
        $this->assertEquals('available', $nonEmergencyBed->fresh()->status);
    }

    public function test_patient_can_book_emergency_bed()
    {
        $patientUser = User::whereHas('patient')->first();
        $this->assertNotNull($patientUser);

        // Find or create an available emergency bed
        $emergencyBed = Bed::whereHas('room', function ($q) {
            $q->where('room_type', 'Emergency')
              ->orWhereHas('department', function ($dq) {
                  $dq->where('code', 'EMER');
              });
        })->where('status', 'available')->first();

        $this->assertNotNull($emergencyBed);
        $this->assertTrue($emergencyBed->isEmergency());

        $doctor = Doctor::first();

        $response = $this->actingAs($patientUser)->post('/facilities/admit', [
            'bed_id' => $emergencyBed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->toDateTimeString(),
            'admission_reason' => 'Acute trauma triage self-booking',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('occupied', $emergencyBed->fresh()->status);

        // Reset the bed for subsequent tests
        $emergencyBed->update(['status' => 'available']);
    }

    public function test_doctor_or_admin_can_book_icu_bed_for_patient()
    {
        $adminUser = User::where('email', 'admin@hospital.test')->first() 
            ?? User::where('email', 'superadmin@hospital.test')->first();
        $this->assertNotNull($adminUser);

        $patient = Patient::first();
        $doctor = Doctor::first();

        $icuBed = Bed::whereHas('room', function ($q) {
            $q->where('room_type', 'ICU');
        })->where('status', 'available')->first();

        $this->assertNotNull($icuBed);

        $response = $this->actingAs($adminUser)->post('/facilities/admit', [
            'patient_id' => $patient->id,
            'bed_id' => $icuBed->id,
            'doctor_id' => $doctor->id,
            'admission_date' => now()->toDateTimeString(),
            'admission_reason' => 'Critical post-operative ICU care',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('occupied', $icuBed->fresh()->status);

        // Reset the bed
        $icuBed->update(['status' => 'available']);
    }

    public function test_admin_can_switch_admitted_patient_bed_and_notify_patient()
    {
        $adminUser = User::where('email', 'admin@hospital.test')->first()
            ?? User::where('email', 'superadmin@hospital.test')->first();
        $this->assertNotNull($adminUser);

        // Pick a patient with a user account
        $patient = Patient::whereNotNull('user_id')->first();
        $this->assertNotNull($patient);
        $patientUserId = $patient->user_id;

        // Provision an active admission on an occupied bed
        $oldBed = Bed::where('status', 'available')->first();
        $this->assertNotNull($oldBed);
        $oldBed->update(['status' => 'occupied']);

        $admission = \App\Models\Admission::create([
            'patient_id' => $patient->id,
            'bed_id' => $oldBed->id,
            'doctor_id' => Doctor::first()->id,
            'admission_date' => now(),
            'status' => 'admitted',
        ]);

        // Find an available bed to switch into
        $newBed = Bed::where('status', 'available')->where('id', '!=', $oldBed->id)->first();
        $this->assertNotNull($newBed);

        $response = $this->actingAs($adminUser)->post("/facilities/admissions/{$admission->id}/switch-bed", [
            'target_bed_id' => $newBed->id,
            'switch_reason' => 'Transferred for step-down care and observation',
        ]);

        $response->assertSessionHas('success');

        // Old bed should be set to cleaning
        $this->assertEquals('cleaning', $oldBed->fresh()->status);

        // New bed should be occupied
        $this->assertEquals('occupied', $newBed->fresh()->status);

        // Admission should point to new bed
        $this->assertEquals($newBed->id, $admission->fresh()->bed_id);

        // Patient should receive notification
        $newNotification = \App\Models\Notification::where('user_id', $patientUserId)
            ->where('title', 'Inpatient Bed Transfer Update')
            ->latest('id')
            ->first();
        $this->assertNotNull($newNotification);
        $this->assertEquals('Inpatient Bed Transfer Update', $newNotification->title);
        $this->assertStringContainsString("Bed #{$newBed->bed_number}", $newNotification->message);
        $this->assertStringContainsString("Transferred for step-down care", $newNotification->message);
    }

    public function test_attending_doctor_can_switch_patient_bed()
    {
        // Find an active admission with doctor
        $admission = \App\Models\Admission::with(['doctor.user', 'bed'])->where('status', 'admitted')->first();
        $this->assertNotNull($admission);

        $attendingDoctorUser = $admission->doctor->user;
        $this->assertNotNull($attendingDoctorUser);

        $oldBed = $admission->bed;
        $newBed = Bed::where('status', 'available')->where('id', '!=', $oldBed->id)->first();
        $this->assertNotNull($newBed);

        $response = $this->actingAs($attendingDoctorUser)->post("/facilities/admissions/{$admission->id}/switch-bed", [
            'target_bed_id' => $newBed->id,
            'switch_reason' => 'Physician clinical transfer order',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals($newBed->id, $admission->fresh()->bed_id);
    }

    public function test_unauthorized_user_cannot_switch_patient_bed()
    {
        $admission = \App\Models\Admission::with(['doctor.user', 'bed'])->where('status', 'admitted')->first();
        $this->assertNotNull($admission);

        // Another doctor who is NOT the attending doctor
        $otherDoctorUser = User::whereHas('doctor', function ($q) use ($admission) {
            $q->where('id', '!=', $admission->doctor_id);
        })->first();

        $newBed = Bed::where('status', 'available')->first();

        if ($otherDoctorUser) {
            $response = $this->actingAs($otherDoctorUser)->post("/facilities/admissions/{$admission->id}/switch-bed", [
                'target_bed_id' => $newBed->id,
            ]);
            $response->assertStatus(403);
        }

        // Patient also cannot switch bed
        $patientUser = User::whereHas('patient')->first();
        $response = $this->actingAs($patientUser)->post("/facilities/admissions/{$admission->id}/switch-bed", [
            'target_bed_id' => $newBed->id,
        ]);
        $response->assertStatus(403);
    }

    public function test_patient_sees_this_bed_is_for_you_for_allocated_bed()
    {
        $patient = Patient::first();
        $patientUser = $patient->user;

        $bed = Bed::where('status', 'available')->first();
        $bed->update(['status' => 'occupied']);

        \App\Models\Admission::create([
            'patient_id' => $patient->id,
            'bed_id' => $bed->id,
            'doctor_id' => Doctor::first()->id,
            'admission_date' => now(),
            'status' => 'admitted',
        ]);

        $response = $this->actingAs($patientUser)->get('/facilities/bed-tracker');

        $response->assertStatus(200);
        $response->assertSee('This Bed is for You');
        $response->assertSee('Your Bed');
    }

    public function test_admin_renders_occupied_bed_with_stacked_buttons()
    {
        $adminUser = User::where('email', 'admin@hospital.test')->first()
            ?? User::where('email', 'superadmin@hospital.test')->first();
        $this->assertNotNull($adminUser);

        $response = $this->actingAs($adminUser)->get('/facilities/bed-tracker');

        $response->assertStatus(200);
        $response->assertSee('Switch');
        $response->assertSee('Discharge');
    }

    public function test_patient_with_active_non_emergency_bed_cannot_book_emergency_bed()
    {
        $patient = Patient::whereNotNull('user_id')->first();
        $this->assertNotNull($patient);
        $patientUser = $patient->user;

        // Give patient an active admission in a non-emergency bed (e.g. booked by doctor/admin)
        $nonEmerBed = Bed::whereHas('room', function ($q) {
            $q->where('room_type', '!=', 'Emergency')
              ->whereHas('department', function ($dq) {
                  $dq->where('code', '!=', 'EMER');
              });
        })->where('status', 'available')->first();
        $this->assertNotNull($nonEmerBed);
        $nonEmerBed->update(['status' => 'occupied']);

        \App\Models\Admission::create([
            'patient_id' => $patient->id,
            'bed_id' => $nonEmerBed->id,
            'doctor_id' => Doctor::first()->id,
            'admission_date' => now(),
            'status' => 'admitted',
        ]);

        // Find available emergency bed
        $emergencyBed = Bed::whereHas('room', function ($q) {
            $q->where('room_type', 'Emergency')
              ->orWhereHas('department', function ($dq) {
                  $dq->where('code', 'EMER');
              });
        })->where('status', 'available')->first();
        $this->assertNotNull($emergencyBed);

        // Bed model should not allow booking by this patient
        $this->assertFalse($emergencyBed->canBeBookedBy($patientUser));

        // Attempting to book emergency bed via POST should be rejected
        $response = $this->actingAs($patientUser)->post('/facilities/admit', [
            'bed_id' => $emergencyBed->id,
            'doctor_id' => Doctor::first()->id,
            'admission_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString("You already have a bed booked in Bed #{$nonEmerBed->bed_number}", session('error'));

        // Visiting bed-tracker view should show the lock notice
        $viewResponse = $this->actingAs($patientUser)->get('/facilities/bed-tracker');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee("You already have a bed booked in Bed #{$nonEmerBed->bed_number}");
    }
}



