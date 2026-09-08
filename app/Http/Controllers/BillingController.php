<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Patient;
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
        $invoice = Invoice::findOrFail($id);

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
}
