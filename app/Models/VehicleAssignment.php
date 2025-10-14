<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleAssignment extends Model
{
    protected $fillable = [
        'vehicle_id',
        'batch_id',
        'assigned_by',
        'assigned_to',
        'assigned_at',
        'started_at',
        'completed_at',
        'starting_km',
        'ending_km',
        'status',
        'notes'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'starting_km' => 'decimal:2',
        'ending_km' => 'decimal:2'
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DeliveryBatch::class, 'batch_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to');
    }
    public function containers(): HasMany
    {
        return $this->hasMany(VehicleContainer::class, 'assignment_id');
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(DeliveryTracking::class, 'assignment_id');
    }
}
