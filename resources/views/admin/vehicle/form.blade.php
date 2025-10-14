@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <form action="{{ isset($vehicle) ? route('admin.vehicle.update', $vehicle->id) : route('admin.vehicle.store') }}"
                      method="POST">
                    @csrf

                    <div class="modal-body">
                        <div class="row">
                            <!-- Vehicle Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Vehicle Number') <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="vehicle_number"
                                           class="form-control"
                                           value="{{ old('vehicle_number', $vehicle->vehicle_number ?? '') }}"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Vehicle Type') <span class="text-danger">*</span></label>
                                    <select name="vehicle_type" class="form-control" required>
                                        <option value="">@lang('Select Type')</option>
                                        <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'truck' ? 'selected' : '' }}>@lang('Truck')</option>
                                        <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'van' ? 'selected' : '' }}>@lang('Van')</option>
                                        <option value="pickup" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'pickup' ? 'selected' : '' }}>@lang('Pickup')</option>
                                        <option value="bike" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'bike' ? 'selected' : '' }}>@lang('Bike')</option>
                                        <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'car' ? 'selected' : '' }}>@lang('Car')</option>
                                        <option value="other" {{ old('vehicle_type', $vehicle->vehicle_type ?? '') == 'other' ? 'selected' : '' }}>@lang('Other')</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Driver Information -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Driver Name') <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="driver_name"
                                           class="form-control"
                                           value="{{ old('driver_name', $vehicle->driver_name ?? '') }}"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Driver Phone') <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="driver_phone"
                                           class="form-control"
                                           value="{{ old('driver_phone', $vehicle->driver_phone ?? '') }}"
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Driver License Number')</label>
                                    <input type="text"
                                           name="driver_license"
                                           class="form-control"
                                           value="{{ old('driver_license', $vehicle->driver_license ?? '') }}">
                                </div>
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Capacity Weight (kg)')</label>
                                    <input type="number"
                                           name="capacity_weight"
                                           class="form-control"
                                           value="{{ old('capacity_weight', $vehicle->capacity_weight ?? '') }}"
                                           step="0.01"
                                           min="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Capacity Volume (m³)')</label>
                                    <input type="number"
                                           name="capacity_volume"
                                           class="form-control"
                                           value="{{ old('capacity_volume', $vehicle->capacity_volume ?? '') }}"
                                           min="0">
                                </div>
                            </div>

                            @if(isset($vehicle))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Status') <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="available" {{ $vehicle->status == 'available' ? 'selected' : '' }}>@lang('Available')</option>
                                        <option value="assigned" {{ $vehicle->status == 'assigned' ? 'selected' : '' }}>@lang('Assigned')</option>
                                        <option value="in_transit" {{ $vehicle->status == 'in_transit' ? 'selected' : '' }}>@lang('In Transit')</option>
                                        <option value="maintenance" {{ $vehicle->status == 'maintenance' ? 'selected' : '' }}>@lang('Maintenance')</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            <!-- Notes -->
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>@lang('Notes')</label>
                                    <textarea name="notes"
                                              class="form-control"
                                              rows="4">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">
                            <i class="las la-save"></i> @lang('Submit')
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.vehicle.index') }}" />
@endpush