<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Medicine;
use App\Models\MedicineOrder;
use App\Models\Prescription;
use App\Models\Notification;
use App\Services\AuditService;

class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $scope = $request->query('scope', 'prescribed'); // 'prescribed' or 'all'

        if ($user->isPatient()) {
            $patient = $user->patient;
            $prescribedMedicineIds = collect();
            if ($patient) {
                $prescribedMedicineIds = \App\Models\PrescriptionItem::whereHas('prescription', function ($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                })->pluck('medicine_id')->unique();
            }

            if ($scope === 'all') {
                $query = Medicine::query();
            } else {
                if ($patient && $prescribedMedicineIds->isNotEmpty()) {
                    $query = Medicine::whereIn('id', $prescribedMedicineIds);
                } else {
                    // If no prescribed medicines yet, default to all catalog so patient is never left with an empty blank screen
                    $query = Medicine::query();
                    $scope = 'all';
                }
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('generic_name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category', $request->category);
            }

            if ($request->filled('stock_status')) {
                if ($request->stock_status === 'in_stock') {
                    $query->where('stock_quantity', '>', 0);
                } elseif ($request->stock_status === 'out_of_stock') {
                    $query->where('stock_quantity', '<=', 0);
                }
            }

            $medicines = $query->orderBy('name')->paginate(12)->withQueryString();
            $categories = Medicine::select('category')->distinct()->pluck('category');

            $myOrders = MedicineOrder::with('medicine')
                ->where('user_id', $user->id)
                ->latest()
                ->take(30)
                ->get();

            $stats = [
                'total_medicines' => $medicines->total(),
                'active_prescriptions' => Prescription::where('patient_id', $patient?->id)->where('status', 'active')->count(),
                'dispensed_prescriptions' => Prescription::where('patient_id', $patient?->id)->where('status', 'dispensed')->count(),
                'my_orders_count' => MedicineOrder::where('user_id', $user->id)->count(),
                'active_orders_count' => MedicineOrder::where('user_id', $user->id)->whereIn('status', ['pending', 'confirmed'])->count(),
            ];

            return view('pharmacy.index', compact('medicines', 'categories', 'stats', 'myOrders', 'scope', 'prescribedMedicineIds'));
        }

        // Staff / Admin View
        $query = Medicine::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock_quantity', '<=', 'reorder_level');
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'in_stock') {
                $query->where('stock_quantity', '>', 0);
            } elseif ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $medicines = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Medicine::select('category')->distinct()->pluck('category');

        $recentOrders = MedicineOrder::with(['medicine', 'user', 'patient'])
            ->latest()
            ->take(40)
            ->get();

        $stats = [
            'total_medicines' => Medicine::count(),
            'total_stock_units' => Medicine::sum('stock_quantity'),
            'low_stock_count' => Medicine::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'out_of_stock_count' => Medicine::where('stock_quantity', '<=', 0)->count(),
            'pending_prescriptions' => Prescription::where('status', 'active')->count(),
            'pending_orders_count' => MedicineOrder::where('status', 'pending')->count(),
        ];

        return view('pharmacy.index', compact('medicines', 'categories', 'stats', 'recentOrders'));
    }

    public function buy(Request $request, $id)
    {
        $user = Auth::user();
        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:50',
            'delivery_instructions' => 'nullable|string|max:255',
        ]);

        $quantity = (int) $validated['quantity'];

        // Availability and stock validation
        if ($medicine->stock_quantity <= 0) {
            return back()->with('error', "Sorry, {$medicine->name} is currently OUT OF STOCK. Refill cannot be processed right now.");
        }

        if ($medicine->stock_quantity < $quantity) {
            return back()->with('error', "Insufficient stock! Only {$medicine->stock_quantity} units of {$medicine->name} are currently available in the pharmacy.");
        }

        // Deduct inventory stock
        $medicine->decrement('stock_quantity', $quantity);

        $totalPrice = round($medicine->unit_price * $quantity, 2);

        // Record official order
        $order = MedicineOrder::create([
            'patient_id' => $user->patient?->id,
            'user_id' => $user->id,
            'medicine_id' => $medicine->id,
            'quantity' => $quantity,
            'unit_price' => $medicine->unit_price,
            'total_price' => $totalPrice,
            'delivery_instructions' => $validated['delivery_instructions'] ?? null,
            'status' => 'pending',
        ]);

        AuditService::log('MED_BUY', 'medicine_orders', $order->id, "Ordered {$quantity}x {$medicine->name} for \${$totalPrice}. Remaining stock: {$medicine->stock_quantity}");

        Notification::create([
            'user_id' => $user->id,
            'title' => 'Medicine Order Placed',
            'message' => "Order #{$order->id} for {$quantity}x {$medicine->name} placed successfully (Total: \${$totalPrice}). The hospital pharmacy will prepare your pack.",
            'type' => 'pharmacy',
            'is_read' => false,
        ]);

        return back()->with('success', "Order #{$order->id} for {$quantity}x {$medicine->name} placed successfully! Stock reserved ({$medicine->stock_quantity} left).");
    }

    public function placeOrder(Request $request, $id)
    {
        return $this->buy($request, $id);
    }

    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        $order = MedicineOrder::with('medicine')->findOrFail($id);

        // Security check
        if (!$user->isAdmin() && !$user->isStaff() && $order->user_id !== $user->id) {
            abort(403, 'Unauthorized to cancel this order.');
        }

        if ($order->status === 'cancelled') {
            return back()->with('error', "Order #{$order->id} is already cancelled.");
        }

        if ($order->status === 'dispensed') {
            return back()->with('error', "Order #{$order->id} has already been dispensed from the pharmacy and cannot be cancelled.");
        }

        $reason = $request->input('reason', 'Cancelled by patient/user');

        // Restore inventory stock
        if ($order->medicine) {
            $order->medicine->increment('stock_quantity', $order->quantity);
        }

        $order->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
        ]);

        AuditService::log('MED_ORDER_CANCEL', 'medicine_orders', $order->id, "Cancelled order #{$order->id} for {$order->quantity}x {$order->medicine?->name}. Stock refunded to {$order->medicine?->stock_quantity} units.");

        Notification::create([
            'user_id' => $order->user_id,
            'title' => 'Medicine Order Cancelled',
            'message' => "Order #{$order->id} for {$order->quantity}x {$order->medicine?->name} has been cancelled. Quantity restored to inventory.",
            'type' => 'pharmacy',
            'is_read' => false,
        ]);

        return back()->with('success', "Order #{$order->id} cancelled successfully! {$order->quantity} units restored to inventory.");
    }

    public function updateOrderStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,dispensed,cancelled',
            'notes' => 'nullable|string|max:255',
        ]);

        $order = MedicineOrder::with('medicine')->findOrFail($id);

        if ($order->status === 'cancelled' && $validated['status'] !== 'cancelled') {
            // If uncancelling, check stock
            if ($order->medicine->stock_quantity < $order->quantity) {
                return back()->with('error', "Cannot reinstate order. Current stock is {$order->medicine->stock_quantity}, needed {$order->quantity}.");
            }
            $order->medicine->decrement('stock_quantity', $order->quantity);
        } elseif ($validated['status'] === 'cancelled' && $order->status !== 'cancelled') {
            $order->medicine->increment('stock_quantity', $order->quantity);
            $order->cancelled_by = $user->id;
            $order->cancelled_at = now();
            $order->cancellation_reason = $validated['notes'] ?? 'Cancelled by pharmacy staff';
        }

        $order->status = $validated['status'];
        $order->save();

        AuditService::log('MED_ORDER_STATUS', 'medicine_orders', $order->id, "Updated order #{$order->id} status to {$validated['status']}");

        return back()->with('success', "Order #{$order->id} status updated to " . ucfirst($validated['status']) . ".");
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot modify drug inventory.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'generic_name' => 'nullable|string|max:100',
            'category' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'reorder_level' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);

        $med = Medicine::create($validated);
        AuditService::log('CREATE', 'medicines', $med->id, "Added new medicine {$med->name} to pharmacy inventory");

        return back()->with('success', "Medicine {$med->name} added to pharmacy catalog.");
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot modify drug inventory.');
        }

        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'generic_name' => 'nullable|string|max:100',
            'category' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
        ]);

        $medicine->update($validated);
        AuditService::log('UPDATE', 'medicines', $medicine->id, "Updated details for medicine {$medicine->name}");

        return back()->with('success', "Medicine {$medicine->name} updated successfully.");
    }

    public function adjustStock(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized. Patients cannot adjust drug inventory.');
        }

        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string',
        ]);

        $oldStock = $medicine->stock_quantity;

        if ($validated['adjustment_type'] === 'add') {
            $newStock = $oldStock + $validated['quantity'];
        } elseif ($validated['adjustment_type'] === 'subtract') {
            $newStock = max(0, $oldStock - $validated['quantity']);
        } else {
            $newStock = $validated['quantity'];
        }

        $medicine->update(['stock_quantity' => $newStock]);

        AuditService::log('STOCK_ADJUST', 'medicines', $medicine->id, "Adjusted stock for {$medicine->name} from {$oldStock} to {$newStock} ({$validated['reason']})");

        return back()->with('success', "Stock updated for {$medicine->name}. Current stock: {$newStock} units.");
    }

    public function dispenseQueue()
    {
        $user = Auth::user();
        if ($user->isPatient()) {
            abort(403, 'Unauthorized.');
        }

        $prescriptions = Prescription::with(['patient', 'doctor.user', 'items.medicine'])
            ->where('status', 'active')
            ->orderBy('created_at')
            ->paginate(10);

        return view('pharmacy.dispense', compact('prescriptions'));
    }
}
