<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BatchOrder;
use App\Models\DeliveryTracking;
use App\Models\VehicleAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeliveryTrackingController extends Controller
{
    public function markDelivered(Request $request, $batchOrderId)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $batchOrder = BatchOrder::with(['sale', 'batch.vehicleAssignment'])
                ->findOrFail($batchOrderId);

            // Check if already delivered
            if ($batchOrder->delivery_status === 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already marked as delivered'
                ], 400);
            }

            // Update batch order status
            $batchOrder->update([
                'delivery_status' => 'delivered',
                'delivered_at' => now(),
                'delivery_notes' => $request->notes
            ]);

            // Update sale delivery status
            $batchOrder->sale->update([
                'delivery_status' => 'delivered'
            ]);

            // Create tracking record
            if ($batchOrder->batch->vehicleAssignment) {
                DeliveryTracking::create([
                    'assignment_id' => $batchOrder->batch->vehicleAssignment->id,
                    'batch_order_id' => $batchOrder->id,
                    'status' => 'delivered',
                    'notes' => $request->notes,
                    'tracked_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order marked as delivered successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function markFailed(Request $request, $batchOrderId)
    {
        $request->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $batchOrder = BatchOrder::with(['sale', 'batch.vehicleAssignment'])
                ->findOrFail($batchOrderId);

            // Check if already failed
            if ($batchOrder->delivery_status === 'failed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order is already marked as failed'
                ], 400);
            }

            $failureNote = $request->reason . ($request->notes ? ': ' . $request->notes : '');

            // Update batch order status
            $batchOrder->update([
                'delivery_status' => 'failed',
                'delivery_notes' => $failureNote
            ]);

            // Update sale delivery status - reset to ready for re-delivery
            $batchOrder->sale->update([
                'delivery_status' => 'ready'
            ]);

            // Create tracking record
            if ($batchOrder->batch->vehicleAssignment) {
                DeliveryTracking::create([
                    'assignment_id' => $batchOrder->batch->vehicleAssignment->id,
                    'batch_order_id' => $batchOrder->id,
                    'status' => 'failed',
                    'notes' => $failureNote,
                    'tracked_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order marked as failed successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getAssignmentStatus($assignmentId)
    {
        try {
            $assignment = VehicleAssignment::with([
                'batch.batchOrders.sale.customer',
                'batch.batchOrders.sale.saleDetails.product'
            ])->findOrFail($assignmentId);

            return response()->json([
                'success' => true,
                'orders' => $assignment->batch->batchOrders,
                'status' => $assignment->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function trackLocation(Request $request, $assignmentId)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'status' => 'required|in:picked,in_transit,arrived',
            'notes' => 'nullable|string'
        ]);

        try {
            $assignment = VehicleAssignment::findOrFail($assignmentId);

            // Check if assignment is active
            if (!in_array($assignment->status, ['assigned', 'in_progress'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot track location for inactive assignment'
                ], 400);
            }

            DeliveryTracking::create([
                'assignment_id' => $assignment->id,
                'status' => $request->status,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'notes' => $request->notes,
                'tracked_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location tracked successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to track location: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadProof(Request $request, $batchOrderId)
    {
        $request->validate([
            'signature' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $batchOrder = BatchOrder::with('batch.vehicleAssignment')
                ->findOrFail($batchOrderId);

            // Check if order is delivered
            if ($batchOrder->delivery_status !== 'delivered') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only upload proof for delivered orders'
                ], 400);
            }

            $tracking = DeliveryTracking::where('batch_order_id', $batchOrderId)
                ->where('status', 'delivered')
                ->latest()
                ->first();

            if (!$tracking) {
                $tracking = DeliveryTracking::create([
                    'assignment_id' => $batchOrder->batch->vehicleAssignment->id,
                    'batch_order_id' => $batchOrderId,
                    'status' => 'delivered',
                    'tracked_at' => now()
                ]);
            }

            $data = [];

            if ($request->hasFile('signature')) {
                // Delete old signature if exists
                if ($tracking->signature_image) {
                    Storage::disk('public')->delete($tracking->signature_image);
                }

                $signature = $request->file('signature');
                $signaturePath = $signature->store('delivery/signatures', 'public');
                $data['signature_image'] = $signaturePath;
            }

            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($tracking->delivery_photo) {
                    Storage::disk('public')->delete($tracking->delivery_photo);
                }

                $photo = $request->file('photo');
                $photoPath = $photo->store('delivery/photos', 'public');
                $data['delivery_photo'] = $photoPath;
            }

            $tracking->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proof uploaded successfully',
                'data' => [
                    'signature_url' => $tracking->signature_image ? Storage::url($tracking->signature_image) : null,
                    'photo_url' => $tracking->delivery_photo ? Storage::url($tracking->delivery_photo) : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload proof: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTrackingHistory($assignmentId)
    {
        try {
            $tracking = DeliveryTracking::with(['batchOrder.sale.customer'])
                ->where('assignment_id', $assignmentId)
                ->orderBy('tracked_at', 'desc')
                ->get()
                ->map(function ($track) {
                    return [
                        'id' => $track->id,
                        'status' => $track->status,
                        'latitude' => $track->latitude,
                        'longitude' => $track->longitude,
                        'notes' => $track->notes,
                        'tracked_at' => $track->tracked_at,
                        'signature_image' => $track->signature_image ? Storage::url($track->signature_image) : null,
                        'delivery_photo' => $track->delivery_photo ? Storage::url($track->delivery_photo) : null,
                        'order' => $track->batchOrder ? [
                            'id' => $track->batchOrder->id,
                            'invoice_no' => $track->batchOrder->sale->invoice_no,
                            'customer' => $track->batchOrder->sale->customer->name
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'tracking' => $tracking
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tracking history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrderTracking($batchOrderId)
    {
        try {
            $batchOrder = BatchOrder::with([
                'sale.customer',
                'batch.vehicleAssignment.vehicle'
            ])->findOrFail($batchOrderId);

            $tracking = DeliveryTracking::where('batch_order_id', $batchOrderId)
                ->orderBy('tracked_at', 'desc')
                ->get()
                ->map(function ($track) {
                    return [
                        'status' => $track->status,
                        'notes' => $track->notes,
                        'tracked_at' => $track->tracked_at,
                        'latitude' => $track->latitude,
                        'longitude' => $track->longitude,
                        'signature_image' => $track->signature_image ? Storage::url($track->signature_image) : null,
                        'delivery_photo' => $track->delivery_photo ? Storage::url($track->delivery_photo) : null
                    ];
                });

            return response()->json([
                'success' => true,
                'order' => [
                    'invoice_no' => $batchOrder->sale->invoice_no,
                    'customer' => $batchOrder->sale->customer->name,
                    'delivery_status' => $batchOrder->delivery_status,
                    'delivered_at' => $batchOrder->delivered_at,
                    'vehicle' => $batchOrder->batch->vehicleAssignment ? [
                        'vehicle_number' => $batchOrder->batch->vehicleAssignment->vehicle->vehicle_number,
                        'driver_name' => $batchOrder->batch->vehicleAssignment->vehicle->driver_name
                    ] : null
                ],
                'tracking' => $tracking
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order tracking: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteProof(Request $request, $trackingId)
    {
        $request->validate([
            'type' => 'required|in:signature,photo'
        ]);

        try {
            DB::beginTransaction();

            $tracking = DeliveryTracking::findOrFail($trackingId);

            if ($request->type === 'signature' && $tracking->signature_image) {
                Storage::disk('public')->delete($tracking->signature_image);
                $tracking->update(['signature_image' => null]);
            }

            if ($request->type === 'photo' && $tracking->delivery_photo) {
                Storage::disk('public')->delete($tracking->delivery_photo);
                $tracking->update(['delivery_photo' => null]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => ucfirst($request->type) . ' deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete proof: ' . $e->getMessage()
            ], 500);
        }
    }
}