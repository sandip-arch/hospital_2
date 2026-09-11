<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QrPaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $patientUser;
    protected Patient $patient;
    protected User $receptionistUser;
    protected User $adminUser;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Patient User & Patient Record
        $this->patientUser = User::firstOrCreate(
            ['email' => 'test.patient@hospital.test'],
            [
                'name' => 'John Doe',
                'username' => 'test_patient_john',
                'password' => bcrypt('password123'),
            ]
        );
        $patientRole = Role::firstOrCreate(['name' => 'patient'], ['display_name' => 'Patient']);
        if (!$this->patientUser->roles->contains($patientRole->id)) {
            $this->patientUser->roles()->attach($patientRole);
        }

        $this->patient = Patient::firstOrCreate(
            ['user_id' => $this->patientUser->id],
            [
                'patient_code' => 'PAT-TEST-001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'gender' => 'Male',
                'dob' => '1990-01-01',
                'phone' => '555-0101',
            ]
        );

        // 2. Create Receptionist User
        $this->receptionistUser = User::firstOrCreate(
            ['email' => 'test.receptionist@hospital.test'],
            [
                'name' => 'Front Desk Receptionist',
                'username' => 'test_receptionist',
                'password' => bcrypt('password123'),
            ]
        );
        $staffRole = Role::firstOrCreate(['name' => 'staff'], ['display_name' => 'Staff']);
        if (!$this->receptionistUser->roles->contains($staffRole->id)) {
            $this->receptionistUser->roles()->attach($staffRole);
        }
        $this->receptionistUser->staff()->firstOrCreate(
            ['user_id' => $this->receptionistUser->id],
            [
                'job_title' => 'Front Desk Receptionist',
                'phone' => '555-0202',
            ]
        );

        // 3. Create Admin User
        $this->adminUser = User::firstOrCreate(
            ['email' => 'test.admin@hospital.test'],
            [
                'name' => 'Hospital Admin',
                'username' => 'test_admin',
                'password' => bcrypt('password123'),
            ]
        );
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        if (!$this->adminUser->roles->contains($adminRole->id)) {
            $this->adminUser->roles()->attach($adminRole);
        }

        // 4. Create an Unpaid Invoice
        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-' . uniqid(),
            'patient_id' => $this->patient->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => 150.00,
            'discount_amount' => 0.00,
            'tax_amount' => 7.50,
            'net_amount' => 157.50,
            'status' => 'unpaid',
            'notes' => 'Test Invoice for Consultation',
        ]);

        InvoiceItem::create([
            'invoice_id' => $this->invoice->id,
            'item_description' => 'Doctor Consultation',
            'quantity' => 1,
            'unit_price' => 150.00,
            'subtotal' => 150.00,
        ]);
    }

    public function test_patient_can_scan_qr_and_switch_invoice_to_checking_state()
    {
        $response = $this->actingAs($this->patientUser)
            ->postJson(route('billing.qr-scan', $this->invoice->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'checking',
            ]);

        $this->invoice->refresh();
        $this->assertEquals('checking', $this->invoice->status);

        $pendingPayment = $this->invoice->latestPendingPayment();
        $this->assertNotNull($pendingPayment);
        $this->assertEquals('pending', $pendingPayment->status);
        $this->assertEquals(157.50, (float)$pendingPayment->amount_paid);

        // Verify notification sent to receptionist
        $notif = Notification::where('user_id', $this->receptionistUser->id)
            ->where('title', 'like', "%{$this->invoice->invoice_number}%")
            ->first();
        $this->assertNotNull($notif);
        $this->assertStringContainsString('submitted a $157.5 QR payment', $notif->message);
    }

    public function test_receptionist_can_decline_payment_with_reason_and_notify_patient()
    {
        // First scan
        $this->actingAs($this->patientUser)
            ->postJson(route('billing.qr-scan', $this->invoice->id));

        $this->invoice->refresh();
        $this->assertEquals('checking', $this->invoice->status);

        // Receptionist declines
        $response = $this->actingAs($this->receptionistUser)
            ->post(route('billing.verify', $this->invoice->id), [
                'action' => 'decline',
                'rejection_reason' => 'Transaction ID mismatch on hospital bank terminal.',
            ]);

        $response->assertSessionHas('error');

        $this->invoice->refresh();
        $this->assertEquals('unpaid', $this->invoice->status);

        $rejectedPayment = $this->invoice->latestRejectedPayment();
        $this->assertNotNull($rejectedPayment);
        $this->assertEquals('rejected', $rejectedPayment->status);
        $this->assertEquals('Transaction ID mismatch on hospital bank terminal.', $rejectedPayment->rejection_reason);

        // Verify patient received decline notification
        $patientNotif = Notification::where('user_id', $this->patientUser->id)
            ->where('title', 'like', "%Payment Declined%")
            ->first();
        $this->assertNotNull($patientNotif);
        $this->assertStringContainsString('Transaction ID mismatch on hospital bank terminal.', $patientNotif->message);
        $this->assertEquals(route('billing.show', $this->invoice->id), $patientNotif->target_url);
    }

    public function test_receptionist_can_approve_payment_and_mark_invoice_as_paid()
    {
        // Patient scans
        $this->actingAs($this->patientUser)
            ->postJson(route('billing.qr-scan', $this->invoice->id));

        $this->invoice->refresh();
        $this->assertEquals('checking', $this->invoice->status);

        // Receptionist approves
        $response = $this->actingAs($this->receptionistUser)
            ->post(route('billing.verify', $this->invoice->id), [
                'action' => 'approve',
            ]);

        $response->assertSessionHas('success');

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);

        $completedPayment = $this->invoice->payments()->where('status', 'completed')->first();
        $this->assertNotNull($completedPayment);
        $this->assertEquals(157.50, (float)$completedPayment->amount_paid);

        // Verify patient received approval notification
        $patientNotif = Notification::where('user_id', $this->patientUser->id)
            ->where('title', 'like', "%Payment Approved%")
            ->first();
        $this->assertNotNull($patientNotif);
        $this->assertStringContainsString('successfully approved', $patientNotif->message);
        $this->assertEquals(route('billing.show', $this->invoice->id), $patientNotif->target_url);
    }

    public function test_patient_is_forbidden_from_approving_their_own_payment()
    {
        // Patient scans
        $this->actingAs($this->patientUser)
            ->postJson(route('billing.qr-scan', $this->invoice->id));

        // Patient attempts to approve
        $response = $this->actingAs($this->patientUser)
            ->post(route('billing.verify', $this->invoice->id), [
                'action' => 'approve',
            ]);

        $response->assertStatus(403);
    }

    public function test_offline_counter_collection_dispatches_notification_to_patient()
    {
        $response = $this->actingAs($this->receptionistUser)
            ->post(route('billing.pay', $this->invoice->id), [
                'amount_paid' => 157.50,
                'payment_method' => 'cash',
                'transaction_reference' => 'RCPT-10029',
                'notes' => 'Paid cash at counter desk',
            ]);

        $response->assertSessionHas('success');

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);

        // Verify patient received notification
        $patientNotif = Notification::where('user_id', $this->patientUser->id)
            ->where('title', 'like', "%Payment Received%")
            ->first();
        $this->assertNotNull($patientNotif);
        $this->assertStringContainsString('offline counter (Cash)', $patientNotif->message);
        $this->assertStringContainsString('Front Desk Receptionist', $patientNotif->message);
        $this->assertEquals(route('billing.show', $this->invoice->id), $patientNotif->target_url);
    }
}
