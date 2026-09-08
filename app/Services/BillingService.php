<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Appointment;
use App\Models\Admission;
use App\Models\Prescription;
use App\Models\LabReport;
use Carbon\Carbon;

class BillingService
{
    /**
     * Generate a unique invoice number: INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ymd') . '-';
        $count = Invoice::whereDate('created_at', today())->count() + 1;
        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate an invoice for an appointment consultation
     */
    public static function createForAppointment(Appointment $appointment): Invoice
    {
        $doctor = $appointment->doctor;
        $fee = $doctor ? $doctor->consultation_fee : 50.00;

        $invoice = Invoice::create([
            'invoice_number' => self::generateInvoiceNumber(),
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'total_amount' => $fee,
            'discount_amount' => 0.00,
            'tax_amount' => round($fee * 0.05, 2), // 5% standard health service tax
            'net_amount' => $fee + round($fee * 0.05, 2),
            'status' => 'unpaid',
            'notes' => "Consultation with " . ($doctor ? $doctor->full_name : 'Doctor') . " - Ref #APT-{$appointment->id}",
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_description' => "Doctor Consultation Fee - " . ($doctor ? $doctor->full_name : 'Specialist') . " ({$doctor->specialization})",
            'quantity' => 1,
            'unit_price' => $fee,
            'subtotal' => $fee,
        ]);

        AuditService::log('CREATE', 'invoices', $invoice->id, "Generated consultation invoice #{$invoice->invoice_number}");

        return $invoice;
    }

    /**
     * Generate an invoice for a hospital admission & stay
     */
    public static function createForAdmission(Admission $admission): Invoice
    {
        $room = $admission->bed->room;
        $rate = $room->daily_rate;
        $days = $admission->stay_days;
        $roomSubtotal = $rate * $days;

        $invoice = Invoice::create([
            'invoice_number' => self::generateInvoiceNumber(),
            'patient_id' => $admission->patient_id,
            'admission_id' => $admission->id,
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'total_amount' => $roomSubtotal,
            'discount_amount' => 0.00,
            'tax_amount' => round($roomSubtotal * 0.05, 2),
            'net_amount' => $roomSubtotal + round($roomSubtotal * 0.05, 2),
            'status' => 'unpaid',
            'notes' => "Inpatient Admission #ADM-{$admission->id} - Room {$room->room_number} ({$room->room_type})",
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_description' => "Room Charges: Room {$room->room_number} ({$room->room_type}) Bed #{$admission->bed->bed_number} [{$days} Day(s) @ \${$rate}/day]",
            'quantity' => $days,
            'unit_price' => $rate,
            'subtotal' => $roomSubtotal,
        ]);

        // Add attending doctor fee
        $doctorFee = $admission->doctor ? $admission->doctor->consultation_fee : 100.00;
        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_description' => "Inpatient Attending Physician Care - " . ($admission->doctor ? $admission->doctor->full_name : 'Physician'),
            'quantity' => 1,
            'unit_price' => $doctorFee,
            'subtotal' => $doctorFee,
        ]);

        $invoice->recalculateTotals();

        AuditService::log('CREATE', 'invoices', $invoice->id, "Generated admission invoice #{$invoice->invoice_number}");

        return $invoice;
    }
}
