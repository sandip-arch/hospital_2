<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Services\AuditService;

class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isPatient()) {
            $patient = $user->patient;
            if ($patient) {
                $prescribedMedicineIds = \App\Models\PrescriptionItem::whereHas('prescription', function ($q) use ($patient) {
                    $q->where('patient_id', $patient->id);
                })->pluck('medicine_id')->unique();

                $query = Medicine::whereIn('id', $prescribedMedicineIds);
            } else {
                $query = Medicine::whereRaw('1 = 0');
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

            $medicines = $query->orderBy('name')->paginate(12)->withQueryString();
            $categories = Medicine::whereIn('id', $prescribedMedicineIds ?? [])->select('category')->distinct()->pluck('category');

            $stats = [
                'total_medicines' => $medicines->total(),
                'active_prescriptions' => Prescription::where('patient_id', $patient?->id)->where('status', 'active')->count(),
                'dispensed_prescriptions' => Prescription::where('patient_id', $patient?->id)->where('status', 'dispensed')->count(),
            ];

            return view('pharmacy.index', compact('medicines', 'categories', 'stats'));
        }

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

        $medicines = $query->orderBy('name')->paginate(12)->withQueryString();
        $categories = Medicine::select('category')->distinct()->pluck('category');

        $stats = [
            'total_medicines' => Medicine::count(),
            'total_stock_units' => Medicine::sum('stock_quantity'),
            'low_stock_count' => Medicine::whereColumn('stock_quantity', '<=', 'reorder_level')->count(),
            'pending_prescriptions' => Prescription::where('status', 'active')->count(),
        ];

        return view('pharmacy.index', compact('medicines', 'categories', 'stats'));
    }

    public function placeOrder(Request $request, $id)
    {
        $user = Auth::user();
        $medicine = Medicine::findOrFail($id);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
            'delivery_instructions' => 'nullable|string|max:255',
        ]);

        AuditService::log('MED_ORDER', 'medicines', $medicine->id, "Patient {$user->name} placed order/refill for {$validated['quantity']}x {$medicine->name}");

        return back()->with('success', "Order/Refill for {$validated['quantity']}x {$medicine->name} submitted to hospital pharmacy!");
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
