<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReport extends Model
{
    protected $fillable = [
        'medical_record_id',
        'patient_id',
        'lab_test_id',
        'doctor_id',
        'technician_id',
        'status',
        'result_summary',
        'file_path',
        'report_date',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'datetime',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'requested' => 'bg-amber-100 text-amber-800 border-amber-200',
            'in_progress' => 'bg-blue-100 text-blue-800 border-blue-200',
            'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
