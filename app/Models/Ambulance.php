<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ambulance extends Model
{
    use HasFactory;

    protected $table = 'ambulances';

    protected $fillable = [
        'vehicle_number',
        'model',
        'type',
        'current_driver_id',
        'assigned_doctor_id',
        'status',
        'current_latitude',
        'current_longitude',
        'last_location_update',
    ];

    protected $casts = [
        'current_latitude' => 'float',
        'current_longitude' => 'float',
        'last_location_update' => 'datetime',
    ];

    public function currentDriver(): BelongsTo
    {
        return $this->belongsTo(AmbulanceDriver::class, 'current_driver_id');
    }

    public function assignedDoctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'assigned_doctor_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(AmbulanceBooking::class, 'ambulance_id');
    }

    public function locationLogs(): HasMany
    {
        return $this->hasMany(AmbulanceLocationLog::class, 'ambulance_id');
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(AmbulanceComplaint::class, 'ambulance_id');
    }

    public function activeBooking()
    {
        return $this->hasOne(AmbulanceBooking::class, 'ambulance_id')
            ->whereIn('booking_status', ['requested', 'assigned', 'en_route', 'arrived'])
            ->latestOfMany();
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function typeDisplay(): string
    {
        return match ($this->type) {
            'Advanced_Life_Support' => 'Advanced Life Support (ALS / ICU)',
            'Basic' => 'Basic Life Support (BLS)',
            'Patient_Transport' => 'Patient Transport Service (PTS)',
            default => str_replace('_', ' ', $this->type),
        };
    }

    public function typeBadge(): string
    {
        return match ($this->type) {
            'Advanced_Life_Support' => 'bg-rose-100 text-rose-800 border-rose-200',
            'Basic' => 'bg-blue-100 text-blue-800 border-blue-200',
            'Patient_Transport' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            default => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'available' => 'bg-emerald-500 text-white',
            'dispatched' => 'bg-amber-500 text-white',
            'in_transit' => 'bg-cyan-500 text-white',
            'maintenance' => 'bg-slate-500 text-white',
            default => 'bg-slate-500 text-white',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'available' => 'Available & Ready',
            'dispatched' => 'Dispatched to Patient',
            'in_transit' => 'In Transit / En Route',
            'maintenance' => 'In Maintenance',
            default => ucfirst($this->status),
        };
    }
}
