<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'room_type',
        'department_id',
        'daily_rate',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function getAvailableBedsCountAttribute(): int
    {
        return $this->beds()->where('status', 'available')->count();
    }

    public function isEmergency(): bool
    {
        if (strcasecmp($this->room_type, 'Emergency') === 0) {
            return true;
        }

        if ($this->department && (
            strcasecmp($this->department->code ?? '', 'EMER') === 0 ||
            stripos($this->department->name ?? '', 'emergency') !== false
        )) {
            return true;
        }

        return stripos($this->room_number, 'ER-') === 0 || stripos($this->room_number, 'EMER') === 0;
    }
}

