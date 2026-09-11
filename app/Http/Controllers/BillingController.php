<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Notification;
use App\Services\BillingService;
use App\Services\AuditService;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Invoice::with(['patient', 'payments', 'appointment.doctor.user']);

        if ($user->isPatient()) {
            if (!$user->patient) {
                $invoices = Invoice::whereRaw('1 = 0')->paginate(12);
                $stats = ['total_invoiced' => 0, 'total_collected' => 0, 'total_unpaid' => 0, 'unpaid_count' => 0];
                $consultantSpendings = collect();
                return view('billing.index', compact('invoices', 'stats', 'consultantSpendings'));
            }

            $patientId = $user->patient->id;
            $query->where('patient_id', $patientId);

            $stats = [
                'total_invoiced' => (float) Invoice::where('patient_id', $patientId)->sum('net_amount'),
                'total_collected' => (float) Payment::whereHas('invoice', fn($q) => $q->where('patient_id', $patientId))->where('status', 'completed')->sum('amount_paid'),
                'total_unpaid' => (float) Invoice::where('patient_id', $patientId)->where('status', 'unpaid')->sum('net_amount'),
                'unpaid_count' => Invoice::where('patient_id', $patientId)->where('status', 'unpaid')->count(),
            ];

            // Calculate spending grouped by doctor/consultant for this patient
            $consultantSpendings = Invoice::where('patient_id', $patientId)
                ->whereNotNull('appointment_id')
                ->with('appointment.doctor.user', 'appointment.department')
                ->get()
                ->groupBy(function ($inv) {
                    return $inv->appointment?->doctor?->user?->name ?? 'Hospital General / Facility';
                })
                ->map(function ($group) {
                    return [
                        'doctor_name' => $group->first()->appointment?->doctor?->user?->name ?? 'Hospital Care',
                        'department' => $group->first()->appointment?->department?->name ?? 'General Consultation',
                        'total_amount' => $group->sum('net_amount'),
                        'paid_amount' => $group->sum(fn($i) => $i->paid_amount),
                        'invoices_count' => $group->count(),
                    ];
                });
        } else {
            $stats = [
                'total_invoiced' => (float) Invoice::sum('net_amount'),
                'total_collected' => (float) Payment::where('status', 'completed')->sum('amount_paid'),
                'total_unpaid' => (float) Invoice::where('status', 'unpaid')->sum('net_amount'),
                'unpaid_count' => Invoice::where('status', 'unpaid')->count(),
            ];
            $consultantSpendings = collect();
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(12)->withQueryString();

        return view('billing.index', compact('invoices', 'stats', 'consultantSpendings'));
    }

    public function create()
    {
        if (Auth::user()->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot generate invoices directly.');
        }

        $patients = Patient::orderBy('first_name')->get();
        return view('billing.create', compact('patients'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot generate invoices directly.');
        }
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += $item['quantity'] * $item['unit_price'];
        }

        $discount = $validated['discount_amount'] ?? 0.00;
        $tax = $validated['tax_amount'] ?? 0.00;
        $net = max(0.00, $subtotal - $discount + $tax);

        $invoice = Invoice::create([
            'invoice_number' => BillingService::generateInvoiceNumber(),
            'patient_id' => $validated['patient_id'],
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'],
            'total_amount' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'net_amount' => $net,
            'status' => 'unpaid',
            'notes' => $validated['notes'] ?? null,
        ]);

        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_description' => $item['item_description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal' => $item['quantity'] * $item['unit_price'],
            ]);
        }

        AuditService::log('CREATE', 'invoices', $invoice->id, "Generated invoice #{$invoice->invoice_number} for \${$net}");

        return redirect()->route('billing.show', $invoice->id)->with('success', "Invoice #{$invoice->invoice_number} created successfully.");
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'patient.emergencyContacts',
            'admission.bed.room',
            'appointment.doctor.user',
            'items',
            'payments',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        return view('billing.show', compact('invoice'));
    }

    public function collectPayment(Request $request, $id)
    {
        $invoice = Invoice::with(['patient.user'])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized. You can only settle your own invoices.');
        }

        $validated = $request->validate([
            'amount_paid' => 'required|numeric|min:0.01|max:' . ($invoice->balance_due > 0 ? $invoice->balance_due : 100000),
            'payment_method' => 'required|in:cash,credit_card,debit_card,insurance,upi,bank_transfer',
            'transaction_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $count = Payment::whereDate('created_at', today())->count() + 1;
        $payNum = 'PAY-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'payment_number' => $payNum,
            'invoice_id' => $invoice->id,
            'payment_date' => now(),
            'amount_paid' => $validated['amount_paid'],
            'payment_method' => $validated['payment_method'],
            'transaction_reference' => $validated['transaction_reference'] ?? ('AUTO-' . uniqid()),
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed',
        ]);

        $invoice->recalculateTotals();

        // Notify patient that offline counter payment was collected
        if ($invoice->patient && $invoice->patient->user) {
            $nowFormatted = now()->format('M d, Y \a\t h:i A');
            $amountFormatted = number_format($validated['amount_paid'], 2);
            $methodName = ucfirst(str_replace('_', ' ', $validated['payment_method']));
            $collectorName = $user ? $user->name : 'Front Desk Cashier';

            Notification::create([
                'user_id' => $invoice->patient->user->id,
                'title' => "Payment Received: Invoice #{$invoice->invoice_number}",
                'message' => "Your payment of \${$amountFormatted} was successfully received via offline counter ({$methodName}) by {$collectorName} at {$nowFormatted}.",
                'type' => 'billing',
                'is_read' => false,
            ]);
        }

        AuditService::log('PAYMENT', 'payments', $payment->id, "Processed \${$validated['amount_paid']} payment via {$validated['payment_method']} for Invoice #{$invoice->invoice_number}");

        return back()->with('success', "Payment of \${$validated['amount_paid']} recorded successfully!");
    }

    public function printInvoice($id)
    {
        $invoice = Invoice::with([
            'patient',
            'items',
            'payments',
        ])->findOrFail($id);

        $user = Auth::user();
        if ($user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to this invoice receipt.');
        }

        return view('billing.print', compact('invoice'));
    }

    public function qrScanPayment(Request $request, $id)
    {
        $invoice = Invoice::with(['patient.user', 'payments'])->findOrFail($id);

        $user = Auth::user();
        if ($user && $user->isPatient() && $invoice->patient_id !== $user->patient?->id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            if ($request->wantsJson()) {
                return response()->json(['status' => $invoice->status, 'message' => 'Invoice already ' . $invoice->status]);
            }
            return redirect()->route('billing.show', $invoice->id)->with('info', "Invoice is already {$invoice->status}.");
        }

        $amount = $invoice->balance_due > 0 ? $invoice->balance_due : $invoice->net_amount;

        $pendingPayment = $invoice->latestPendingPayment();
        if (!$pendingPayment) {
            $count = Payment::whereDate('created_at', today())->count() + 1;
            $payNum = 'PAY-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $pendingPayment = Payment::create([
                'payment_number' => $payNum,
                'invoice_id' => $invoice->id,
                'payment_date' => now(),
                'amount_paid' => $amount,
                'payment_method' => 'upi',
                'transaction_reference' => 'QR-SCAN-' . strtoupper(substr(uniqid(), -6)),
                'notes' => 'Contactless demo QR payment submitted by patient. Verification required.',
                'status' => 'pending',
            ]);
        }

        $invoice->update(['status' => 'checking']);

        // Dispatch notifications to staff who can verify payments (Receptionist, Admin, Superadmin)
        $staffUsers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['superadmin', 'admin', 'staff']);
        })->get()->filter(fn($u) => $u->canVerifyPayments());

        $patientName = $invoice->patient ? $invoice->patient->full_name : 'Patient';

        foreach ($staffUsers as $staff) {
            Notification::create([
                'user_id' => $staff->id,
                'title' => "Payment Verification Needed: {$invoice->invoice_number}",
                'message' => "Patient {$patientName} submitted a \${$amount} QR payment for Invoice #{$invoice->invoice_number}. Please review and approve or decline.",
                'type' => 'billing',
                'is_read' => false,
            ]);
        }

        AuditService::log('PAYMENT', 'payments', $pendingPayment->id, "Patient scanned QR for Invoice #{$invoice->invoice_number}. Status changed to checking.");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => 'checking',
                'message' => 'QR scanned successfully. Verification request sent to front desk.',
                'payment' => $pendingPayment,
            ]);
        }

        return view('billing.qr-scanned-mobile', compact('invoice', 'pendingPayment'));
    }

    public function checkStatus($id)
    {
        $invoice = Invoice::select('id', 'status', 'total_amount', 'discount_amount', 'tax_amount', 'net_amount')
            ->with(['payments' => fn($q) => $q->latest()])
            ->findOrFail($id);

        $latestPayment = $invoice->payments->first();

        return response()->json([
            'status' => $invoice->status,
            'status_badge' => $invoice->status_badge,
            'balance_due' => $invoice->balance_due,
            'paid_amount' => $invoice->paid_amount,
            'latest_payment' => $latestPayment ? [
                'id' => $latestPayment->id,
                'payment_number' => $latestPayment->payment_number,
                'status' => $latestPayment->status,
                'status_badge' => $latestPayment->status_badge,
                'amount_paid' => $latestPayment->amount_paid,
                'rejection_reason' => $latestPayment->rejection_reason,
            ] : null,
        ]);
    }

    public function verifyPayment(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->canVerifyPayments()) {
            abort(403, 'Unauthorized. Only Receptionists and Admins can verify or approve payments.');
        }

        $invoice = Invoice::with(['patient.user', 'payments'])->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:approve,decline',
            'rejection_reason' => 'required_if:action,decline|nullable|string|max:255',
        ]);

        $pendingPayment = $invoice->latestPendingPayment();

        if ($validated['action'] === 'approve') {
            if ($pendingPayment) {
                $pendingPayment->update([
                    'status' => 'completed',
                    'notes' => ($pendingPayment->notes ?? '') . ' [Approved by ' . $user->name . ' on ' . now()->format('Y-m-d H:i') . ']',
                ]);
            } else {
                $count = Payment::whereDate('created_at', today())->count() + 1;
                $payNum = 'PAY-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                $pendingPayment = Payment::create([
                    'payment_number' => $payNum,
                    'invoice_id' => $invoice->id,
                    'payment_date' => now(),
                    'amount_paid' => $invoice->balance_due > 0 ? $invoice->balance_due : $invoice->net_amount,
                    'payment_method' => 'upi',
                    'transaction_reference' => 'APPROVED-' . strtoupper(substr(uniqid(), -6)),
                    'notes' => 'Approved by ' . $user->name,
                    'status' => 'completed',
                ]);
            }

            $invoice->recalculateTotals();

            if ($invoice->patient && $invoice->patient->user) {
                $nowFormatted = now()->format('M d, Y \a\t h:i A');
                $amountFormatted = number_format($pendingPayment->amount_paid, 2);
                Notification::create([
                    'user_id' => $invoice->patient->user->id,
                    'title' => "Payment Approved: Invoice #{$invoice->invoice_number}",
                    'message' => "Your payment of \${$amountFormatted} for Invoice #{$invoice->invoice_number} was successfully approved at {$nowFormatted}.",
                    'type' => 'billing',
                    'is_read' => false,
                ]);
            }

            AuditService::log('PAYMENT', 'payments', $pendingPayment->id, "Payment for Invoice #{$invoice->invoice_number} approved by {$user->name}");

            return back()->with('success', "Payment of \${$pendingPayment->amount_paid} has been approved and Invoice #{$invoice->invoice_number} is marked as PAID.");
        } else {
            $reason = $validated['rejection_reason'] ?? 'Payment verification failed.';

            if ($pendingPayment) {
                $pendingPayment->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'notes' => ($pendingPayment->notes ?? '') . " [Declined by {$user->name}: {$reason}]",
                ]);
            }

            $invoice->status = 'unpaid';
            $invoice->save();

            if ($invoice->patient && $invoice->patient->user) {
                Notification::create([
                    'user_id' => $invoice->patient->user->id,
                    'title' => "Payment Declined: Invoice #{$invoice->invoice_number}",
                    'message' => "Your payment for Invoice #{$invoice->invoice_number} was declined. Reason: {$reason}. Please click here to try again.",
                    'type' => 'billing',
                    'is_read' => false,
                ]);
            }

            AuditService::log('PAYMENT', 'payments', $pendingPayment ? $pendingPayment->id : $invoice->id, "Payment for Invoice #{$invoice->invoice_number} declined by {$user->name}. Reason: {$reason}");

            return back()->with('error', "Payment for Invoice #{$invoice->invoice_number} was declined. Notification sent to patient.");
        }
    }
}
