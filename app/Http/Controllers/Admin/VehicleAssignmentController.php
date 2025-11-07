<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\VehicleAssignment;
use App\Models\VehicleContainer;
use App\Models\ContainerItem;
use App\Models\DeliveryBatch;
use App\Models\Vehicle;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleAssignmentController extends Controller
{
    public function index()
    {
        $pageTitle = 'Vehicle Assignments';
        $query = VehicleAssignment::query();
        $user=Auth::guard('admin')->user();
        if($user->role_id!=0){
            $query->where('assigned_to',$user->id);
        }

        $assignments = $query->with(['vehicle', 'batch', 'assignedBy'])->latest()->paginate(getPaginate());

        return view('admin.delivery.assignment.index', compact('pageTitle', 'assignments'));
    }

    public function create($batchId)
    {
        $pageTitle = 'Assign Vehicle';
        $batch = DeliveryBatch::with(['batchOrders.sale.saleDetails.product.category', 'batchOrders.sale.customer', 'area'])
            ->findOrFail($batchId);

        // Check if already assigned
        if ($batch->vehicleAssignment) {
            $notify[] = ['error', 'This batch is already assigned to a vehicle'];
            return redirect()->route('admin.delivery.batch.show', $batchId)->withNotify($notify);
        }

        $vehicles = Vehicle::where('status', 'available')->get();
        $salemanRole = Role::firstOrCreate(['name' => 'Sales Man']);

        $salesmans = Admin::where('role_id', $salemanRole->id)->get();

        // Check if there are available vehicles
        if ($vehicles->isEmpty()) {
            $notify[] = ['error', 'No available vehicles found'];
            return redirect()->route('admin.delivery.batch.show', $batchId)->withNotify($notify);
        }

        return view('admin.delivery.assignment.create', compact('pageTitle', 'batch', 'vehicles','salesmans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_id' => 'required|exists:delivery_batches,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'assigned_to' => 'required|exists:admins,id',
            'starting_km' => 'nullable|numeric|min:0',
            'containers' => 'required|array|min:1',
            'containers.*.name' => 'required|string|max:255',
            'containers.*.notes' => 'nullable|string|max:500',
            'containers.*.items' => 'required|array|min:1',
            'containers.*.items.*.product_id' => 'required|exists:products,id',
            'containers.*.items.*.quantity' => 'required|integer|min:1',
            'containers.*.items.*.sale_id' => 'nullable|exists:sales,id',
            'containers.*.items.*.type' => 'required|in:order,extra',
            'containers.*.items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $batch = DeliveryBatch::with(['batchOrders'])->findOrFail($request->batch_id);
            $vehicle = Vehicle::findOrFail($request->vehicle_id);
            $saleman = Admin::findOrFail($request->assigned_to);

            // Check if batch already assigned
            if ($batch->vehicleAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'This batch is already assigned to a vehicle'
                ], 400);
            }

            // Check if vehicle is available
            if ($vehicle->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Selected vehicle is not available'
                ], 400);
            }
             // Check if batch already assigned
            if (!$saleman) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saleman not Found'
                ], 400);
            }

            // Validate products exist
            $productIds = collect($request->containers)
                ->pluck('items')
                ->flatten(1)
                ->pluck('product_id')
                ->unique();

            $existingProducts = Product::whereIn('id', $productIds)->pluck('id');
            $missingProducts = $productIds->diff($existingProducts);

            if ($missingProducts->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products do not exist'
                ], 400);
            }

            // Validate sale_ids if provided
            $saleIds = collect($request->containers)
                ->pluck('items')
                ->flatten(1)
                ->where('type', 'order')
                ->pluck('sale_id')
                ->filter()
                ->unique();

            if ($saleIds->isNotEmpty()) {
                $batchSaleIds = $batch->batchOrders->pluck('sale_id');
                $invalidSales = $saleIds->diff($batchSaleIds);

                if ($invalidSales->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some orders do not belong to this batch'
                    ], 400);
                }
            }

            // Create assignment
            $assignment = VehicleAssignment::create([
                'vehicle_id' => $request->vehicle_id,
                'batch_id' => $request->batch_id,
                'assigned_to' => $request->assigned_to,
                'assigned_by' => auth()->guard('admin')->id(),
                'assigned_at' => now(),
                'starting_km' => $request->starting_km ?? 0,
                'status' => 'assigned',
                'notes' => $request->notes
            ]);

            // Create containers and items
            foreach ($request->containers as $index => $containerData) {
                // Check for empty items array
                if (empty($containerData['items'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Container "' . $containerData['name'] . '" must have at least one item'
                    ], 400);
                }

                $container = VehicleContainer::create([
                    'assignment_id' => $assignment->id,
                    'container_name' => $containerData['name'],
                    'position' => $index + 1,
                    'notes' => $containerData['notes'] ?? null
                ]);

                foreach ($containerData['items'] as $itemData) {
                    // Validate sale_id is provided for order type
                    if ($itemData['type'] === 'order' && empty($itemData['sale_id'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Sale ID is required for order items'
                        ], 400);
                    }

                    ContainerItem::create([
                        'container_id' => $container->id,
                        'sale_id' => $itemData['sale_id'] ?? null,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'item_type' => $itemData['type'],
                        'notes' => $itemData['notes'] ?? null
                    ]);
                }
            }

            // Update batch status
            $batch->update(['status' => 'assigned']);

            // Update vehicle status
            $vehicle->update(['status' => 'assigned']);

            // Update sales delivery status
            Sale::whereIn('id', $batch->batchOrders->pluck('sale_id'))
                ->update(['delivery_status' => 'assigned']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vehicle assigned successfully',
                'redirect' => route('admin.delivery.assignment.show', $assignment->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Vehicle Assignment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign vehicle. Please try again.'
            ], 500);
        }
    }

    public function show($id)
    {
        $pageTitle = 'Assignment Details';
        $assignment = VehicleAssignment::with([
            'vehicle',
            'batch.batchOrders.sale.customer.area',
            'batch.batchOrders.sale.saleDetails.product',
            'containers.items.product',
            'containers.items.sale',
            'assignedBy'
        ])->findOrFail($id);

        return view('admin.delivery.assignment.show', compact('pageTitle', 'assignment'));
    }

    public function startDelivery(Request $request, $id)
    {
        $request->validate([
            'starting_km' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $assignment = VehicleAssignment::with(['batch.batchOrders', 'vehicle'])->findOrFail($id);

            if ($assignment->status !== 'assigned') {
                $notify[] = ['error', 'Assignment is not in assigned status'];
                return back()->withNotify($notify);
            }

            // Validate starting_km
            if ($request->starting_km < $assignment->starting_km) {
                $notify[] = ['error', 'Starting KM cannot be less than initial odometer reading'];
                return back()->withNotify($notify);
            }

            $assignment->update([
                'status' => 'in_progress',
                'started_at' => now(),
                'starting_km' => $request->starting_km
            ]);

            // Update batch status
            $assignment->batch->update(['status' => 'in_transit']);

            // Update vehicle status
            $assignment->vehicle->update(['status' => 'in_transit']);

            // Update sales status
            Sale::whereIn('id', $assignment->batch->batchOrders->pluck('sale_id'))
                ->update(['delivery_status' => 'in_transit']);

            DB::commit();

            $notify[] = ['success', 'Delivery started successfully'];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Start Delivery Error: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to start delivery. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function completeDelivery(Request $request, $id)
    {
        $request->validate([
            'ending_km' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $assignment = VehicleAssignment::with(['batch.batchOrders', 'vehicle'])->findOrFail($id);

            if ($assignment->status !== 'in_progress') {
                $notify[] = ['error', 'Assignment is not in progress'];
                return back()->withNotify($notify);
            }

            // Validate ending_km is greater than starting_km
            if ($request->ending_km <= $assignment->starting_km) {
                $notify[] = ['error', 'Ending odometer must be greater than starting odometer'];
                return back()->withNotify($notify);
            }

            $assignment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'ending_km' => $request->ending_km
            ]);

            // Update batch status
            $assignment->batch->update(['status' => 'delivered']);

            // Update vehicle status
            $assignment->vehicle->update(['status' => 'available']);

            // Update sales status for all orders in batch
            Sale::whereIn('id', $assignment->batch->batchOrders->pluck('sale_id'))
                ->update(['delivery_status' => 'delivered']);

            DB::commit();

            $notify[] = ['success', 'Delivery completed successfully'];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Complete Delivery Error: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to complete delivery. Please try again.'];
            return back()->withNotify($notify);
        }
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Assignment';
        $assignment = VehicleAssignment::with([
            'vehicle',
            'batch.batchOrders.sale.saleDetails.product',
            'batch.batchOrders.sale.customer',
            'containers.items.product',
            'containers.items.sale'
        ])->findOrFail($id);

        // Check if delivery has started
        if ($assignment->status !== 'assigned') {
            $notify[] = ['error', 'Cannot edit assignment after delivery has started'];
            return redirect()->route('admin.delivery.assignment.show', $id)->withNotify($notify);
        }

        $vehicles = Vehicle::where(function ($query) use ($assignment) {
            $query->where('status', 'available')
                ->orWhere('id', $assignment->vehicle_id);
        })->get();

        return view('admin.delivery.assignment.edit', compact('pageTitle', 'assignment', 'vehicles'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'starting_km' => 'nullable|numeric|min:0',
            'containers' => 'required|array|min:1',
            'containers.*.name' => 'required|string|max:255',
            'containers.*.notes' => 'nullable|string|max:500',
            'containers.*.items' => 'required|array|min:1',
            'containers.*.items.*.product_id' => 'required|exists:products,id',
            'containers.*.items.*.quantity' => 'required|integer|min:1',
            'containers.*.items.*.sale_id' => 'nullable|exists:sales,id',
            'containers.*.items.*.type' => 'required|in:order,extra',
            'containers.*.items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $assignment = VehicleAssignment::with(['vehicle', 'batch.batchOrders', 'containers.items'])->findOrFail($id);

            // Check if assignment can be edited
            if ($assignment->status !== 'assigned') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot edit assignment after delivery has started'
                ], 400);
            }

            $oldVehicleId = $assignment->vehicle_id;
            $newVehicleId = $request->vehicle_id;

            // Check if new vehicle is available
            if ($newVehicleId != $oldVehicleId) {
                $newVehicle = Vehicle::findOrFail($newVehicleId);
                if ($newVehicle->status !== 'available') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected vehicle is not available'
                    ], 400);
                }
            }

            // Validate products exist
            $productIds = collect($request->containers)
                ->pluck('items')
                ->flatten(1)
                ->pluck('product_id')
                ->unique();

            $existingProducts = Product::whereIn('id', $productIds)->pluck('id');
            $missingProducts = $productIds->diff($existingProducts);

            if ($missingProducts->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Some products do not exist'
                ], 400);
            }

            // Validate sale_ids if provided
            $saleIds = collect($request->containers)
                ->pluck('items')
                ->flatten(1)
                ->where('type', 'order')
                ->pluck('sale_id')
                ->filter()
                ->unique();

            if ($saleIds->isNotEmpty()) {
                $batchSaleIds = $assignment->batch->batchOrders->pluck('sale_id');
                $invalidSales = $saleIds->diff($batchSaleIds);

                if ($invalidSales->isNotEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some orders do not belong to this batch'
                    ], 400);
                }
            }

            // Update assignment
            $assignment->update([
                'vehicle_id' => $newVehicleId,
                'starting_km' => $request->starting_km ?? 0,
                'notes' => $request->notes
            ]);

            // Handle vehicle status changes
            if ($newVehicleId != $oldVehicleId) {
                // Free old vehicle
                Vehicle::where('id', $oldVehicleId)->update(['status' => 'available']);

                // Assign new vehicle
                Vehicle::where('id', $newVehicleId)->update(['status' => 'assigned']);
            }

            // Get existing container and item IDs for cleanup
            $existingContainerIds = $assignment->containers->pluck('id');
            $existingItemIds = $assignment->containers->pluck('items')->flatten()->pluck('id');

            $updatedContainerIds = [];
            $updatedItemIds = [];

            // Update containers and items
            foreach ($request->containers as $index => $containerData) {
                // Check for empty items array
                if (empty($containerData['items'])) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Container "' . $containerData['name'] . '" must have at least one item'
                    ], 400);
                }

                // Update or create container
                if (isset($containerData['id']) && $containerData['id']) {
                    $container = VehicleContainer::where('assignment_id', $assignment->id)
                        ->where('id', $containerData['id'])
                        ->first();

                    if ($container) {
                        $container->update([
                            'container_name' => $containerData['name'],
                            'position' => $index + 1,
                            'notes' => $containerData['notes'] ?? null
                        ]);
                        $updatedContainerIds[] = $container->id;
                    } else {
                        $container = VehicleContainer::create([
                            'assignment_id' => $assignment->id,
                            'container_name' => $containerData['name'],
                            'position' => $index + 1,
                            'notes' => $containerData['notes'] ?? null
                        ]);
                        $updatedContainerIds[] = $container->id;
                    }
                } else {
                    $container = VehicleContainer::create([
                        'assignment_id' => $assignment->id,
                        'container_name' => $containerData['name'],
                        'position' => $index + 1,
                        'notes' => $containerData['notes'] ?? null
                    ]);
                    $updatedContainerIds[] = $container->id;
                }

                // Update or create items
                foreach ($containerData['items'] as $itemIndex => $itemData) {
                    // Validate sale_id is provided for order type
                    if ($itemData['type'] === 'order' && empty($itemData['sale_id'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Sale ID is required for order items'
                        ], 400);
                    }

                    if (isset($itemData['id']) && $itemData['id']) {
                        $item = ContainerItem::where('container_id', $container->id)
                            ->where('id', $itemData['id'])
                            ->first();

                        if ($item) {
                            $item->update([
                                'sale_id' => $itemData['sale_id'] ?? null,
                                'product_id' => $itemData['product_id'],
                                'quantity' => $itemData['quantity'],
                                'item_type' => $itemData['type'],
                                'notes' => $itemData['notes'] ?? null
                            ]);
                            $updatedItemIds[] = $item->id;
                        } else {
                            $item = ContainerItem::create([
                                'container_id' => $container->id,
                                'sale_id' => $itemData['sale_id'] ?? null,
                                'product_id' => $itemData['product_id'],
                                'quantity' => $itemData['quantity'],
                                'item_type' => $itemData['type'],
                                'notes' => $itemData['notes'] ?? null
                            ]);
                            $updatedItemIds[] = $item->id;
                        }
                    } else {
                        $item = ContainerItem::create([
                            'container_id' => $container->id,
                            'sale_id' => $itemData['sale_id'] ?? null,
                            'product_id' => $itemData['product_id'],
                            'quantity' => $itemData['quantity'],
                            'item_type' => $itemData['type'],
                            'notes' => $itemData['notes'] ?? null
                        ]);
                        $updatedItemIds[] = $item->id;
                    }
                }
            }

            // Clean up deleted containers and items
            VehicleContainer::where('assignment_id', $assignment->id)
                ->whereNotIn('id', $updatedContainerIds)
                ->delete();

            ContainerItem::whereIn('container_id', $existingContainerIds)
                ->whereNotIn('id', $updatedItemIds)
                ->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'redirect' => route('admin.delivery.assignment.show', $assignment->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Assignment Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment. Please try again.'
            ], 500);
        }
    }
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $assignment = VehicleAssignment::with(['vehicle', 'batch.batchOrders'])->findOrFail($id);

            // Check if delivery has started
            if ($assignment->status !== 'assigned') {
                $notify[] = ['error', 'Cannot delete assignment after delivery has started'];
                return back()->withNotify($notify);
            }

            // Reset vehicle status
            $assignment->vehicle->update(['status' => 'available']);

            // Reset batch status
            $assignment->batch->update(['status' => 'pending']);

            // Reset sales delivery status
            Sale::whereIn('id', $assignment->batch->batchOrders->pluck('sale_id'))
                ->update(['delivery_status' => 'batched']);

            // Delete assignment (will cascade delete containers and items)
            $assignment->delete();

            DB::commit();

            $notify[] = ['success', 'Assignment deleted successfully'];
            return redirect()->route('admin.delivery.assignment.index')->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete Assignment Error: ' . $e->getMessage());
            $notify[] = ['error', 'Failed to delete assignment. Please try again.'];
            return back()->withNotify($notify);
        }
    }
}
