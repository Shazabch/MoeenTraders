@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 mb-30">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>@lang('Vehicle Information')</h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Vehicle Number')</span>
                                <strong>{{ $vehicle->vehicle_number }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Type')</span>
                                <strong>{{ ucfirst($vehicle->vehicle_type) }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Driver')</span>
                                <strong>{{ $vehicle->driver_name }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Phone')</span>
                                <strong>{{ $vehicle->driver_phone }}</strong>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>@lang('Statistics')</h5>
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Total Assignments')</span>
                                <strong class="text--primary">{{ $vehicle->assignments->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Completed')</span>
                                <strong class="text--success">{{ $vehicle->assignments->where('status', 'completed')->count() }}</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>@lang('Current Status')</span>
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
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="card b-radius--10">
            <div class="card-header">
                <h5 class="card-title">@lang('Assignment History')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Batch')</th>
                                <th>@lang('Assigned Date')</th>
                                <th>@lang('Started')</th>
                                <th>@lang('Completed')</th>
                                <th>@lang('Distance')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vehicle->assignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->batch->batch_number }}</strong><br>
                                        <small class="text-muted">{{ $assignment->batch->total_orders }} orders</small>
                                    </td>
                                    <td>{{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}</td>
                                    <td>
                                        @if($assignment->started_at)
                                            {{ showDateTime($assignment->started_at, 'd M, Y h:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->completed_at)
                                            {{ showDateTime($assignment->completed_at, 'd M, Y h:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($assignment->ending_km && $assignment->starting_km)
                                            <strong>{{ number_format($assignment->ending_km - $assignment->starting_km, 2) }} km</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'assigned' => 'badge--info',
                                                'in_progress' => 'badge--warning',
                                                'completed' => 'badge--success',
                                                'cancelled' => 'badge--danger'
                                            ];
                                        @endphp
                                        <span class="badge {{ $statusClass[$assignment->status] ?? 'badge--secondary' }}">
                                            {{ __(str_replace('_', ' ', ucfirst($assignment->status))) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.delivery.assignment.show', $assignment->id) }}"
                                           class="btn btn-sm btn-outline--primary">
                                            <i class="las la-eye"></i> @lang('View')
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" style="width: 80px;">
                                        <p class="mt-3">@lang('No assignments found')</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.vehicle.index') }}" />
@endpush