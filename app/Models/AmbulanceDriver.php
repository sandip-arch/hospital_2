<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AmbulanceDriver extends Model
{
    use HasFactory;

    protected $table = 'ambulance_drivers';

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'license_number',
        'contact_number',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ambulance(): HasOne
    {
        return $this->hasOne(Ambulance::class, 'current_driver_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(AmbulanceBooking::class, 'driver_id');
    }

    public function isOnDuty(): bool
    {
        return $this->status === 'on_duty';
    }

    public function statusBadge(): string
    {
        return $this->status === 'on_duty'
            ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
            : 'bg-slate-100 text-slate-800 border-slate-200';
    }
}
