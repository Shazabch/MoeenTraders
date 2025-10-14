<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerItem extends Model
{
    protected $fillable = [
        'container_id',
        'sale_id',
        'product_id',
        'quantity',
        'item_type',
        'notes'
    ];

    public function container(): BelongsTo
    {
        return $this->belongsTo(VehicleContainer::class, 'container_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}