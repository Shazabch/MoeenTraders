<?php

namespace App\Models;

use App\Traits\ActionTakenBy;
use Illuminate\Database\Eloquent\Model;
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
}
