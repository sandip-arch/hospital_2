<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\LabTest;
use App\Models\Bed;

class HospitalManagementSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_public_pages_load_successfully()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Apex Horizon Medical Center');

        $response = $this->get('/doctors-directory');
        $response->assertStatus(200);

        $response = $this->get('/departments-overview');
        $response->assertStatus(200);

        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Instant Role-Based Demonstration');
    }

    public function test_superadmin_dashboard_and_admin_modules()
    {
        $superadmin = User::where('email', 'superadmin@hospital.test')->first();
        $this->assertNotNull($superadmin);

        $response = $this->actingAs($superadmin)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Hospital Executive Dashboard');

        $response = $this->actingAs($superadmin)->get('/admin/users');
        $response->assertStatus(200);

        $response = $this->actingAs($superadmin)->get('/admin/departments');
        $response->assertStatus(200);

        $response = $this->actingAs($superadmin)->get('/admin/audit-logs');
        $response->assertStatus(200);

        $response = $this->actingAs($superadmin)->get('/admin/settings');
        $response->assertStatus(200);
    }

    public function test_doctor_clinical_workspace()
    {
        $doctorUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $this->assertNotNull($doctorUser);

        $response = $this->actingAs($doctorUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Doctor Clinical Dashboard');

        $response = $this->actingAs($doctorUser)->get('/doctor/schedule');
        $response->assertStatus(200);

        $response = $this->actingAs($doctorUser)->get('/medical-records');
        $response->assertStatus(200);

        $response = $this->actingAs($doctorUser)->get('/prescriptions');
        $response->assertStatus(200);
    }

    public function test_staff_operations_center()
    {
        $staffUser = User::where('email', 'receptionist@hospital.test')->first();
        $this->assertNotNull($staffUser);

        $response = $this->actingAs($staffUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Staff & Clinical Support Center');

        $response = $this->actingAs($staffUser)->get('/facilities/bed-tracker');
        $response->assertStatus(200);

        $response = $this->actingAs($staffUser)->get('/facilities/admissions');
        $response->assertStatus(200);

        $response = $this->actingAs($staffUser)->get('/pharmacy');
        $response->assertStatus(200);

        $response = $this->actingAs($staffUser)->get('/billing');
        $response->assertStatus(200);
    }

    public function test_patient_health_portal()
    {
        $patientUser = User::where('email', 'patient.john@hospital.test')->first();
        $this->assertNotNull($patientUser);

        $response = $this->actingAs($patientUser)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('My Health Portal');
        $response->assertSee('PAT-2026-0001');
    }

    public function test_patient_registration_generates_upi()
    {
        $admin = User::where('email', 'admin@hospital.test')->first();

        $response = $this->actingAs($admin)->post('/patients', [
            'first_name' => 'Michael',
            'last_name' => 'Chang',
            'dob' => '1992-06-15',
            'gender' => 'Male',
            'blood_type' => 'O+',
            'phone' => '+1 (555) 777-8899',
            'email' => 'michael.chang@example.com',
            'contact_name' => 'Linda Chang',
            'relationship' => 'Spouse',
            'contact_phone' => '+1 (555) 777-8800'
        ]);

        $response->assertRedirect();
        $patient = Patient::where('email', 'michael.chang@example.com')->first();
        $this->assertNotNull($patient);
        $this->assertStringStartsWith('PAT-', $patient->patient_code);
    }

    public function test_appointment_booking_and_consultation()
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $patient = Patient::first();
        $doctor = Doctor::first();

        $response = $this->actingAs($admin)->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'department_id' => $doctor->department_id,
            'appointment_date' => date('Y-m-d', strtotime('+1 day')),
            'time_slot' => '10:30 AM',
            'reason' => 'Annual cardiology physical examination',
            'generate_invoice' => '1'
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'time_slot' => '10:30 AM'
        ]);
    }

    public function test_clinical_emr_and_prescription_issuance()
    {
        $doctorUser = User::where('email', 'dr.sarah@hospital.test')->first();
        $patient = Patient::first();
        $medicine = Medicine::first();

        // 1. Record EMR
        $response = $this->actingAs($doctorUser)->post('/medical-records', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->doctor->id,
            'visit_date' => date('Y-m-d H:i:s'),
            'diagnosis' => 'Moderate Essential Hypertension',
            'symptoms' => 'Occasional headache, elevated blood pressure',
            'notes' => 'SOAP: Subjective headache. Objective BP 145/90. Assessment: Stage 1 HTN. Plan: ACE inhibitor.',
            'vital_bp' => '145/90 mmHg',
            'vital_hr' => '78 bpm',
            'vital_spo2' => '99%',
            'vital_temp' => '98.4 °F',
            'vital_resp' => '16 /min',
        ]);
        $response->assertRedirect();

        // 2. Issue Prescription
        $response = $this->actingAs($doctorUser)->post('/prescriptions', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctorUser->doctor->id,
            'prescribed_date' => date('Y-m-d'),
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'dosage' => '10mg',
                    'frequency' => 'Once daily morning',
                    'duration_days' => 30,
                    'quantity_prescribed' => 30,
                    'instructions' => 'Take with water before breakfast'
                ]
            ],
            'notes' => 'Avoid excessive sodium.'
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('prescriptions', [
            'patient_id' => $patient->id,
            'status' => 'active'
        ]);
    }

    public function test_billing_invoice_and_payment_collection()
    {
        $admin = User::where('email', 'admin@hospital.test')->first();
        $patient = Patient::first();

        // Create Invoice
        $response = $this->actingAs($admin)->post('/billing', [
            'patient_id' => $patient->id,
            'invoice_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+7 days')),
            'discount_amount' => 10.00,
            'tax_amount' => 5.00,
            'items' => [
                [
                    'item_description' => 'Comprehensive Outpatient Consultation',
                    'quantity' => 1,
                    'unit_price' => 100.00
                ]
            ]
        ]);
        $response->assertRedirect();
        $invoice = Invoice::where('patient_id', $patient->id)->latest()->first();
        $this->assertNotNull($invoice);

        // Collect Payment
        $response = $this->actingAs($admin)->post('/billing/' . $invoice->id . '/pay', [
            'amount_paid' => $invoice->net_amount,
            'payment_method' => 'credit_card',
            'transaction_reference' => 'TXN-TEST-9988'
        ]);
        $response->assertRedirect();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->balance_due);
    }
}
