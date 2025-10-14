<?php

namespace App\Models;

use App\Traits\ActionTakenBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{

    use ActionTakenBy;
    protected $guarded = [];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (Auth::guard('admin')->check()) {
                $sale->user_id = Auth::guard('admin')->id();
            }
        });
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetails::class);
    }

    public function saleReturn()
    {
        return $this->hasOne(SaleReturn::class);
    }


    // Add accessor for status badge
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'confirmed' => 'info',
            'processing' => 'primary',
            'shipped' => 'dark',
            'delivered' => 'success',
            'cancelled' => 'danger'
        ];

        return $badges[$this->status] ?? 'secondary';
    }
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function batchOrder()
    {
        return $this->hasOne(BatchOrder::class, 'sale_id');
    }

    public function scopeReadyForDelivery($query)
    {
        return $query->where('delivery_status', 'ready')
            ->whereNotIn('status', ['cancelled', 'delivered']);
    }

    public function scopeByDeliveryStatus($query, $status)
    {
        return $query->where('delivery_status', $status);
    }
}
