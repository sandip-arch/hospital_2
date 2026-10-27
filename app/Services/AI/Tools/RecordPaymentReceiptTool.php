<?php

namespace App\Services\AI\Tools;

use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class RecordPaymentReceiptTool implements ToolInterface
{
    public function name(): string
    {
        return 'record_payment_receipt';
    }

    public function description(): string
    {
        return 'Billing Action: Record a payment receipt, transaction ID, or contactless QR payment submission for an invoice. Verifies payment status and notifies cashier.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'invoice_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target invoice ID.',
                ],
                'amount' => [
                    'type' => 'NUMBER',
                    'description' => 'Payment amount submitted.',
                ],
                'payment_method' => [
                    'type' => 'STRING',
                    'description' => 'Payment channel: "upi", "credit_card", "debit_card", "cash", "insurance", or "bank_transfer".',
                ],
                'transaction_reference' => [
                    'type' => 'STRING',
                    'description' => 'Unique transaction reference ID, UTR number, or payment gateway reference.',
                ],
            ],
            'required' => ['invoice_id', 'amount', 'payment_method', 'transaction_reference'],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        $invoiceId = (int) ($arguments['invoice_id'] ?? 0);
        $invoice = Invoice::with(['patient', 'payments'])->find($invoiceId);

        if (!$invoice) {
            return [
                'status' => 'error',
                'message' => "Invoice #{$invoiceId} not found.",
            ];
        }

        // Patient authorization check
        if ($authenticatedUser->isPatient()) {
            $patient = $authenticatedUser->patient;
            if ($patient && $invoice->patient_id !== $patient->id) {
                return [
                    'status' => 'error',
                    'message' => 'Access Denied: You cannot submit payments for invoices belonging to another individual.',
                ];
            }
        }

        $amount = (float) ($arguments['amount'] ?? 0);
        $method = strtolower($arguments['payment_method'] ?? 'upi');
        $txRef = trim($arguments['transaction_reference'] ?? ('TXN-' . strtoupper(substr(uniqid(), -8))));

        // If staff/admin is recording, mark 'completed' & 'paid'; if patient, mark 'pending' & 'checking'
        $isCashier = $authenticatedUser->canVerifyPayments();
        $paymentStatus = $isCashier ? 'completed' : 'pending';
        $invoiceStatus = $isCashier ? 'paid' : 'checking';

        DB::transaction(function () use ($invoice, $amount, $method, $txRef, $paymentStatus, $invoiceStatus) {
            Payment::create([
                'payment_number' => 'PAY-' . strtoupper(substr(uniqid(), -8)),
                'invoice_id' => $invoice->id,
                'payment_date' => Carbon::now(),
                'amount_paid' => $amount,
                'payment_method' => $method,
                'transaction_reference' => $txRef,
                'status' => $paymentStatus,
                'notes' => 'Submitted via AI Assistant',
            ]);

            $invoice->update(['status' => $invoiceStatus]);

            if ($invoice->patient?->user_id) {
                Notification::create([
                    'user_id' => $invoice->patient->user_id,
                    'title' => 'Payment Receipt Recorded',
                    'message' => "Payment of $" . number_format($amount, 2) . " for Invoice #{$invoice->invoice_number} is recorded with status '{$paymentStatus}'.",
                    'type' => 'billing',
                    'is_read' => false,
                ]);
            }
        });

        return [
            'status' => 'success',
            'message' => $isCashier
                ? "Payment of $" . number_format($amount, 2) . " recorded and verified. Invoice marked as PAID."
                : "Payment of $" . number_format($amount, 2) . " submitted with Reference: {$txRef}. Verification in progress by front desk.",
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'amount_paid' => '$' . number_format($amount, 2),
            'payment_status' => $paymentStatus,
            'invoice_status_now' => $invoiceStatus,
            'transaction_reference' => $txRef,
        ];
    }
}
