<?php

namespace App\Models;

use App\Traits\ActionTakenBy;
use App\Traits\UserNotify;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Customer extends Model
{
    use ActionTakenBy, UserNotify;
    use SoftDeletes;


    public function area()
    {
        return $this->hasOneThrough(
            Area::class,
            AreaCustomer::class,
            'customer_id', // Foreign key on area_customer table
            'id',           // Foreign key on areas table
            'id',           // Local key on customers table
            'area_id'       // Local key on area_customer table
        );
    }

    public function sale()
    {
        return $this->hasMany(Sale::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function saleReturns()
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetails::class);
    }

    public function totalSaleReturnDueAmount()
    {
        return $this->sale->sum('due_amount');
    }

    public function totalSaleDueAmount()
    {
        return $this->saleReturns->sum('due_amount');
    }

    function  totalReceivableAmount()
    {
        $saleAmount   = $this->sale->where('due_amount', '>', 0)->sum('due_amount');
        $returnAmount = $this->saleReturns->where('due_amount', '<', 0)->sum('due_amount');
        return $saleAmount + abs($returnAmount);
    }

    function totalPayableAmount()
    {
        $saleAmount = $this->sale->where('due_amount', '<', 0)->sum('due_amount');

        $returnAmount   = $this->saleReturns->where('due_amount', '>', 0)->sum('due_amount');
        return abs($saleAmount) + $returnAmount;
    }

    public function fullname(): Attribute
    {
        return new Attribute(
            get: fn() => $this->name,
        );
    }

    public function mobileNumber(): Attribute
    {
        return new Attribute(
            get: fn() =>  $this->mobile,
        );
    }
    public function generateInvoice($startDate, $endDate, $search)
    {

        $directory = 'customer_pdf';
        $pdf = Pdf::loadView('partials.customer-pdf', ['customer' => $this, 'startDate' => $startDate, 'endDate' => $endDate, 'search' => $search]);

        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
        }

        $filename = 'customer' . $this->customer?->name . '.pdf';
        $filepath = $directory . '/' . $filename;

        Storage::disk('public')->put($filepath, $pdf->output());


        // $this->update(['customer' => $filepath]);

    }
}
