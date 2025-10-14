<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AreaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $pageTitle = 'Manage Delivery Areas';
        $emptyMessage = 'No areas found';

        $areas = Area::withCount('customers')
                     ->with('customers:id,name')
                     ->latest()
                     ->paginate(10);

        $customers = Customer::orderBy('name')->get(['id', 'name', 'address']);
        $areasData = $areas->items();

        return view('admin.area.index', compact('pageTitle', 'areas', 'customers', 'areasData', 'emptyMessage'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'description' => 'nullable|string',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        try {
            DB::beginTransaction();

            $area = Area::create([
                'name' => $request->name,
                'description' => $request->description,
                'status' => 1,
            ]);

            if ($request->customer_ids) {
                $area->customers()->attach($request->customer_ids);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Area created successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Area creation failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Area creation failed.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Area $area)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:areas,name,' . $area->id,
            'description' => 'nullable|string',
            'status' => 'required|integer|in:0,1',
            'customer_ids' => 'nullable|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        try {
            DB::beginTransaction();

            $area->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
            ]);

            $area->customers()->sync($request->customer_ids ?: []);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Area updated successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Area update failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Area update failed.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Area $area)
    {
        try {
            DB::beginTransaction();

            $area->customers()->detach();
            $area->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Area deleted successfully.']);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Area deletion failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Area deletion failed.'], 500);
        }
    }

}