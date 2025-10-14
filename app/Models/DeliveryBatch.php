<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryBatch extends Model
{
    protected $fillable = [
        'batch_number',
        'created_by',
        'area_id',
        'delivery_date',
        'status',
        'notes',
        'total_orders',
        'total_amount'
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'total_amount' => 'decimal:2'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function batchOrders(): HasMany
    {
        return $this->hasMany(BatchOrder::class, 'batch_id');
    }

    public function vehicleAssignment()
    {
        return $this->hasOne(VehicleAssignment::class, 'batch_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($batch) {
            if (!$batch->batch_number) {
                $batch->batch_number = 'BATCH-' . date('Ymd') . '-' . str_pad(static::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}

