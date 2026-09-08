<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'invoice_id',
        'payment_date',
        'amount_paid',
        'payment_method',
        'transaction_reference',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'datetime',
            'amount_paid' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getMethodBadgeAttribute(): string
    {
        return match ($this->payment_method) {
            'cash' => 'bg-emerald-100 text-emerald-800',
            'credit_card', 'debit_card' => 'bg-blue-100 text-blue-800',
            'upi' => 'bg-purple-100 text-purple-800',
            'insurance' => 'bg-teal-100 text-teal-800',
            'bank_transfer' => 'bg-indigo-100 text-indigo-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
