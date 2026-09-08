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

    public function getTargetUrlAttribute(): string
    {
        // 1. Internal Message notifications -> direct to chat with sender
        if ($this->title === 'New Internal Message' || stripos($this->message, 'Message from') !== false) {
            if (preg_match('/Message from (.*?)(?:\s*\([^)]*\))?:/i', $this->message, $matches)) {
                $name = trim($matches[1]);
                $sender = User::where('name', 'like', $name . '%')->first();
                if ($sender) {
                    return route('communication.messages', ['user_id' => $sender->id]);
                }
            }
            return route('communication.messages');
        }

        // 2. Clinical Appointments
        if ($this->type === 'appointment') {
            return route('appointments.index');
        }

        // 3. Lab Results
        if ($this->type === 'lab_result') {
            return route('lab.requests');
        }

        // 4. Billing & Invoices
        if ($this->type === 'billing') {
            return route('billing.index');
        }

        // Default
        return route('communication.notifications');
    }
}
