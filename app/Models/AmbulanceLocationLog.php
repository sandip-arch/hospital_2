<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbulanceLocationLog extends Model
{
    use HasFactory;

    protected $table = 'ambulance_location_logs';

    public $timestamps = false;

    protected $fillable = [
        'ambulance_id',
        'booking_id',
        'latitude',
        'longitude',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'recorded_at' => 'datetime',
    ];

    public function ambulance(): BelongsTo
    {
        return $this->belongsTo(Ambulance::class, 'ambulance_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(AmbulanceBooking::class, 'booking_id');
    }
}
