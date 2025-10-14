<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BatchOrder extends Model
{
    protected $fillable = [
        'batch_id',
        'sale_id',
        'sort_order',
        'delivery_status',
        'delivered_at',
        'delivery_notes'
    ];

    protected $casts = [
        'delivered_at' => 'datetime'
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DeliveryBatch::class, 'batch_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function trackingRecords(): HasMany
    {
        return $this->hasMany(DeliveryTracking::class, 'batch_order_id');
    }
}
