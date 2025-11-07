<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VehicleAssignment;
use App\Models\DeliveryBatch;
use App\Models\BatchOrder;
use App\Models\Sale;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DeliveryDashboardController extends Controller
{
    public function index()
    {
        $pageTitle = 'Delivery Dashboard';
        return view('admin.delivery.dashboard', compact('pageTitle'));
    }

    public function getData()
    {
        try {
            // Statistics
            $stats = [
                'active_deliveries' => VehicleAssignment::whereIn('status', ['assigned', 'in_progress'])->count(),
                'delivered_today' => BatchOrder::where('delivery_status', 'delivered')
                    ->whereDate('delivered_at', today())
                    ->count(),
                'pending_orders' => Sale::where('delivery_status', 'ready')
                    ->whereNotIn('status', ['cancelled'])
                    ->count(),
                'available_vehicles' => Vehicle::where('status', 'available')
                    ->where('is_active', true)
                    ->count()
            ];

            // Active Deliveries
            $activeDeliveries = VehicleAssignment::with([
                'vehicle',
                'batch.batchOrders'
            ])
                ->whereIn('status', ['assigned', 'in_progress'])
                ->get()
                ->map(function ($assignment) {
                    $totalOrders = $assignment->batch->batchOrders->count();
                    $deliveredOrders = $assignment->batch->batchOrders
                        ->where('delivery_status', 'delivered')
                        ->count();

                    $progress = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100) : 0;

                    return [
                        'id' => $assignment->id,
                        'status' => $assignment->status,
                        'progress' => $progress,
                        'batch' => [
                            'id' => $assignment->batch->id,
                            'batch_number' => $assignment->batch->batch_number,
                            'delivery_date' => $assignment->batch->delivery_date,
                            'total_orders' => $totalOrders,
                            'total_amount' => $assignment->batch->total_amount
                        ],
                        'vehicle' => [
                            'vehicle_number' => $assignment->vehicle->vehicle_number,
                            'driver_name' => $assignment->vehicle->driver_name,
                            'driver_phone' => $assignment->vehicle->driver_phone
                        ]
                    ];
                });

            // Recent Batches
            $recentBatches = DeliveryBatch::with(['area'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($batch) {
                    return [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'area' => $batch->area ? ['name' => $batch->area->name] : null,
                        'total_orders' => $batch->total_orders,
                        'total_amount' => $batch->total_amount,
                        'delivery_date' => $batch->delivery_date,
                        'status' => $batch->status
                    ];
                });

            // Ready Orders
            $readyOrders = Sale::with(['customer'])
                ->where('delivery_status', 'ready')
                ->whereNotIn('status', ['cancelled'])
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($sale) {
                    return [
                        'id' => $sale->id,
                        'invoice_no' => $sale->invoice_no,
                        'grand_total' => $sale->grand_total,
                        'customer' => [
                            'name' => $sale->customer->name
                        ]
                    ];
                });

            // Vehicles
            $vehicles = Vehicle::where('is_active', true)
                ->get()
                ->map(function ($vehicle) {
                    return [
                        'id' => $vehicle->id,
                        'vehicle_number' => $vehicle->vehicle_number,
                        'driver_name' => $vehicle->driver_name,
                        'status' => $vehicle->status
                    ];
                });

            // Week Statistics
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $weekDeliveries = BatchOrder::whereBetween('delivered_at', [$startOfWeek, $endOfWeek]);

            $totalWeekDeliveries = $weekDeliveries->count();
            $successfulDeliveries = $weekDeliveries->where('delivery_status', 'delivered')->count();
            $failedDeliveries = $weekDeliveries->where('delivery_status', 'failed')->count();

            $successRate = $totalWeekDeliveries > 0
                ? round(($successfulDeliveries / $totalWeekDeliveries) * 100)
                : 0;

            $weekStats = [
                'total_deliveries' => $totalWeekDeliveries,
                'successful' => $successfulDeliveries,
                'failed' => $failedDeliveries,
                'success_rate' => $successRate
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'active_deliveries' => $activeDeliveries,
                'recent_batches' => $recentBatches,
                'ready_orders' => $readyOrders,
                'vehicles' => $vehicles,
                'week_stats' => $weekStats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reports(Request $request)
    {
        $pageTitle = 'Delivery Reports';

        $dateRange = $request->input('date_range', 'today');

        switch ($dateRange) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today();
                break;
            case 'week':
                $startDate = Carbon::now()->startOfWeek();
                $endDate = Carbon::now()->endOfWeek();
                break;
            case 'month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'custom':
                $startDate = Carbon::parse($request->input('start_date'));
                $endDate = Carbon::parse($request->input('end_date'));
                break;
            default:
                $startDate = Carbon::today();
                $endDate = Carbon::today();
        }

        // Delivery Performance
        $deliveries = BatchOrder::whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN delivery_status = "delivered" THEN 1 ELSE 0 END) as delivered,
                SUM(CASE WHEN delivery_status = "failed" THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN delivery_status = "pending" THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        // Vehicle Performance
        $vehiclePerformance = VehicleAssignment::with('vehicle')
            ->whereBetween('assigned_at', [$startDate, $endDate])
            ->get()
            ->map(function ($assignment) {
                $totalOrders = $assignment->batch->batchOrders->count();
                $deliveredOrders = $assignment->batch->batchOrders
                    ->where('delivery_status', 'delivered')
                    ->count();

                return [
                    'vehicle' => $assignment->vehicle->vehicle_number,
                    'driver' => $assignment->vehicle->driver_name,
                    'total_orders' => $totalOrders,
                    'delivered' => $deliveredOrders,
                    'success_rate' => $totalOrders > 0
                        ? round(($deliveredOrders / $totalOrders) * 100)
                        : 0,
                    'distance' => $assignment->ending_km && $assignment->starting_km
                        ? $assignment->ending_km - $assignment->starting_km
                        : 0
                ];
            });

        // Area Performance
        $areaPerformance = DeliveryBatch::with(['area', 'batchOrders'])
            ->whereBetween('delivery_date', [$startDate, $endDate])
            ->whereNotNull('area_id')
            ->get()
            ->groupBy('area_id')
            ->map(function ($batches, $areaId) {
                $totalOrders = $batches->sum(function ($batch) {
                    return $batch->batchOrders->count();
                });

                $deliveredOrders = $batches->sum(function ($batch) {
                    return $batch->batchOrders->where('delivery_status', 'delivered')->count();
                });

                return [
                    'area' => $batches->first()->area->name,
                    'total_orders' => $totalOrders,
                    'delivered' => $deliveredOrders,
                    'success_rate' => $totalOrders > 0
                        ? round(($deliveredOrders / $totalOrders) * 100)
                        : 0
                ];
            })
            ->values();

        return view('admin.delivery.reports', compact(
            'pageTitle',
            'deliveries',
            'vehiclePerformance',
            'areaPerformance',
            'startDate',
            'endDate'
        ));
    }

    public function exportReport(Request $request)
    {
        // Implement export functionality (CSV, PDF, Excel)
        // This would use packages like maatwebsite/excel or dompdf

        $dateRange = $request->input('date_range', 'today');
        $format = $request->input('format', 'csv');

        // Sample implementation - you would expand this based on your needs
        try {
            // Generate report data
            $data = $this->generateReportData($dateRange);

            switch ($format) {
                case 'csv':
                    return $this->exportToCsv($data);
                case 'pdf':
                    return $this->exportToPdf($data);
                case 'excel':
                    return $this->exportToExcel($data);
                default:
                    return response()->json(['error' => 'Invalid format'], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateReportData($dateRange)
    {
        // Implement report data generation based on date range
        // Return formatted data array
        return [];
    }

    private function exportToCsv($data)
    {
        // Implement CSV export
    }

    private function exportToPdf($data)
    {
        // Implement PDF export
    }

    private function exportToExcel($data)
    {
        // Implement Excel export
    }
    public function updateBatchOrderStatus(Request $request, $assignment_id)
    {
        $request->validate([
            'order_id' => 'required|exists:batch_orders,id',
            // Note: The statuses here should match what you allow the sales manager to select
            'delivery_status' => 'required|in:pending,delivered,cancelled,failed',
        ]);

        try {
            // Find the specific BatchOrder record
            $batchOrder = BatchOrder::findOrFail($request->order_id);

            // Check if the BatchOrder belongs to the current Assignment/Batch (optional security layer)
            if ($batchOrder->batch_id != $assignment->batch_id) {
                // Return an error if the order doesn't belong to the batch
                // or you can simply proceed without this check if the order_id is secure.
            }

            // Update the delivery status on the BatchOrder record
            $batchOrder->delivery_status = $request->delivery_status;
            $batchOrder->save();

            // OPTIONAL: You might also need to update the global 'Sale' model status
            // if the batch order status should reflect globally.
            // This depends on your application logic. E.g.,
            // if ($request->delivery_status == 'delivered') {
            //     $batchOrder->sale->status = 'delivered';
            //     $batchOrder->sale->save();
            // }

            $notify[] = ['success', 'Order #' . $batchOrder->sale->invoice_no . ' delivery status updated to ' . ucfirst($request->delivery_status) . ' successfully.'];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Could not update order status. Error: ' . $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
}
