<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbulanceComplaint extends Model
{
    use HasFactory;

    protected $table = 'ambulance_complaints';

    protected $fillable = [
        'ambulance_id',
        'driver_id',
        'title',
        'category',
        'priority',
        'description',
        'odometer_reading',
        'status',
        'admin_notes',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'odometer_reading' => 'integer',
    ];

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class, 'ambulance_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(AmbulanceDriver::class, 'driver_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'submitted' => 'bg-amber-50 text-amber-700 border-amber-200',
            'under_investigation' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'in_maintenance' => 'bg-purple-50 text-purple-700 border-purple-200',
            'resolved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'closed' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'submitted' => 'Pending Review',
            'under_investigation' => 'Under Investigation',
            'in_maintenance' => 'In Maintenance',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function priorityBadge(): string
    {
        return match ($this->priority) {
            'critical' => 'bg-rose-100 text-rose-800 border-rose-300 font-black',
            'high' => 'bg-amber-100 text-amber-800 border-amber-200 font-bold',
            'medium' => 'bg-blue-100 text-blue-800 border-blue-200 font-semibold',
            'low' => 'bg-slate-100 text-slate-700 border-slate-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'mechanical' => 'Mechanical Engine/Transmission',
            'electrical' => 'Electrical / Battery / Lighting',
            'medical_equipment' => 'Onboard Medical Gear / Defibrillator / O2',
            'tyres_brakes' => 'Tyres, Suspension & Braking',
            'air_conditioning' => 'HVAC & Patient Cabin Climate',
            'fuel_oil' => 'Fuel, Coolant & Lubrication',
            'cleanliness' => 'Cabin Sterility & Sanitation',
            'other' => 'Other Operational Issue',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }
}
