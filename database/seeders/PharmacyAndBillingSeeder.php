<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Medicine;
use App\Models\LabTest;
use App\Models\LabReport;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Admission;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\Message;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Models\User;

class PharmacyAndBillingSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Medicines Catalog
        $medicines = [
            ['name' => 'Augmentin 625mg', 'generic_name' => 'Amoxicillin + Clavulanic Acid', 'category' => 'Antibiotic', 'unit_price' => 18.50, 'stock_quantity' => 150, 'reorder_level' => 30, 'expiry_date' => '2027-06-30'],
            ['name' => 'Lipitor 20mg', 'generic_name' => 'Atorvastatin Calcium', 'category' => 'Cardiovascular', 'unit_price' => 24.00, 'stock_quantity' => 220, 'reorder_level' => 40, 'expiry_date' => '2028-01-15'],
            ['name' => 'Glucophage 500mg', 'generic_name' => 'Metformin Hydrochloride', 'category' => 'Antidiabetic', 'unit_price' => 12.00, 'stock_quantity' => 300, 'reorder_level' => 50, 'expiry_date' => '2027-11-20'],
            ['name' => 'Norvasc 5mg', 'generic_name' => 'Amlodipine Besylate', 'category' => 'Antihypertensive', 'unit_price' => 15.00, 'stock_quantity' => 180, 'reorder_level' => 30, 'expiry_date' => '2027-09-10'],
            ['name' => 'Tylenol Extra 500mg', 'generic_name' => 'Paracetamol / Acetaminophen', 'category' => 'Analgesic', 'unit_price' => 8.00, 'stock_quantity' => 500, 'reorder_level' => 50, 'expiry_date' => '2028-05-01'],
            ['name' => 'Prilosec 20mg', 'generic_name' => 'Omeprazole Delayed-Release', 'category' => 'Gastrointestinal', 'unit_price' => 16.50, 'stock_quantity' => 120, 'reorder_level' => 25, 'expiry_date' => '2027-08-18'],
            ['name' => 'Zithromax 500mg', 'generic_name' => 'Azithromycin Dihydrate', 'category' => 'Antibiotic', 'unit_price' => 28.00, 'stock_quantity' => 8, 'reorder_level' => 20, 'expiry_date' => '2026-12-31'], // Low stock trigger
            ['name' => 'Ventolin HFA 100mcg', 'generic_name' => 'Salbutamol Inhalation Aerosol', 'category' => 'Respiratory', 'unit_price' => 35.00, 'stock_quantity' => 45, 'reorder_level' => 15, 'expiry_date' => '2027-10-05'],
            ['name' => 'Rocephin 1g Vial', 'generic_name' => 'Ceftriaxone Sodium Injection', 'category' => 'Antibiotic', 'unit_price' => 42.00, 'stock_quantity' => 60, 'reorder_level' => 15, 'expiry_date' => '2027-03-25'],
            ['name' => 'Advil 400mg', 'generic_name' => 'Ibuprofen Liqui-Gels', 'category' => 'NSAID', 'unit_price' => 9.50, 'stock_quantity' => 250, 'reorder_level' => 40, 'expiry_date' => '2028-02-14'],
            ['name' => 'Eliquis 5mg', 'generic_name' => 'Apixaban', 'category' => 'Cardiovascular', 'unit_price' => 65.00, 'stock_quantity' => 110, 'reorder_level' => 25, 'expiry_date' => '2028-04-10'],
            ['name' => 'Lyrica 75mg', 'generic_name' => 'Pregabalin', 'category' => 'Neurology', 'unit_price' => 38.00, 'stock_quantity' => 90, 'reorder_level' => 20, 'expiry_date' => '2027-12-15'],
            ['name' => 'Imitrex 50mg', 'generic_name' => 'Sumatriptan Succinate', 'category' => 'Neurology', 'unit_price' => 45.00, 'stock_quantity' => 70, 'reorder_level' => 15, 'expiry_date' => '2027-07-20'],
            ['name' => 'Synthroid 75mcg', 'generic_name' => 'Levothyroxine Sodium', 'category' => 'Endocrinology', 'unit_price' => 14.00, 'stock_quantity' => 210, 'reorder_level' => 35, 'expiry_date' => '2028-06-30'],
            ['name' => 'Plavix 75mg', 'generic_name' => 'Clopidogrel Bisulfate', 'category' => 'Cardiovascular', 'unit_price' => 29.50, 'stock_quantity' => 12, 'reorder_level' => 30, 'expiry_date' => '2027-05-15'], // Low stock trigger
        ];

        $medModels = [];
        foreach ($medicines as $med) {
            $medModels[$med['name']] = Medicine::updateOrCreate(
                ['name' => $med['name']],
                $med
            );
        }

        // 2. Lab Tests Catalog
        $labTests = [
            ['test_name' => 'Complete Blood Count (CBC)', 'code' => 'LAB-CBC', 'description' => 'Evaluates overall health and detects wide range of disorders including anemia, infection and leukemia.', 'cost' => 35.00],
            ['test_name' => 'Comprehensive Metabolic Panel (CMP)', 'code' => 'LAB-CMP', 'description' => 'Measures 14 substances in blood for kidney/liver status, electrolyte and fluid balance.', 'cost' => 55.00],
            ['test_name' => 'Lipid Profile Panel', 'code' => 'LAB-LIPID', 'description' => 'Measures Total Cholesterol, HDL, LDL, and Triglycerides to assess cardiovascular risk.', 'cost' => 45.00],
            ['test_name' => '12-Lead Electrocardiogram (ECG)', 'code' => 'RAD-ECG', 'description' => 'Records electrical signals from the heart to detect arrhythmias, ischemia, and infarction.', 'cost' => 60.00],
            ['test_name' => 'Digital Chest X-Ray (PA View)', 'code' => 'RAD-CXR', 'description' => 'High-resolution radiograph of chest cavity, cardiac silhouette, and lung fields.', 'cost' => 80.00],
            ['test_name' => 'MRI Brain with Contrast', 'code' => 'RAD-MRI-BR', 'description' => 'Magnetic Resonance Imaging of cranial structures for neurological diagnostics.', 'cost' => 450.00],
            ['test_name' => 'MRI Lumbar Spine', 'code' => 'RAD-MRI-LSP', 'description' => 'Detailed imaging of lumbar intervertebral discs, spinal canal, and nerve roots.', 'cost' => 420.00],
            ['test_name' => 'HbA1c Glycated Hemoglobin', 'code' => 'LAB-HBA1C', 'description' => 'Assesses 3-month average plasma glucose concentration for diabetes management.', 'cost' => 40.00],
            ['test_name' => 'High-Sensitivity Cardiac Troponin I', 'code' => 'LAB-TROP-I', 'description' => 'Gold-standard biomarker for myocardial injury and acute coronary syndromes.', 'cost' => 75.00],
            ['test_name' => 'Urinalysis with Microscopic Examination', 'code' => 'LAB-UA', 'description' => 'Screening for urinary tract infections, kidney disease, and diabetes mellitus.', 'cost' => 25.00],
        ];

        $testModels = [];
        foreach ($labTests as $t) {
            $testModels[$t['code']] = LabTest::updateOrCreate(
                ['code' => $t['code']],
                $t
            );
        }

        // Fetch Seed entities
        $patJohn = Patient::where('patient_code', 'PAT-2026-0001')->first();
        $patEmma = Patient::where('patient_code', 'PAT-2026-0002')->first();
        $patRobert = Patient::where('patient_code', 'PAT-2026-0003')->first();
        $patWilliam = Patient::where('patient_code', 'PAT-2026-0005')->first();
        $patDavid = Patient::where('patient_code', 'PAT-2026-0007')->first();
        $patJames = Patient::where('patient_code', 'PAT-2026-0009')->first();

        $drSarah = Doctor::first();
        $drJames = Doctor::skip(1)->first() ?? $drSarah;
        $drRobert = Doctor::skip(3)->first() ?? $drSarah;

        $mrJohn = MedicalRecord::where('patient_id', $patJohn?->id)->first();
        $mrEmma = MedicalRecord::where('patient_id', $patEmma?->id)->first();
        $mrRobert = MedicalRecord::where('patient_id', $patRobert?->id)->first();
        $mrDavid = MedicalRecord::where('patient_id', $patDavid?->id)->first();
        $mrJames = MedicalRecord::where('patient_id', $patJames?->id)->first();

        $labTech = User::where('username', 'labtech')->first();

        // 3. Prescriptions
        // Prescription for Johnathan Doe
        if ($patJohn && $drSarah && $mrJohn) {
            $rx1 = Prescription::updateOrCreate(
                ['medical_record_id' => $mrJohn->id],
                [
                    'patient_id' => $patJohn->id,
                    'doctor_id' => $drSarah->id,
                    'prescribed_date' => today()->subDays(14)->toDateString(),
                    'notes' => 'Take Atorvastatin daily at bedtime. Continue low-sodium dietary modifications.',
                    'status' => 'dispensed',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx1->id, 'medicine_id' => $medModels['Lipitor 20mg']->id],
                [
                    'dosage' => '20mg',
                    'frequency' => 'Once daily (Night)',
                    'duration_days' => 30,
                    'quantity_prescribed' => 30,
                    'instructions' => 'Take 1 tablet every evening after dinner with water.',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx1->id, 'medicine_id' => $medModels['Norvasc 5mg']->id],
                [
                    'dosage' => '5mg',
                    'frequency' => 'Once daily (Morning)',
                    'duration_days' => 30,
                    'quantity_prescribed' => 30,
                    'instructions' => 'Take 1 tablet every morning with breakfast.',
                ]
            );
        }

        // Prescription for Emma Watson
        if ($patEmma && $drJames && $mrEmma) {
            $rx2 = Prescription::updateOrCreate(
                ['medical_record_id' => $mrEmma->id],
                [
                    'patient_id' => $patEmma->id,
                    'doctor_id' => $drJames->id,
                    'prescribed_date' => today()->subDays(10)->toDateString(),
                    'notes' => 'Use Sumatriptan at first onset of migraine aura. Do not exceed 200mg in 24 hours.',
                    'status' => 'active',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx2->id, 'medicine_id' => $medModels['Imitrex 50mg']->id],
                [
                    'dosage' => '50mg',
                    'frequency' => 'As needed for acute attack',
                    'duration_days' => 30,
                    'quantity_prescribed' => 6,
                    'instructions' => 'Take 1 tablet at onset of headache. May repeat in 2 hours if headache recurs.',
                ]
            );
        }

        // Prescription for Robert Brown (Inpatient)
        if ($patRobert && $drSarah && $mrRobert) {
            $rx3 = Prescription::updateOrCreate(
                ['medical_record_id' => $mrRobert->id],
                [
                    'patient_id' => $patRobert->id,
                    'doctor_id' => $drSarah->id,
                    'prescribed_date' => today()->subDays(3)->toDateString(),
                    'notes' => 'Inpatient telemetry acute NSTEMI regimen: DAPT + Statin + Glycemic control.',
                    'status' => 'dispensed',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx3->id, 'medicine_id' => $medModels['Plavix 75mg']->id],
                [
                    'dosage' => '75mg',
                    'frequency' => 'Once daily',
                    'duration_days' => 30,
                    'quantity_prescribed' => 30,
                    'instructions' => 'Take 1 tablet daily with or without food.',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx3->id, 'medicine_id' => $medModels['Glucophage 500mg']->id],
                [
                    'dosage' => '500mg',
                    'frequency' => 'Twice daily with meals',
                    'duration_days' => 30,
                    'quantity_prescribed' => 60,
                    'instructions' => 'Take 1 tablet with breakfast and 1 with dinner.',
                ]
            );
        }

        // Prescription for James Rodriguez
        if ($patJames && $drRobert && $mrJames) {
            $rx4 = Prescription::updateOrCreate(
                ['medical_record_id' => $mrJames->id],
                [
                    'patient_id' => $patJames->id,
                    'doctor_id' => $drRobert->id,
                    'prescribed_date' => today()->subDays(4)->toDateString(),
                    'notes' => 'Neuropathic pain relief for L5 radiculopathy.',
                    'status' => 'active',
                ]
            );

            PrescriptionItem::updateOrCreate(
                ['prescription_id' => $rx4->id, 'medicine_id' => $medModels['Lyrica 75mg']->id],
                [
                    'dosage' => '75mg',
                    'frequency' => 'Once daily at bedtime',
                    'duration_days' => 14,
                    'quantity_prescribed' => 14,
                    'instructions' => 'Take 1 capsule at bedtime. May cause drowsiness.',
                ]
            );
        }

        // 4. Lab Reports
        if ($patJohn && $drSarah && $mrJohn) {
            LabReport::updateOrCreate(
                ['patient_id' => $patJohn->id, 'lab_test_id' => $testModels['LAB-LIPID']->id],
                [
                    'medical_record_id' => $mrJohn->id,
                    'doctor_id' => $drSarah->id,
                    'technician_id' => $labTech ? $labTech->id : null,
                    'status' => 'completed',
                    'result_summary' => 'Total Cholesterol: 215 mg/dL (Borderline High), HDL: 48 mg/dL (Optimal >40), LDL: 135 mg/dL (Elevated, Target <100), Triglycerides: 160 mg/dL (Borderline High).',
                    'file_path' => null,
                    'report_date' => today()->subDays(12)->setTime(15, 0),
                ]
            );

            LabReport::updateOrCreate(
                ['patient_id' => $patJohn->id, 'lab_test_id' => $testModels['RAD-ECG']->id],
                [
                    'medical_record_id' => $mrJohn->id,
                    'doctor_id' => $drSarah->id,
                    'technician_id' => $labTech ? $labTech->id : null,
                    'status' => 'completed',
                    'result_summary' => 'Normal sinus rhythm, HR 72 bpm. PR interval 160ms, QRS 88ms, QTc 415ms. Normal axis (+60°). No ST-segment elevation, pathological Q waves, or T-wave inversions.',
                    'file_path' => null,
                    'report_date' => today()->subDays(13)->setTime(10, 30),
                ]
            );
        }

        if ($patRobert && $drSarah && $mrRobert) {
            LabReport::updateOrCreate(
                ['patient_id' => $patRobert->id, 'lab_test_id' => $testModels['LAB-TROP-I']->id],
                [
                    'medical_record_id' => $mrRobert->id,
                    'doctor_id' => $drSarah->id,
                    'technician_id' => $labTech ? $labTech->id : null,
                    'status' => 'completed',
                    'result_summary' => 'High-Sensitivity Troponin I: 1450 ng/L (Ref: <14 ng/L). Significant myocardial necrosis marker elevation consistent with acute NSTEMI.',
                    'file_path' => null,
                    'report_date' => today()->subDays(3)->setTime(9, 15),
                ]
            );

            LabReport::updateOrCreate(
                ['patient_id' => $patRobert->id, 'lab_test_id' => $testModels['RAD-CXR']->id],
                [
                    'medical_record_id' => $mrRobert->id,
                    'doctor_id' => $drSarah->id,
                    'technician_id' => $labTech ? $labTech->id : null,
                    'status' => 'completed',
                    'result_summary' => 'PA Chest View: Normal cardiothoracic ratio (<0.50). Lung fields clear with no focal consolidation, pneumothorax, or pleural effusion. Mediastinal contours unremarkable.',
                    'file_path' => null,
                    'report_date' => today()->subDays(3)->setTime(10, 0),
                ]
            );
        }

        if ($patJames && $drRobert && $mrJames) {
            LabReport::updateOrCreate(
                ['patient_id' => $patJames->id, 'lab_test_id' => $testModels['RAD-MRI-LSP']->id],
                [
                    'medical_record_id' => $mrJames->id,
                    'doctor_id' => $drRobert->id,
                    'technician_id' => $labTech ? $labTech->id : null,
                    'status' => 'in_progress',
                    'result_summary' => 'MRI Lumbar Spine scan acquisition completed. Senior Radiologist review in progress.',
                    'file_path' => null,
                    'report_date' => null,
                ]
            );
        }

        // 5. Invoices & Payments
        // Invoice 1 for Johnathan Doe
        if ($patJohn && $mrJohn) {
            $inv1 = Invoice::updateOrCreate(
                ['invoice_number' => 'INV-2026-0001'],
                [
                    'patient_id' => $patJohn->id,
                    'appointment_id' => $mrJohn->appointment_id,
                    'invoice_date' => today()->subDays(14)->toDateString(),
                    'due_date' => today()->subDays(7)->toDateString(),
                    'total_amount' => 225.00,
                    'discount_amount' => 0.00,
                    'tax_amount' => 11.25,
                    'net_amount' => 236.25,
                    'status' => 'paid',
                    'notes' => 'Cardiology Consultation + Lipid Profile + 12-Lead ECG Package.',
                ]
            );

            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv1->id, 'item_description' => 'Doctor Consultation - Dr. Sarah Jenkins (Cardiology)'],
                ['quantity' => 1, 'unit_price' => 120.00, 'subtotal' => 120.00]
            );
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv1->id, 'item_description' => 'Diagnostic Lab: Lipid Profile Panel'],
                ['quantity' => 1, 'unit_price' => 45.00, 'subtotal' => 45.00]
            );
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv1->id, 'item_description' => 'Diagnostic: 12-Lead Electrocardiogram (ECG)'],
                ['quantity' => 1, 'unit_price' => 60.00, 'subtotal' => 60.00]
            );

            Payment::updateOrCreate(
                ['payment_number' => 'PAY-2026-0001'],
                [
                    'invoice_id' => $inv1->id,
                    'payment_date' => today()->subDays(14)->setTime(12, 15),
                    'amount_paid' => 236.25,
                    'payment_method' => 'credit_card',
                    'transaction_reference' => 'TXN-VISA-904812',
                    'notes' => 'Settled in full via contactless Visa card at front desk.',
                    'status' => 'completed',
                ]
            );
        }

        // Invoice 2 for Robert Brown (Inpatient ICU)
        $admRobert = Admission::where('patient_id', $patRobert?->id)->first();
        if ($patRobert && $admRobert) {
            $inv2 = Invoice::updateOrCreate(
                ['invoice_number' => 'INV-2026-0002'],
                [
                    'patient_id' => $patRobert->id,
                    'admission_id' => $admRobert->id,
                    'invoice_date' => today()->subDays(1)->toDateString(),
                    'due_date' => today()->addDays(7)->toDateString(),
                    'total_amount' => 1575.00,
                    'discount_amount' => 100.00,
                    'tax_amount' => 73.75,
                    'net_amount' => 1548.75,
                    'status' => 'partially_paid',
                    'notes' => 'Inpatient ICU 101 telemetry care, Troponin I assay, Digital Chest X-Ray.',
                ]
            );

            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv2->id, 'item_description' => 'ICU Room 101 - Bed B1 [3 Days @ $450.00/day]'],
                ['quantity' => 3, 'unit_price' => 450.00, 'subtotal' => 1350.00]
            );
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv2->id, 'item_description' => 'Diagnostic Lab: High-Sensitivity Troponin I'],
                ['quantity' => 1, 'unit_price' => 75.00, 'subtotal' => 75.00]
            );
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv2->id, 'item_description' => 'Diagnostic Radiology: Digital Chest X-Ray (PA)'],
                ['quantity' => 1, 'unit_price' => 80.00, 'subtotal' => 80.00]
            );
            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv2->id, 'item_description' => 'Attending Physician Specialist Rounding'],
                ['quantity' => 1, 'unit_price' => 70.00, 'subtotal' => 70.00]
            );

            Payment::updateOrCreate(
                ['payment_number' => 'PAY-2026-0002'],
                [
                    'invoice_id' => $inv2->id,
                    'payment_date' => today()->subDays(1)->setTime(16, 0),
                    'amount_paid' => 1000.00,
                    'payment_method' => 'insurance',
                    'transaction_reference' => 'INS-BLUECROSS-CLM-88421',
                    'notes' => 'BlueCross Primary Insurance pre-authorized advance payment.',
                    'status' => 'completed',
                ]
            );
        }

        // Invoice 3 for Emma Watson
        if ($patEmma && $mrEmma) {
            $inv3 = Invoice::updateOrCreate(
                ['invoice_number' => 'INV-2026-0003'],
                [
                    'patient_id' => $patEmma->id,
                    'appointment_id' => $mrEmma->appointment_id,
                    'invoice_date' => today()->subDays(10)->toDateString(),
                    'due_date' => today()->addDays(4)->toDateString(),
                    'total_amount' => 150.00,
                    'discount_amount' => 0.00,
                    'tax_amount' => 7.50,
                    'net_amount' => 157.50,
                    'status' => 'unpaid',
                    'notes' => 'Neurology Specialist Consultation - Dr. James Wilson.',
                ]
            );

            InvoiceItem::updateOrCreate(
                ['invoice_id' => $inv3->id, 'item_description' => 'Neurology Specialist Consultation - Dr. James Wilson'],
                ['quantity' => 1, 'unit_price' => 150.00, 'subtotal' => 150.00]
            );
        }

        // 6. System Settings
        $settings = [
            'hospital_name' => 'Apex Horizon International Medical Center',
            'hospital_phone' => '+1 (800) 555-APEX / +1 (555) 019-9000',
            'hospital_email' => 'contact@apexmedical.test',
            'hospital_address' => '500 Health Sciences Blvd, Medical District, Boston MA 02115',
            'tax_rate_percent' => '5.0',
            'currency_symbol' => '$',
            'appointment_slot_duration_minutes' => '30',
            'emergency_contact_number' => '911 / +1 (555) 911-0000',
        ];

        foreach ($settings as $key => $val) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $val, 'description' => ucwords(str_replace('_', ' ', $key))]
            );
        }

        // 7. Notifications
        $superadminUser = User::where('username', 'superadmin')->first();
        $doctorUser = User::where('username', 'dr_sarah')->first();
        $patientUser = User::where('username', 'patient_john')->first();
        $nurseUser = User::where('username', 'nurse')->first();

        if ($superadminUser) {
            Notification::updateOrCreate(
                ['user_id' => $superadminUser->id, 'title' => 'Daily System & Audit Backup Verified'],
                [
                    'message' => 'Automated relational database integrity check and RBAC compliance stream verified.',
                    'type' => 'system',
                    'is_read' => false,
                ]
            );
            Notification::updateOrCreate(
                ['user_id' => $superadminUser->id, 'title' => 'Low Stock Warning: Zithromax 500mg'],
                [
                    'message' => 'Pharmacy inventory for Zithromax 500mg has reached 8 units (Threshold: 20 units).',
                    'type' => 'system',
                    'is_read' => false,
                ]
            );
            Notification::updateOrCreate(
                ['user_id' => $superadminUser->id, 'title' => 'Low Stock Warning: Plavix 75mg'],
                [
                    'message' => 'Pharmacy inventory for Plavix 75mg has reached 12 units (Threshold: 30 units).',
                    'type' => 'system',
                    'is_read' => false,
                ]
            );
        }

        if ($doctorUser) {
            Notification::updateOrCreate(
                ['user_id' => $doctorUser->id, 'title' => 'Clinical Queue Ready: 5 Consultations Today'],
                [
                    'message' => 'You have 5 scheduled patient appointments today in Cardiology Outpatient Clinic.',
                    'type' => 'appointment',
                    'is_read' => false,
                ]
            );
            Notification::updateOrCreate(
                ['user_id' => $doctorUser->id, 'title' => 'Critical Lab Result Published: Troponin I'],
                [
                    'message' => 'Troponin I lab report for Inpatient Robert Brown (ICU-101) has been published: 1450 ng/L.',
                    'type' => 'lab_result',
                    'is_read' => false,
                ]
            );
        }

        if ($patientUser) {
            Notification::updateOrCreate(
                ['user_id' => $patientUser->id, 'title' => 'Diagnostic Results Ready: Lipid Profile & ECG'],
                [
                    'message' => 'Your diagnostic lab results for Lipid Profile Panel and 12-lead ECG are verified and ready in your portal.',
                    'type' => 'lab_result',
                    'is_read' => false,
                ]
            );
            Notification::updateOrCreate(
                ['user_id' => $patientUser->id, 'title' => 'Appointment Reminder: Today at 09:30 AM'],
                [
                    'message' => 'Your cardiology follow-up consultation with Dr. Sarah Jenkins is scheduled for today at 09:30 AM.',
                    'type' => 'appointment',
                    'is_read' => false,
                ]
            );
        }

        // 8. Internal 2-Way Chat Messages
        if ($doctorUser && $superadminUser) {
            Message::updateOrCreate(
                ['sender_id' => $doctorUser->id, 'receiver_id' => $superadminUser->id],
                [
                    'message_body' => 'Good morning Dr. Vance. Telemetry monitoring equipment in ICU-101 has been calibrated for Patient Robert Brown.',
                    'sent_at' => today()->setTime(8, 45),
                    'is_read' => true,
                ]
            );
            Message::updateOrCreate(
                ['sender_id' => $superadminUser->id, 'receiver_id' => $doctorUser->id],
                [
                    'message_body' => 'Thank you Dr. Sarah. The clinical operations team is briefed on the telemetry monitoring protocol.',
                    'sent_at' => today()->setTime(9, 10),
                    'is_read' => false,
                ]
            );
        }

        if ($nurseUser && $doctorUser) {
            Message::updateOrCreate(
                ['sender_id' => $nurseUser->id, 'receiver_id' => $doctorUser->id],
                [
                    'message_body' => 'Dr. Jenkins, morning vital signs for Robert Brown in ICU-101 have been recorded: BP 152/94, HR 92 bpm, SpO2 95% on 2L.',
                    'sent_at' => today()->setTime(8, 30),
                    'is_read' => true,
                ]
            );
        }

        // 9. Audit Logs
        $auditSamples = [
            ['action' => 'LOGIN', 'table' => 'users', 'rec' => $superadminUser?->id, 'user' => $superadminUser, 'det' => 'Superadmin master authentication from local gateway', 'mins' => 45],
            ['action' => 'CREATE', 'table' => 'medical_records', 'rec' => $mrJohn?->id, 'user' => $doctorUser, 'det' => 'Created EMR consultation record for Patient PAT-2026-0001', 'mins' => 30],
            ['action' => 'ADMIT', 'table' => 'admissions', 'rec' => $admRobert?->id, 'user' => $superadminUser, 'det' => 'Admitted Patient Robert Brown to ICU-101 Bed B1', 'mins' => 20],
            ['action' => 'DISPENSE', 'table' => 'prescriptions', 'rec' => $rx1?->id, 'user' => User::where('username', 'pharmacist')->first(), 'det' => 'Dispensed medications for Prescription #1 (Atorvastatin, Amlodipine)', 'mins' => 15],
            ['action' => 'PAYMENT', 'table' => 'payments', 'rec' => 1, 'user' => User::where('username', 'cashier')->first(), 'det' => 'Collected $236.25 payment via Visa for Invoice INV-2026-0001', 'mins' => 10],
        ];

        foreach ($auditSamples as $as) {
            AuditLog::create([
                'user_id' => $as['user']?->id,
                'action' => $as['action'],
                'table_name' => $as['table'],
                'record_id' => $as['rec'],
                'ip_address' => '127.0.0.1',
                'details' => $as['det'],
                'created_at' => now()->subMinutes($as['mins']),
            ]);
        }
    }
}
