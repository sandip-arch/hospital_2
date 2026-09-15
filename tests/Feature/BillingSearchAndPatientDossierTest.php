<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Patient;
use App\Models\Invoice;

class BillingSearchAndPatientDossierTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh --seed');
    }

    public function test_billing_page_search_and_filtration_system()
    {
        $receptionist = User::where('email', 'receptionist@hospital.test')->first();
        $this->assertNotNull($receptionist);

        // 1. Check general billing desk loads
        $response = $this->actingAs($receptionist)->get('/billing');
        $response->assertStatus(200);
        $response->assertSee('Financial Transaction', false);
        $response->assertSee('All Invoices');
        $response->assertSee('Unpaid');
        $response->assertSee('Under Verification');
        $response->assertSee('Fully Settled');

        // 2. Test status filter
        $responseUnpaid = $this->actingAs($receptionist)->get('/billing?status=unpaid');
        $responseUnpaid->assertStatus(200);
        $responseUnpaid->assertSee('Status: Unpaid');

        $responsePaid = $this->actingAs($receptionist)->get('/billing?status=paid');
        $responsePaid->assertStatus(200);
        $responsePaid->assertSee('Status: Paid');

        // 3. Test patient full name search (CONCAT first_name and last_name)
        $patient = Patient::first();
        $this->assertNotNull($patient);
        $fullName = $patient->full_name;

        $responseSearch = $this->actingAs($receptionist)->get('/billing?search=' . urlencode($fullName));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee($patient->first_name);

        // 4. Test invoice number search
        $invoice = Invoice::first();
        if ($invoice) {
            $responseInv = $this->actingAs($receptionist)->get('/billing?search=' . urlencode($invoice->invoice_number));
            $responseInv->assertStatus(200);
            $responseInv->assertSee($invoice->invoice_number);
        }
    }

    public function test_patient_billing_and_transaction_history_dossier()
    {
        $receptionist = User::where('email', 'receptionist@hospital.test')->first();
        $this->assertNotNull($receptionist);

        $patient = Patient::with('invoices.payments')->first();
        $this->assertNotNull($patient);

        // Access patient profile with tab=billing and from=billing
        $response = $this->actingAs($receptionist)->get("/patients/{$patient->id}?tab=billing&from=billing");
        $response->assertStatus(200);

        // Verify "Back to Billing Desk" link
        $response->assertSee('Back to Billing Desk');
        $response->assertSee('Patient Billing Dossier', false);

        // Verify financial metrics KPI cards
        $response->assertSee('Total Billed');
        $response->assertSee('Total Settled');
        $response->assertSee('Outstanding Balance');
        $response->assertSee('Pending Invoices');

        // Verify Status Filter Pills
        $response->assertSee('All Invoices');
        $response->assertSee('Paid');
        $response->assertSee('Unpaid');
        $response->assertSee('Partially Paid');
        $response->assertSee('Under Verification');
        $response->assertSee('Cancelled');

        // Verify Transaction History Section
        $response->assertSee('Payment & Transaction Receipts History', false);
    }
}
