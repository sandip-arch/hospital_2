<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    protected $fillable = [
        'room_id',
        'bed_number',
        'status',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(Admission::class)->where('status', 'admitted')->latestOfMany();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'available' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'occupied' => 'bg-rose-100 text-rose-800 border-rose-300',
            'cleaning' => 'bg-amber-100 text-amber-800 border-amber-300',
            'maintenance' => 'bg-gray-200 text-gray-800 border-gray-300',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function isEmergency(): bool
    {
        return (bool) $this->room?->isEmergency();
    }

    public function canBeBookedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        // Doctors, Admins, Superadmins, Staff can book ANY bed
        if ($user->isAdmin() || $user->isDoctor() || $user->isStaff()) {
            return true;
        }

        // Patients can only book Emergency beds and only if they don't already have an active non-emergency bed
        if ($user->isPatient()) {
            if (!$this->isEmergency()) {
                return false;
            }

            if ($user->currentNonEmergencyAdmission()) {
                return false;
            }

            return true;
        }

        return false;
    }
}

