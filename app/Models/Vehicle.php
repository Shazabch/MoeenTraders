<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_number',
        'vehicle_type',
        'driver_name',
        'driver_phone',
        'driver_license',
        'capacity_weight',
        'capacity_volume',
        'status',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'capacity_weight' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->latest();
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'available');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
