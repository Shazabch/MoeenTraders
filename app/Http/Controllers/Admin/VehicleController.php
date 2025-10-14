<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $pageTitle = 'Vehicles';
        $vehicles = Vehicle::latest()->paginate(getPaginate());

        return view('admin.vehicle.index', compact('pageTitle', 'vehicles'));
    }

    public function create()
    {
        $pageTitle = 'Add Vehicle';
        return view('admin.vehicle.form', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_number' => 'required|string|unique:vehicles,vehicle_number',
            'vehicle_type' => 'required|string',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'driver_license' => 'nullable|string|max:50',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        Vehicle::create([
            'vehicle_number' => $request->vehicle_number,
            'vehicle_type' => $request->vehicle_type,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'driver_license' => $request->driver_license,
            'capacity_weight' => $request->capacity_weight,
            'capacity_volume' => $request->capacity_volume,
            'notes' => $request->notes,
            'status' => 'available',
            'is_active' => true
        ]);

        $notify[] = ['success', 'Vehicle added successfully'];
        return redirect()->route('admin.vehicle.index')->withNotify($notify);
    }

    public function edit($id)
    {
        $pageTitle = 'Edit Vehicle';
        $vehicle = Vehicle::findOrFail($id);

        return view('admin.vehicle.form', compact('pageTitle', 'vehicle'));
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $request->validate([
            'vehicle_number' => 'required|string|unique:vehicles,vehicle_number,' . $id,
            'vehicle_type' => 'required|string',
            'driver_name' => 'required|string|max:255',
            'driver_phone' => 'required|string|max:20',
            'driver_license' => 'nullable|string|max:50',
            'capacity_weight' => 'nullable|numeric|min:0',
            'capacity_volume' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:available,assigned,in_transit,maintenance'
        ]);

        $vehicle->update($request->all());

        $notify[] = ['success', 'Vehicle updated successfully'];
        return redirect()->route('admin.vehicle.index')->withNotify($notify);
    }

    public function toggleStatus($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if vehicle is currently assigned
        if ($vehicle->currentAssignment) {
            $notify[] = ['error', 'Cannot deactivate vehicle that is currently assigned'];
            return back()->withNotify($notify);
        }

        $vehicle->is_active = !$vehicle->is_active;
        $vehicle->save();

        $status = $vehicle->is_active ? 'activated' : 'deactivated';
        $notify[] = ['success', "Vehicle {$status} successfully"];

        return back()->withNotify($notify);
    }

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Check if vehicle has any assignments
        if ($vehicle->assignments()->count() > 0) {
            $notify[] = ['error', 'Cannot delete vehicle with existing assignments'];
            return back()->withNotify($notify);
        }

        $vehicle->delete();

        $notify[] = ['success', 'Vehicle deleted successfully'];
        return back()->withNotify($notify);
    }

    public function assignments($id)
    {
        $pageTitle = 'Vehicle Assignments';
        $vehicle = Vehicle::with(['assignments.batch', 'assignments.assignedBy'])
            ->findOrFail($id);

        return view('admin.vehicle.assignments', compact('pageTitle', 'vehicle'));
    }
}