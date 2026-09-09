<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Models\AmbulanceBooking;
use App\Models\User;
use App\Services\AuditService;

class AmbulanceAdminController extends Controller
{
    /**
     * Fleet Management Dashboard for Superadmin & Admin.
     */
    public function index(Request $request)
    {
        $query = Ambulance::with(['currentDriver.user', 'activeBooking.patient']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $ambulances = $query->orderBy('vehicle_number')->paginate(15);

        $stats = [
            'total' => Ambulance::count(),
            'available' => Ambulance::where('status', 'available')->count(),
            'dispatched' => Ambulance::where('status', 'dispatched')->count(),
            'in_transit' => Ambulance::where('status', 'in_transit')->count(),
            'maintenance' => Ambulance::where('status', 'maintenance')->count(),
            'active_bookings' => AmbulanceBooking::whereIn('booking_status', ['requested', 'assigned', 'en_route', 'arrived'])->count(),
            'total_drivers' => AmbulanceDriver::count(),
        ];

        return view('admin.ambulances.index', compact('ambulances', 'stats'));
    }

    /**
     * Show form to create a new ambulance.
     */
    public function create()
    {
        $availableDrivers = AmbulanceDriver::with('user')
            ->whereDoesntHave('ambulance')
            ->get();

        return view('admin.ambulances.create', compact('availableDrivers'));
    }

    /**
     * Store new ambulance.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:ambulances,vehicle_number',
            'model' => 'required|string|max:100',
            'type' => 'required|in:Basic,Advanced_Life_Support,Patient_Transport',
            'current_driver_id' => 'nullable|exists:ambulance_drivers,id',
            'status' => 'required|in:available,dispatched,in_transit,maintenance',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Default to hospital location if coordinates not provided
        $hLat = (float) \App\Models\SystemSetting::get('hospital_latitude', 42.3375000);
        $hLng = (float) \App\Models\SystemSetting::get('hospital_longitude', -71.1065000);
        $validated['current_latitude'] = $validated['current_latitude'] ?? $hLat;
        $validated['current_longitude'] = $validated['current_longitude'] ?? $hLng;
        $validated['last_location_update'] = now();

        $ambulance = Ambulance::create($validated);

        AuditService::log('CREATE_AMBULANCE', 'ambulances', $ambulance->id, "Ambulance {$ambulance->vehicle_number} registered by Admin.");

        return redirect()->route('admin.ambulances.index')
            ->with('success', "Ambulance {$ambulance->vehicle_number} has been successfully registered to the fleet.");
    }

    /**
     * Show edit form for ambulance.
     */
    public function edit(int $id)
    {
        $ambulance = Ambulance::with('currentDriver.user')->findOrFail($id);

        $availableDrivers = AmbulanceDriver::with('user')
            ->where(function ($q) use ($ambulance) {
                $q->whereDoesntHave('ambulance')
                  ->orWhere('id', $ambulance->current_driver_id);
            })
            ->get();

        return view('admin.ambulances.edit', compact('ambulance', 'availableDrivers'));
    }

    /**
     * Update ambulance.
     */
    public function update(Request $request, int $id)
    {
        $ambulance = Ambulance::findOrFail($id);

        $validated = $request->validate([
            'vehicle_number' => 'required|string|max:20|unique:ambulances,vehicle_number,' . $ambulance->id,
            'model' => 'required|string|max:100',
            'type' => 'required|in:Basic,Advanced_Life_Support,Patient_Transport',
            'current_driver_id' => 'nullable|exists:ambulance_drivers,id',
            'status' => 'required|in:available,dispatched,in_transit,maintenance',
            'current_latitude' => 'nullable|numeric|between:-90,90',
            'current_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $validated['last_location_update'] = now();

        $ambulance->update($validated);

        AuditService::log('UPDATE_AMBULANCE', 'ambulances', $ambulance->id, "Ambulance {$ambulance->vehicle_number} updated by Admin.");

        return redirect()->route('admin.ambulances.index')
            ->with('success', "Ambulance {$ambulance->vehicle_number} updated successfully.");
    }

    /**
     * Delete / decommission ambulance.
     */
    public function destroy(int $id)
    {
        $ambulance = Ambulance::findOrFail($id);

        if ($ambulance->activeBooking()->exists()) {
            return back()->with('error', "Cannot decommission {$ambulance->vehicle_number} while it has an active booking dispatch.");
        }

        $vehicleNum = $ambulance->vehicle_number;
        $ambulance->delete();

        AuditService::log('DELETE_AMBULANCE', 'ambulances', $id, "Ambulance {$vehicleNum} decommissioned.");

        return redirect()->route('admin.ambulances.index')
            ->with('success', "Ambulance {$vehicleNum} has been decommissioned from active fleet.");
    }

    /**
     * Driver roster view.
     */
    public function drivers()
    {
        $drivers = AmbulanceDriver::with(['user', 'ambulance'])->paginate(15);
        $candidateUsers = User::whereDoesntHave('ambulanceDriver')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.ambulances.drivers', compact('drivers', 'candidateUsers'));
    }

    /**
     * Store new driver profile.
     */
    public function storeDriver(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id|unique:ambulance_drivers,user_id',
            'license_number' => 'required|string|max:50',
            'contact_number' => 'required|string|max:20',
            'status' => 'required|in:on_duty,off_duty',
        ]);

        $driver = AmbulanceDriver::create($validated);

        AuditService::log('CREATE_DRIVER', 'ambulance_drivers', $driver->id, "Ambulance Driver profile created for user ID {$driver->user_id}.");

        return back()->with('success', 'Ambulance driver registered successfully.');
    }
}
