<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Admission extends Model
{
    protected $fillable = [
        'patient_id',
        'bed_id',
        'doctor_id',
        'admission_date',
        'discharge_date',
        'admission_reason',
        'discharge_notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getStayDaysAttribute(): int
    {
        $start = Carbon::parse($this->admission_date);
        $end = $this->discharge_date ? Carbon::parse($this->discharge_date) : Carbon::now();
        return max(1, $start->diffInDays($end));
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'admitted' => 'bg-blue-100 text-blue-800 border-blue-200',
            'discharged' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'transferred' => 'bg-amber-100 text-amber-800 border-amber-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
