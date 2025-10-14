<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryBatch;
use App\Models\BatchOrder;
use App\Models\Sale;
use App\Models\Area;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryBatchController extends Controller
{
    public function index()
    {
        $pageTitle = 'Delivery Batches';
        $batches = DeliveryBatch::with(['area', 'creator', 'batchOrders', 'vehicleAssignment.vehicle'])
            ->latest()
            ->paginate(getPaginate());

        return view('admin.delivery.batch.index', compact('pageTitle', 'batches'));
    }

    public function create()
    {
        $pageTitle = 'Create Delivery Batch';
        $areas = Area::where('status', 1)->get();

        // Get orders ready for delivery
        $orders = Sale::with(['customer.area', 'saleDetails.product'])
            // ->readyForDelivery()
            ->latest()
            ->get();

        return view('admin.delivery.batch.create', compact('pageTitle', 'areas', 'orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'nullable|exists:areas,id',
            'delivery_date' => 'required|date',
            'orders' => 'required|array|min:1',
            'orders.*' => 'exists:sales,id',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $orders = Sale::whereIn('id', $request->orders)->get();
            $totalAmount = $orders->sum('grand_total');

            // Create batch
            $batch = DeliveryBatch::create([
                'created_by' => auth()->id(),
                'area_id' => $request->area_id,
                'delivery_date' => $request->delivery_date,
                'notes' => $request->notes,
                'total_orders' => count($request->orders),
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            // Add orders to batch
            foreach ($request->orders as $index => $orderId) {
                BatchOrder::create([
                    'batch_id' => $batch->id,
                    'sale_id' => $orderId,
                    'sort_order' => $index + 1
                ]);

                // Update sale delivery status
                Sale::where('id', $orderId)->update([
                    'delivery_status' => 'batched'
                ]);
            }

            DB::commit();

            $notify[] = ['success', 'Delivery batch created successfully'];
            return response()->json([
                'success' => true,
                'message' => 'Delivery batch created successfully',
                'redirect' => route('admin.delivery.batch.show', $batch->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pageTitle = 'Batch Details';
        $batch = DeliveryBatch::with([
            'batchOrders.sale.customer.area',
            'batchOrders.sale.saleDetails.product',
            'area',
            'vehicleAssignment.vehicle',
            'vehicleAssignment.containers.items.product'
        ])->findOrFail($id);

        $availableVehicles = Vehicle::available()->get();

        return view('admin.delivery.batch.show', compact('pageTitle', 'batch', 'availableVehicles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'delivery_date' => 'required|date',
            'status' => 'required|in:pending,assigned,in_transit,delivered,cancelled',
            'notes' => 'nullable|string'
        ]);

        $batch = DeliveryBatch::findOrFail($id);
        $batch->update($request->only(['delivery_date', 'status', 'notes']));

        $notify[] = ['success', 'Batch updated successfully'];
        return back()->withNotify($notify);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $batch = DeliveryBatch::findOrFail($id);

            // Check if batch has been assigned
            if ($batch->vehicleAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete batch that has been assigned to a vehicle'
                ], 400);
            }

            // Reset order delivery status
            Sale::whereIn('id', $batch->batchOrders->pluck('sale_id'))
                ->update(['delivery_status' => 'ready']);

            $batch->delete();

            DB::commit();

            $notify[] = ['success', 'Batch deleted successfully'];
            return back()->withNotify($notify);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrdersByArea($areaId)
    {
        $orders = Sale::with(['customer', 'saleDetails.product'])
            ->readyForDelivery()
            ->whereHas('customer', function($q) use ($areaId) {
                $q->where('area_id', $areaId);
            })
            ->get();

        return response()->json($orders);
    }

    public function updateOrderSequence(Request $request, $id)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:batch_orders,id',
            'orders.*.sort_order' => 'required|integer'
        ]);

        try {
            foreach ($request->orders as $order) {
                BatchOrder::where('id', $order['id'])
                    ->update(['sort_order' => $order['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order sequence updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sequence: ' . $e->getMessage()
            ], 500);
        }
    }
}