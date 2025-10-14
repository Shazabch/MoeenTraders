<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryTracking extends Model
{
    protected $fillable = [
        'assignment_id',
        'batch_order_id',
        'status',
        'latitude',
        'longitude',
        'notes',
        'signature_image',
        'delivery_photo',
        'tracked_at'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'tracked_at' => 'datetime'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    public function batchOrder(): BelongsTo
    {
        return $this->belongsTo(BatchOrder::class, 'batch_order_id');
    }
}