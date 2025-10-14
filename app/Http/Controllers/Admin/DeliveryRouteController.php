<?php
// app/Http/Controllers/Admin/DeliveryRouteController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class DeliveryRouteController extends Controller
{
    /**
     * Show the main view for creating delivery batches.
     */
    public function index(): View
    {
        // 1. Initial data required for the page
        $areas = Area::all(['id', 'name']);

        // 2. Initial list of ALL available orders (unfiltered)
        // You might replace this with an empty array if you enforce Area filtering first
        $availableOrders = Sale::whereIn('status', ['confirmed', 'processing'])
            ->whereNull('delivery_batch_id')
            ->with('customer', 'saleDetails')
            ->get();

        // You'll need to define what these other variables are
        $pageTitle = 'Create New Delivery Batch';
        $vehicles = []; // Placeholder for vehicles
        $drivers = [];  // Placeholder for drivers

        return view('admin.delivery.route_create', compact('pageTitle', 'areas', 'availableOrders', 'vehicles', 'drivers'));
    }

    /**
     * API endpoint to fetch available orders filtered by the selected Area.
     */
    public function getOrdersByArea(Request $request)
    {
        $request->validate(['area_id' => 'required|exists:areas,id']);

        $areaId = $request->area_id;

        // 1. Get all Customer IDs belonging to the selected Area
        $customerIds = Area::findOrFail($areaId)->customers()->pluck('customers.id');

        // 2. Fetch all ready orders for those specific customers/shops
        $readyOrders = Sale::whereIn('customer_id', $customerIds)
            ->whereIn('status', ['confirmed', 'processing'])
            ->whereNull('delivery_batch_id')
            ->with(['customer' => function ($query) {
                // Select only the necessary fields for the display table
                $query->select('id', 'name', 'address');
            }, 'saleDetails'])
            ->get();

        return Response::json(['orders' => $readyOrders]);
    }

    // Add your logic for saving the delivery batch here later
    public function store(Request $request)
    {
        // ... Logic to create the delivery batch/route ...
    }
}
