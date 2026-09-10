<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Models\AmbulanceComplaint;
use App\Models\Notification;
use App\Models\User;
use App\Services\AuditService;

class AmbulanceComplaintController extends Controller
{
    /**
     * Display complaints list for Driver or Admin.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isDriver = $user->isDriver();
        $isAdmin = $user->isAdmin();

        $query = AmbulanceComplaint::with(['ambulance.assignedDoctor.user', 'driver.user', 'resolver']);

        if ($isDriver && !$isAdmin) {
            $driver = $user->ambulanceDriver;
            $driverId = $driver ? $driver->id : 0;
            $query->where('driver_id', $driverId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('ambulance_id')) {
            $query->where('ambulance_id', $request->ambulance_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('ambulance', function ($aq) use ($search) {
                      $aq->where('vehicle_number', 'like', "%{$search}%");
                  });
            });
        }

        $complaints = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => AmbulanceComplaint::count(),
            'pending' => AmbulanceComplaint::where('status', 'submitted')->count(),
            'in_investigation' => AmbulanceComplaint::where('status', 'under_investigation')->count(),
            'in_maintenance' => AmbulanceComplaint::where('status', 'in_maintenance')->count(),
            'resolved' => AmbulanceComplaint::where('status', 'resolved')->count(),
        ];

        $driver = $user->ambulanceDriver;
        $assignedAmbulance = $driver ? Ambulance::where('current_driver_id', $driver->id)->first() : null;
        $fleetAmbulances = Ambulance::orderBy('vehicle_number')->get();

        return view('admin.ambulances.complaints', compact('complaints', 'stats', 'assignedAmbulance', 'fleetAmbulances', 'isDriver', 'isAdmin'));
    }

    /**
     * File a new ambulance complaint.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'ambulance_id' => 'required|exists:ambulances,id',
            'title' => 'required|string|max:150',
            'category' => 'required|in:mechanical,electrical,medical_equipment,tyres_brakes,air_conditioning,fuel_oil,cleanliness,other',
            'priority' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|max:3000',
            'odometer_reading' => 'nullable|integer|min:0',
        ]);

        $user = Auth::user();
        $driver = $user->ambulanceDriver;

        if (!$driver) {
            // If user has driver role but profile missing, create one
            $driver = AmbulanceDriver::create([
                'user_id' => $user->id,
                'license_number' => 'DRV-' . strtoupper(uniqid()),
                'contact_number' => '+1 (555) 000-0000',
                'status' => 'on_duty',
            ]);
        }

        $validated['driver_id'] = $driver->id;
        $validated['status'] = 'submitted';

        $complaint = AmbulanceComplaint::create($validated);
        $ambulance = Ambulance::find($validated['ambulance_id']);

        // Notify Admins and Superadmins
        $adminUserIds = User::where('status', 'active')
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['superadmin', 'admin']);
            })
            ->pluck('id');

        foreach ($adminUserIds as $adminId) {
            Notification::create([
                'user_id' => $adminId,
                'title' => "Ambulance Issue Reported: {$ambulance?->vehicle_number}",
                'message' => "Driver {$user->name} filed [{$complaint->priority}] complaint: {$complaint->title}",
                'type' => 'system',
                'target_url' => route('ambulance.complaints.index'),
                'is_read' => false,
            ]);
        }

        AuditService::log('CREATE_COMPLAINT', 'ambulance_complaints', $complaint->id, "Ambulance complaint '{$complaint->title}' filed by {$user->name} for {$ambulance?->vehicle_number}.");

        return back()->with('success', "Ambulance complaint '{$complaint->title}' has been successfully submitted and forwarded to fleet maintenance.");
    }

    /**
     * Update complaint status and administrative notes.
     */
    public function update(Request $request, $id)
    {
        $complaint = AmbulanceComplaint::with(['ambulance', 'driver.user'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:submitted,under_investigation,in_maintenance,resolved,closed',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        if (in_array($validated['status'], ['resolved', 'closed'])) {
            $validated['resolved_by'] = Auth::id();
            $validated['resolved_at'] = now();
        }

        $complaint->update($validated);

        // Notify the reporting driver
        if ($complaint->driver?->user_id) {
            Notification::create([
                'user_id' => $complaint->driver->user_id,
                'title' => "Ambulance Complaint Update: {$complaint->ambulance?->vehicle_number}",
                'message' => "Your report '{$complaint->title}' is now: {$complaint->statusLabel()}.",
                'type' => 'system',
                'target_url' => route('ambulance.complaints.index'),
                'is_read' => false,
            ]);
        }

        AuditService::log('UPDATE_COMPLAINT', 'ambulance_complaints', $complaint->id, "Complaint '{$complaint->title}' updated to {$complaint->status} by " . Auth::user()->name);

        return back()->with('success', "Complaint status updated to {$complaint->statusLabel()}.");
    }

    /**
     * Delete/cancel complaint.
     */
    public function destroy($id)
    {
        $complaint = AmbulanceComplaint::findOrFail($id);
        $user = Auth::user();

        // Only allow admin or owning driver to delete
        if (!$user->isAdmin() && $complaint->driver?->user_id !== $user->id) {
            return back()->with('error', 'Unauthorized to delete this complaint.');
        }

        $title = $complaint->title;
        $complaint->delete();

        AuditService::log('DELETE_COMPLAINT', 'ambulance_complaints', $id, "Ambulance complaint '{$title}' deleted.");

        return back()->with('success', "Complaint '{$title}' removed.");
    }
}
