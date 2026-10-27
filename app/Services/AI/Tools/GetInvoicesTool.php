<?php

namespace App\Services\AI\Tools;

use App\Models\Invoice;
use App\Models\User;
use App\Services\AI\Contracts\ToolInterface;
use App\Services\AI\Security\RelationshipGuard;
use Exception;

class GetInvoicesTool implements ToolInterface
{
    public function name(): string
    {
        return 'get_invoices';
    }

    public function description(): string
    {
        return 'Retrieve hospital billing ledgers, outstanding balances, itemized breakdown (consultations, room stay, lab fees, medications), payment status (unpaid, checking, paid), and contactless QR payment links.';
    }

    public function parameters(): array
    {
        return [
            'type' => 'OBJECT',
            'properties' => [
                'patient_id' => [
                    'type' => 'INTEGER',
                    'description' => 'Target patient ID (applicable for staff/cashiers; discarded for patients to enforce zero-trust self-isolation).',
                ],
                'status' => [
                    'type' => 'STRING',
                    'description' => 'Filter by payment status: "unpaid", "checking", "paid", "partially_paid", or "all". Default is "all".',
                ],
            ],
        ];
    }

    public function allowedRoles(): array
    {
        return ['patient', 'staff', 'admin', 'superadmin'];
    }

    public function execute(array $arguments, User $authenticatedUser): array
    {
        try {
            $query = Invoice::with(['items', 'patient', 'payments']);

            if ($authenticatedUser->isPatient()) {
                $patient = RelationshipGuard::resolveEnforcedPatient($authenticatedUser, null);
                $query->where('patient_id', $patient->id);
            } elseif (!empty($arguments['patient_id'])) {
                $query->where('patient_id', (int) $arguments['patient_id']);
            }

            $status = strtolower($arguments['status'] ?? 'all');
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $invoices = $query->orderBy('invoice_date', 'desc')->take(15)->get();

            if ($invoices->isEmpty()) {
                return [
                    'status' => 'success',
                    'count' => 0,
                    'total_outstanding' => '$0.00',
                    'message' => 'No invoices found matching the criteria.',
                    'invoices' => [],
                ];
            }

            $totalOutstanding = 0;

            $formatted = $invoices->map(function ($inv) use (&$totalOutstanding) {
                if (in_array($inv->status, ['unpaid', 'partially_paid', 'checking'])) {
                    $totalOutstanding += (float) $inv->net_amount;
                }

                return [
                    'invoice_id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'patient_name' => $inv->patient?->full_name ?? 'Patient',
                    'date' => $inv->invoice_date ? $inv->invoice_date->toDateString() : 'N/A',
                    'due_date' => $inv->due_date ? $inv->due_date->toDateString() : 'N/A',
                    'status' => $inv->status,
                    'subtotal' => '$' . number_format($inv->total_amount, 2),
                    'discount' => '$' . number_format($inv->discount_amount, 2),
                    'tax' => '$' . number_format($inv->tax_amount, 2),
                    'net_payable' => '$' . number_format($inv->net_amount, 2),
                    'qr_payment_url' => route('billing.qr-scan', ['id' => $inv->id]),
                    'line_items' => $inv->items->map(function ($item) {
                        return [
                            'item' => $item->description,
                            'quantity' => $item->quantity,
                            'unit_price' => '$' . number_format($item->unit_price, 2),
                            'total' => '$' . number_format($item->total_price, 2),
                        ];
                    })->all(),
                ];
            })->all();

            return [
                'status' => 'success',
                'count' => count($formatted),
                'total_outstanding' => '$' . number_format($totalOutstanding, 2),
                'invoices' => $formatted,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }
}
