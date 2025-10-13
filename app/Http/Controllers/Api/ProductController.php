<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Order;
use App\Models\Sale;
use App\Models\SaleDetails;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function getAllProducts()
    {
        $products = Product::with(['brand:id,name', 'category:id,name', 'unit:id,name'])
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }
 
    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'status' => 'success',
            'data' => $categories,
        ]);
    }
    public function getAllBrands()
    {
        $brands = Brand::all();

        return response()->json([
            'status' => 'success',
            'data' => $brands,
        ]);
    }
    public function getAllUnits()
    {
        $units = Unit::all();

        return response()->json([
            'status' => 'success',
            'data' => $units,
        ]);
    }

}
