<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Ambulance;
use App\Models\AmbulanceBooking;
use App\Models\AmbulanceDriver;
use App\Models\AmbulanceLocationLog;
use App\Models\Department;
use App\Models\Patient;
use App\Services\AuditService;

class AmbulanceController extends Controller
{
    // Default Apex Horizon Medical Center Coordinates
    public const HOSPITAL_LAT = 42.3375000;
    public const HOSPITAL_LNG = -71.1065000;
    public const HOSPITAL_NAME = 'Apex Horizon Medical Center';
    public const HOSPITAL_ADDRESS = '500 Health Sciences Blvd, Boston MA';

    /**
     * Calculate spherical distance between two coordinate pairs in kilometers (Haversine formula).
     */
    public static function calculateDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Display live nearby ambulances discovery map with user device location support.
     */
    public function index(Request $request)
    {
        $query = Ambulance::with(['currentDriver.user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('vehicle_number', 'like', $term)
                  ->orWhere('model', 'like', $term)
                  ->orWhereHas('currentDriver.user', function ($uq) use ($term) {
                      $uq->where('name', 'like', $term);
                  });
            });
        }

        $ambulances = $query->get();

        $userLat = $request->filled('lat') ? (float) $request->lat : null;
        $userLng = $request->filled('lng') ? (float) $request->lng : null;
        $radius = $request->filled('radius') ? (float) $request->radius : null;

        // Calculate distances if user device coordinates provided
        if ($userLat !== null && $userLng !== null) {
            $ambulances = $ambulances->map(function ($amb) use ($userLat, $userLng) {
                if ($amb->current_latitude !== null && $amb->current_longitude !== null) {
                    $dist = self::calculateDistanceKm($userLat, $userLng, (float) $amb->current_latitude, (float) $amb->current_longitude);
                    $amb->distance_km = $dist;
                    $amb->eta_minutes = max(1, (int) round(($dist / 40) * 60));
                } else {
                    $amb->distance_km = null;
                    $amb->eta_minutes = null;
                }
                return $amb;
            });

            // Filter by radius in km if specified
            if ($radius !== null && $radius > 0) {
                $ambulances = $ambulances->filter(function ($amb) use ($radius) {
                    return $amb->distance_km !== null && $amb->distance_km <= $radius;
                });
            }

            // Sort: nearest by default when location provided, or custom sort
            if ($request->get('sort') === 'status') {
                $ambulances = $ambulances->sortBy('status');
            } elseif ($request->get('sort') === 'type') {
                $ambulances = $ambulances->sortBy('type');
            } else {
                $ambulances = $ambulances->sortBy(function ($amb) {
                    return $amb->distance_km ?? PHP_FLOAT_MAX;
                });
            }
        } else {
            $ambulances = $ambulances->sortBy('status');
        }

        $stats = [
            'total' => Ambulance::count(),
            'available' => Ambulance::where('status', 'available')->count(),
            'dispatched' => Ambulance::where('status', 'dispatched')->count(),
            'in_transit' => Ambulance::where('status', 'in_transit')->count(),
            'maintenance' => Ambulance::where('status', 'maintenance')->count(),
        ];

        $hospital = [
            'name' => \App\Models\SystemSetting::get('hospital_name', self::HOSPITAL_NAME),
            'lat' => (float) \App\Models\SystemSetting::get('hospital_latitude', self::HOSPITAL_LAT),
            'lng' => (float) \App\Models\SystemSetting::get('hospital_longitude', self::HOSPITAL_LNG),
            'address' => \App\Models\SystemSetting::get('hospital_address', self::HOSPITAL_ADDRESS),
        ];

        return view('ambulance.index', compact('ambulances', 'stats', 'hospital', 'userLat', 'userLng', 'radius'));
    }

    /**
     * Show booking form for ambulance.
     */
    public function showBookingForm(Request $request, ?int $ambulance_id = null)
    {
        $selectedAmbulance = null;
        if ($ambulance_id) {
            $selectedAmbulance = Ambulance::with('currentDriver.user')->find($ambulance_id);
        }

        $availableAmbulances = Ambulance::with('currentDriver.user')
            ->where('status', 'available')
            ->get();

        $departments = Department::orderBy('name')->get();
        $emergencyDept = Department::where('code', 'EMER')->first();

        $user = Auth::user();
        $patient = null;
        if ($user && $user->patient) {
            $patient = $user->patient;
        }

        $hospital = [
            'name' => \App\Models\SystemSetting::get('hospital_name', self::HOSPITAL_NAME),
            'lat' => (float) \App\Models\SystemSetting::get('hospital_latitude', self::HOSPITAL_LAT),
            'lng' => (float) \App\Models\SystemSetting::get('hospital_longitude', self::HOSPITAL_LNG),
            'address' => \App\Models\SystemSetting::get('hospital_address', self::HOSPITAL_ADDRESS),
        ];

        $prefillLat = $request->query('pickup_lat');
        $prefillLng = $request->query('pickup_lng');
        $prefillAddress = $request->query('pickup_address');

        return view('ambulance.book', compact(
            'selectedAmbulance',
            'availableAmbulances',
            'departments',
            'emergencyDept',
            'patient',
            'hospital',
            'prefillLat',
            'prefillLng',
            'prefillAddress'
        ));
    }

    /**
     * Store new ambulance booking.
     */
    public function storeBooking(Request $request)
    {
        $validated = $request->validate([
            'contact_phone' => 'required|string|max:20',
            'pickup_address' => 'required|string',
            'pickup_latitude' => 'required|numeric|between:-90,90',
            'pickup_longitude' => 'required|numeric|between:-180,180',
            'ambulance_id' => 'nullable|exists:ambulances,id',
            'destination_hospital_department_id' => 'nullable|exists:departments,id',
            'patient_name' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $pickupLat = (float) $validated['pickup_latitude'];
        $pickupLng = (float) $validated['pickup_longitude'];

        // Determine Ambulance: user-selected or nearest available based on device GPS
        $ambulance = null;
        if (!empty($validated['ambulance_id'])) {
            $ambulance = Ambulance::find($validated['ambulance_id']);
        }

        if (!$ambulance || $ambulance->status !== 'available') {
            // Find nearest available ambulance using Haversine distance from user's device location
            $available = Ambulance::where('status', 'available')->get();

            if ($available->isEmpty()) {
                return back()->withInput()->with('error', 'All ambulances are currently busy or in maintenance. Please call 911 for critical emergencies.');
            }

            $closest = null;
            $minDistance = PHP_FLOAT_MAX;
            foreach ($available as $candidate) {
                if ($candidate->current_latitude !== null && $candidate->current_longitude !== null) {
                    $dist = self::calculateDistanceKm($pickupLat, $pickupLng, (float) $candidate->current_latitude, (float) $candidate->current_longitude);
                    if ($dist < $minDistance) {
                        $minDistance = $dist;
                        $closest = $candidate;
                    }
                } else {
                    $closest = $closest ?? $candidate;
                }
            }

            $ambulance = $closest ?? $available->first();
        }

        // Link patient if logged in or resolved
        $patientId = null;
        if (Auth::check() && Auth::user()->patient) {
            $patientId = Auth::user()->patient->id;
        }

        // Default department: Emergency if not specified
        $deptId = $validated['destination_hospital_department_id'] ?? null;
        if (!$deptId) {
            $emer = Department::where('code', 'EMER')->first();
            $deptId = $emer ? $emer->id : null;
        }

        // Create the booking
        $booking = AmbulanceBooking::create([
            'patient_id' => $patientId,
            'ambulance_id' => $ambulance->id,
            'driver_id' => $ambulance->current_driver_id,
            'contact_phone' => $validated['contact_phone'],
            'pickup_address' => $validated['pickup_address'],
            'pickup_latitude' => $pickupLat,
            'pickup_longitude' => $pickupLng,
            'destination_hospital_department_id' => $deptId,
            'booking_status' => 'assigned',
            'booking_time' => now(),
            'completed_at' => null,
        ]);

        // Update ambulance status to dispatched
        $ambulance->update([
            'status' => 'dispatched',
            'last_location_update' => now(),
        ]);

        // Log initial position in breadcrumbs
        if ($ambulance->current_latitude && $ambulance->current_longitude) {
            AmbulanceLocationLog::create([
                'ambulance_id' => $ambulance->id,
                'booking_id' => $booking->id,
                'latitude' => $ambulance->current_latitude,
                'longitude' => $ambulance->current_longitude,
                'recorded_at' => now(),
            ]);
        }

        AuditService::log('BOOK_AMBULANCE', 'ambulance_bookings', $booking->id, "Ambulance {$ambulance->vehicle_number} booked for pickup at {$validated['pickup_address']}");

        // Save booking ID to session for guest tracking
        session()->push('user_ambulance_bookings', $booking->id);

        return redirect()->route('ambulance.track', $booking->id)
            ->with('success', "Ambulance {$ambulance->vehicle_number} has been dispatched! Track your ambulance live below.");
    }

    /**
     * Zomato-style live tracking view.
     */
    public function track(int $booking_id)
    {
        $booking = AmbulanceBooking::with([
            'ambulance.currentDriver.user',
            'driver.user',
            'patient',
            'destinationDepartment',
            'locationLogs' => function ($q) {
                $q->orderBy('recorded_at', 'desc')->take(20);
            }
        ])->findOrFail($booking_id);

        $hospital = [
            'name' => self::HOSPITAL_NAME,
            'lat' => self::HOSPITAL_LAT,
            'lng' => self::HOSPITAL_LNG,
            'address' => self::HOSPITAL_ADDRESS,
        ];

        return view('ambulance.track', compact('booking', 'hospital'));
    }

    /**
     * Show past / active bookings for the current user.
     */
    public function myBookings()
    {
        $user = Auth::user();
        $query = AmbulanceBooking::with(['ambulance.currentDriver.user', 'destinationDepartment']);

        if ($user && $user->patient) {
            $query->where('patient_id', $user->patient->id);
        } else {
            // Check session bookings for guest
            $sessionIds = session()->get('user_ambulance_bookings', []);
            $query->whereIn('id', $sessionIds);
        }

        $bookings = $query->orderBy('booking_time', 'desc')->paginate(10);

        return view('ambulance.my-bookings', compact('bookings'));
    }

    /**
     * API: Get all ambulances and coordinates for map display with optional user device location.
     */
    public function apiLocations(Request $request)
    {
        $query = Ambulance::with(['currentDriver.user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $userLat = $request->filled('lat') ? (float) $request->lat : null;
        $userLng = $request->filled('lng') ? (float) $request->lng : null;
        $radius = $request->filled('radius') ? (float) $request->radius : null;

        $ambulances = $query->get()->map(function ($amb) use ($userLat, $userLng) {
            $distanceKm = null;
            $etaMinutes = null;
            $etaText = null;

            if ($userLat !== null && $userLng !== null && $amb->current_latitude !== null && $amb->current_longitude !== null) {
                $distanceKm = self::calculateDistanceKm($userLat, $userLng, (float) $amb->current_latitude, (float) $amb->current_longitude);
                $etaMinutes = max(1, (int) round(($distanceKm / 40) * 60));
                $etaText = "{$distanceKm} km away • ~{$etaMinutes} mins ETA";
            }

            return [
                'id' => $amb->id,
                'vehicle_number' => $amb->vehicle_number,
                'model' => $amb->model,
                'type' => $amb->type,
                'type_display' => $amb->typeDisplay(),
                'type_badge' => $amb->typeBadge(),
                'status' => $amb->status,
                'status_label' => $amb->statusLabel(),
                'status_badge' => $amb->statusBadge(),
                'lat' => $amb->current_latitude,
                'lng' => $amb->current_longitude,
                'distance_km' => $distanceKm,
                'eta_minutes' => $etaMinutes,
                'eta_text' => $etaText,
                'driver_name' => $amb->currentDriver?->user?->name ?? 'Unassigned Driver',
                'driver_phone' => $amb->currentDriver?->contact_number ?? 'N/A',
                'driver_license' => $amb->currentDriver?->license_number ?? 'N/A',
                'last_update' => $amb->last_location_update?->diffForHumans() ?? 'Just now',
            ];
        });

        if ($radius !== null && $radius > 0 && $userLat !== null && $userLng !== null) {
            $ambulances = $ambulances->filter(function ($amb) use ($radius) {
                return $amb['distance_km'] !== null && $amb['distance_km'] <= $radius;
            })->values();
        }

        if ($userLat !== null && $userLng !== null) {
            $ambulances = $ambulances->sortBy(function ($amb) {
                return $amb['distance_km'] ?? PHP_FLOAT_MAX;
            })->values();
        }

        return response()->json([
            'hospital' => [
                'name' => \App\Models\SystemSetting::get('hospital_name', self::HOSPITAL_NAME),
                'lat' => (float) \App\Models\SystemSetting::get('hospital_latitude', self::HOSPITAL_LAT),
                'lng' => (float) \App\Models\SystemSetting::get('hospital_longitude', self::HOSPITAL_LNG),
                'address' => \App\Models\SystemSetting::get('hospital_address', self::HOSPITAL_ADDRESS),
            ],
            'user_location' => ($userLat !== null && $userLng !== null) ? ['lat' => $userLat, 'lng' => $userLng] : null,
            'ambulances' => $ambulances,
        ]);
    }

    /**
     * API: Reposition/Deploy fleet units around user's device location (instant testing/demo worldwide).
     */
    public function apiRepositionNearDevice(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $centerLat = (float) $validated['lat'];
        $centerLng = (float) $validated['lng'];

        $ambulances = Ambulance::all();
        if ($ambulances->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No ambulances exist in the fleet to reposition.'], 404);
        }

        // Varied radii around device in km: 1.1km, 1.8km, 2.5km, 3.4km, 4.2km
        $radiiKm = [1.1, 1.8, 2.5, 3.2, 4.0, 1.5, 2.8, 3.6];
        $earthRadius = 6371.0;
        $count = count($ambulances);

        foreach ($ambulances as $i => $amb) {
            $distKm = $radiiKm[$i % count($radiiKm)];
            $angleDeg = ($i * (360.0 / max(1, $count))) + (($i % 2 === 0 ? 1 : -1) * 15);
            $bearingRad = deg2rad($angleDeg);

            $centerLatRad = deg2rad($centerLat);
            $centerLngRad = deg2rad($centerLng);
            $distRatio = $distKm / $earthRadius;

            $newLatRad = asin(sin($centerLatRad) * cos($distRatio) + cos($centerLatRad) * sin($distRatio) * cos($bearingRad));
            $newLngRad = $centerLngRad + atan2(sin($bearingRad) * sin($distRatio) * cos($centerLatRad), cos($distRatio) - sin($centerLatRad) * sin($newLatRad));

            $newLat = round(rad2deg($newLatRad), 7);
            $newLng = round(rad2deg($newLngRad), 7);

            $amb->update([
                'current_latitude' => $newLat,
                'current_longitude' => $newLng,
                'last_location_update' => now(),
            ]);
        }

        AuditService::log('REPOSITION_FLEET', 'ambulances', null, "Repositioned fleet around device GPS ({$centerLat}, {$centerLng})");

        return response()->json([
            'success' => true,
            'message' => "Successfully stationed {$count} ambulances around your current device location!",
            'center' => ['lat' => $centerLat, 'lng' => $centerLng],
        ]);
    }

    /**
     * API: Get live tracking data for a specific booking.
     */
    public function apiTrack(int $booking_id)
    {
        $booking = AmbulanceBooking::with([
            'ambulance.currentDriver.user',
            'driver.user',
            'destinationDepartment'
        ])->findOrFail($booking_id);

        $amb = $booking->ambulance;

        // Calculate distance between ambulance and pickup (Haversine formula in km)
        $distanceKm = 0.0;
        if ($amb && $amb->current_latitude && $amb->current_longitude) {
            $lat1 = deg2rad($amb->current_latitude);
            $lon1 = deg2rad($amb->current_longitude);
            $lat2 = deg2rad($booking->pickup_latitude);
            $lon2 = deg2rad($booking->pickup_longitude);

            $dlat = $lat2 - $lat1;
            $dlon = $lon2 - $lon1;
            $a = sin($dlat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dlon / 2) ** 2;
            $c = 2 * asin(sqrt($a));
            $distanceKm = round(6371 * $c, 2);
        }

        // Estimate time: average speed 40 km/h in city traffic
        $etaMinutes = max(1, (int) round(($distanceKm / 40) * 60));

        return response()->json([
            'booking_id' => $booking->id,
            'status' => $booking->booking_status,
            'status_display' => $booking->statusDisplay(),
            'step_index' => $booking->stepIndex(),
            'is_active' => $booking->isActive(),
            'pickup' => [
                'address' => $booking->pickup_address,
                'lat' => $booking->pickup_latitude,
                'lng' => $booking->pickup_longitude,
            ],
            'hospital' => [
                'name' => self::HOSPITAL_NAME,
                'lat' => self::HOSPITAL_LAT,
                'lng' => self::HOSPITAL_LNG,
                'department' => $booking->destinationDepartment?->name ?? 'Emergency Medicine',
            ],
            'ambulance' => [
                'id' => $amb->id,
                'vehicle_number' => $amb->vehicle_number,
                'model' => $amb->model,
                'type' => $amb->typeDisplay(),
                'status' => $amb->status,
                'lat' => $amb->current_latitude,
                'lng' => $amb->current_longitude,
            ],
            'driver' => [
                'name' => $booking->driver?->user?->name ?? $amb->currentDriver?->user?->name ?? 'Emergency Responder',
                'phone' => $booking->driver?->contact_number ?? $amb->currentDriver?->contact_number ?? '+1 (555) 911-0000',
                'license' => $booking->driver?->license_number ?? $amb->currentDriver?->license_number ?? 'Verified Medic',
            ],
            'metrics' => [
                'distance_km' => $distanceKm,
                'eta_minutes' => $booking->booking_status === 'arrived' ? 0 : $etaMinutes,
                'eta_text' => $booking->booking_status === 'arrived'
                    ? 'Arrived at your location'
                    : ($booking->booking_status === 'completed' ? 'Trip Completed' : "{$etaMinutes} mins away ({$distanceKm} km)"),
            ],
        ]);
    }

    /**
     * API: Advance simulation step for live tracking (just like Zomato live simulation).
     */
    public function apiSimulateStep(Request $request, int $booking_id)
    {
        $booking = AmbulanceBooking::with('ambulance')->findOrFail($booking_id);
        $amb = $booking->ambulance;

        if (!$amb || in_array($booking->booking_status, ['completed', 'cancelled'])) {
            return response()->json(['message' => 'Booking is already finalised.']);
        }

        // If status is 'assigned', transition to 'en_route'
        if ($booking->booking_status === 'assigned') {
            $booking->update(['booking_status' => 'en_route']);
            $amb->update(['status' => 'in_transit']);
        }

        // Target: If en_route or requested -> moving towards pickup location
        // If arrived -> next step can complete the ride
        if ($booking->booking_status === 'en_route' || $booking->booking_status === 'requested') {
            $targetLat = (float) $booking->pickup_latitude;
            $targetLng = (float) $booking->pickup_longitude;

            $curLat = (float) $amb->current_latitude;
            $curLng = (float) $amb->current_longitude;

            $dLat = $targetLat - $curLat;
            $dLng = $targetLng - $curLng;
            $dist = sqrt(($dLat * $dLat) + ($dLng * $dLng));

            // Move ~25% closer each simulation step
            if ($dist < 0.0008) { // Less than ~80 meters -> Arrived!
                $amb->update([
                    'current_latitude' => $targetLat,
                    'current_longitude' => $targetLng,
                    'last_location_update' => now(),
                ]);
                $booking->update(['booking_status' => 'arrived']);
            } else {
                $stepFraction = 0.25;
                $newLat = $curLat + ($dLat * $stepFraction);
                $newLng = $curLng + ($dLng * $stepFraction);

                $amb->update([
                    'current_latitude' => $newLat,
                    'current_longitude' => $newLng,
                    'last_location_update' => now(),
                ]);

                // Record location log
                AmbulanceLocationLog::create([
                    'ambulance_id' => $amb->id,
                    'booking_id' => $booking->id,
                    'latitude' => $newLat,
                    'longitude' => $newLng,
                    'recorded_at' => now(),
                ]);
            }
        } elseif ($booking->booking_status === 'arrived') {
            // Patient picked up -> Complete or head to hospital
            $booking->update([
                'booking_status' => 'completed',
                'completed_at' => now(),
            ]);
            $amb->update([
                'status' => 'available',
                'current_latitude' => self::HOSPITAL_LAT,
                'current_longitude' => self::HOSPITAL_LNG,
                'last_location_update' => now(),
            ]);
        }

        return $this->apiTrack($booking->id);
    }
}
