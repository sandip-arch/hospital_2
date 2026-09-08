<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'appointment' => 'fa-calendar-check text-blue-500',
            'billing' => 'fa-file-invoice-dollar text-emerald-500',
            'lab_result' => 'fa-vial text-purple-500',
            'system' => 'fa-bell text-amber-500',
            default => 'fa-info-circle text-gray-500',
        };
    }
}
