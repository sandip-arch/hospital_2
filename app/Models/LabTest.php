<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    protected $fillable = [
        'test_name',
        'code',
        'description',
        'cost',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }
}
