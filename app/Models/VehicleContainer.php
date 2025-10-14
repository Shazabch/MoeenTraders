<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class VehicleContainer extends Model
{
    protected $fillable = [
        'assignment_id',
        'container_name',
        'position',
        'notes'
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(VehicleAssignment::class, 'assignment_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ContainerItem::class, 'container_id');
    }
}