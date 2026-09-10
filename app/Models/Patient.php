<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Patient extends Model
{
    protected $fillable = [
        'user_id',
        'patient_code',
        'first_name',
        'last_name',
        'dob',
        'gender',
        'blood_type',
        'phone',
        'email',
        'address',
        'medical_history',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labReports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->dob ? Carbon::parse($this->dob)->age : 0;
    }

    public function ambulanceBookings(): HasMany
    {
        return $this->hasMany(AmbulanceBooking::class);
    }

    public function currentNonEmergencyAdmission(): ?Admission
    {
        return $this->admissions()
            ->with('bed.room.department')
            ->where('status', 'admitted')
            ->get()
            ->first(function ($admission) {
                return $admission->bed && !$admission->bed->isEmergency();
            });
    }
}

