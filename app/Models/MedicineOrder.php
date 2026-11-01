<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineOrder extends Model
{
    protected $table = 'medicine_orders';

    protected $fillable = [
        'patient_id',
        'user_id',
        'medicine_id',
        'quantity',
        'unit_price',
        'total_price',
        'delivery_instructions',
        'status',
        'cancellation_reason',
        'cancelled_by',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'quantity' => 'integer',
            'cancelled_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'confirmed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'dispensed' => 'bg-blue-100 text-blue-800 border-blue-200',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-amber-100 text-amber-800 border-amber-200',
        };
    }
}

