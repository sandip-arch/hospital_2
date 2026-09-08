<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'patient_id',
        'admission_id',
        'appointment_id',
        'invoice_date',
        'due_date',
        'total_amount',
        'discount_amount',
        'tax_amount',
        'net_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getPaidAmountAttribute(): float
    {
        return (float) $this->payments()->where('status', 'completed')->sum('amount_paid');
    }

    public function getBalanceDueAttribute(): float
    {
        return max(0.00, (float) $this->net_amount - $this->paid_amount);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->total_amount = $subtotal;
        $this->net_amount = max(0, $subtotal - $this->discount_amount + $this->tax_amount);

        if ($this->paid_amount >= $this->net_amount && $this->net_amount > 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'unpaid';
        }

        $this->save();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'partially_paid' => 'bg-amber-100 text-amber-800 border-amber-200',
            'unpaid' => 'bg-rose-100 text-rose-800 border-rose-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
