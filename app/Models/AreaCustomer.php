<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaCustomer extends Model
{
    // Explicitly define the table name since it doesn't follow Laravel's plural convention
    protected $table = 'area_customer';

    // Allow mass assignment for these columns
    protected $fillable = [
        'area_id',
        'customer_id',
    ];

    /**
     * Each AreaCustomer belongs to an Area.
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    /**
     * Each AreaCustomer belongs to a Customer.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
