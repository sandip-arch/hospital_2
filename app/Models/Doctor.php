<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'user_id',
        'department_id',
        'specialization',
        'license_number',
        'consultation_fee',
        'phone',
        'bio',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'consultation_fee' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function availabilities(): HasMany
    {
        return $this->hasMany(DoctorAvailability::class);
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

    public function ambulances(): HasMany
    {
        return $this->hasMany(Ambulance::class, 'assigned_doctor_id');
    }

    public function getFullNameAttribute(): string
    {
        return $this->user ? 'Dr. ' . $this->user->name : 'Dr. Doctor';
    }
}
