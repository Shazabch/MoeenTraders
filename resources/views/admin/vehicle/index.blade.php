@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Vehicle Number')</th>
                                <th>@lang('Type')</th>
                                <th>@lang('Driver')</th>
                                <th>@lang('Contact')</th>
                                <th>@lang('Capacity')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicles as $vehicle)
                                <tr>
                                    <td>
                                        <strong>{{ $vehicle->vehicle_number }}</strong>
                                    </td>
                                    <td>{{ ucfirst($vehicle->vehicle_type) }}</td>
                                    <td>
                                        <strong>{{ $vehicle->driver_name }}</strong>
                                        @if($vehicle->driver_license)
                                            <br><small class="text-muted">License: {{ $vehicle->driver_license }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $vehicle->driver_phone }}</td>
                                    <td>
                                        @if($vehicle->capacity_weight)
                                            <span class="badge badge--info">{{ $vehicle->capacity_weight }} kg</span>
                                        @endif
                                        @if($vehicle->capacity_volume)
                                            <span class="badge badge--info">{{ $vehicle->capacity_volume }} m³</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'available' => 'badge--success',
                                                'assigned' => 'badge--info',
                                                'in_transit' => 'badge--warning',
                                                'maintenance' => 'badge--danger'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClass[$vehicle->status] ?? 'badge--secondary' }}">
                                            {{ __(ucfirst($vehicle->status)) }}
                                        </span>
                                        <br>
                                        @if($vehicle->is_active)
                                            <span class="badge badge--success mt-1">@lang('Active')</span>
                                        @else
                                            <span class="badge badge--danger mt-1">@lang('Inactive')</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.vehicle.edit', $vehicle->id) }}"
                                           class="btn btn-sm btn-outline--primary">
                                            <i class="las la-pen"></i> @lang('Edit')
                                        </a>

                                        <a href="{{ route('admin.vehicle.assignments', $vehicle->id) }}"
                                           class="btn btn-sm btn-outline--info">
                                            <i class="las la-list"></i> @lang('Assignments')
                                        </a>

                                        <button type="button"
                                                class="btn btn-sm btn-outline--{{ $vehicle->is_active ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $vehicle->id }})">
                                            <i class="las la-{{ $vehicle->is_active ? 'ban' : 'check' }}"></i>
                                            {{ $vehicle->is_active ? __('Deactivate') : __('Activate') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" style="width: 80px;">
                                        <p class="mt-3">@lang('No vehicles found')</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($vehicles->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($vehicles) }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.vehicle.create') }}" class="btn btn-sm btn--primary">
        <i class="las la-plus"></i> @lang('Add Vehicle')
    </a>
@endpush

@push('script')
<script>
    function toggleStatus(id) {
        if (!confirm('@lang("Are you sure to change the status?")')) {
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ url("admin/vehicle") }}/' + id + '/toggle-status';

        const csrfField = document.createElement('input');
        csrfField.type = 'hidden';
        csrfField.name = '_token';
        csrfField.value = '{{ csrf_token() }}';

        form.appendChild(csrfField);
        document.body.appendChild(form);
        form.submit();
    }
</script>
@endpush