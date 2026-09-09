<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AmbulanceBooking extends Model
{
    use HasFactory;

    protected $table = 'ambulance_bookings';

    public $timestamps = false;

    protected $fillable = [
        'patient_id',
        'ambulance_id',
        'driver_id',
        'contact_phone',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'destination_hospital_department_id',
        'booking_status',
        'booking_time',
        'completed_at',
    ];

    protected $casts = [
        'pickup_latitude' => 'float',
        'pickup_longitude' => 'float',
        'booking_time' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class, 'ambulance_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(AmbulanceDriver::class, 'driver_id');
    }

    public function destinationDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'destination_hospital_department_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(AmbulanceLocationLog::class, 'booking_id');
    }

    public function isActive(): bool
    {
        return in_array($this->booking_status, ['requested', 'assigned', 'en_route', 'arrived']);
    }

    public function statusBadge(): string
    {
        return match ($this->booking_status) {
            'requested' => 'bg-amber-100 text-amber-800 border-amber-200',
            'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
            'en_route' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'arrived' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function statusDisplay(): string
    {
        return match ($this->booking_status) {
            'requested' => 'Booking Requested',
            'assigned' => 'Driver & Ambulance Assigned',
            'en_route' => 'Ambulance En Route',
            'arrived' => 'Arrived at Pickup Location',
            'completed' => 'Trip Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst(str_replace('_', ' ', $this->booking_status)),
        };
    }

    public function stepIndex(): int
    {
        return match ($this->booking_status) {
            'requested' => 1,
            'assigned' => 2,
            'en_route' => 3,
            'arrived' => 4,
            'completed' => 5,
            'cancelled' => 0,
            default => 1,
        };
    }
}
