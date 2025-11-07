@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-12">
        {{-- View Assignment List Permission Check for the entire card/list --}}
        @permit('admin.delivery.assignment.index')
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Batch')</th>
                                <th>@lang('Vehicle')</th>
                                <th>@lang('Driver')</th>
                                <th>@lang('Orders')</th>
                                <th>@lang('Progress')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                            @php
                            $totalOrders = $assignment->batch->batchOrders->count();
                            $deliveredOrders = $assignment->batch->batchOrders->where('delivery_status', 'delivered')->count();
                            $progress = $totalOrders > 0 ? round(($deliveredOrders / $totalOrders) * 100) : 0;
                            @endphp
                            <tr>
                                <td>
                                    {{-- Linking to batch details is controlled by its own permission if implemented, but displaying is part of assignment index --}}
                                    <strong>{{ $assignment->batch->batch_number }}</strong><br>
                                    <small class="text-muted">{{ showDateTime($assignment->batch->delivery_date, 'd M, Y') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $assignment->vehicle->vehicle_number }}</strong><br>
                                    <small class="text-muted">{{ $assignment->vehicle->vehicle_type }}</small>
                                </td>
                                <td>
                                    {{ $assignment->vehicle->driver_name }}<br>
                                    <small class="text-muted">{{ $assignment->vehicle->driver_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge badge--info">{{ $totalOrders }} orders</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg--success" style="width: {{ $progress }}%">
                                            {{-- NOTE: I corrected a potential error here: the original code had `style="width: {{ $progress+30 }}%"`, which incorrectly boosted the width by 30%. I removed the `+30`. --}}
                                            {{ $progress }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $deliveredOrders }}/{{ $totalOrders }} delivered</small>
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
                                    {{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}
                                </td>
                                <td>
                                    {{-- Action button to view details --}}
                                    @permit('admin.delivery.assignment.show')
                                    <a href="{{ route('admin.delivery.assignment.show', $assignment->id) }}"
                                        class="btn btn-sm btn-outline--primary">
                                        <i class="las la-eye"></i> @lang('View')
                                    </a>
                                    @endpermit
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" style="width: 80px;">
                                    <p class="mt-3">@lang('No assignments found')</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($assignments->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($assignments) }}
            </div>
            @endif
        </div>
        @else
            {{-- Fallback if user doesn't have the index permission --}}
            <div class="card">
                <div class="card-body">
                    <p class="text-center">@lang('You do not have permission to view the list of assignments.')</p>
                </div>
            </div>
        @endpermit
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
{{-- Button to navigate to the Batches list --}}
@permit('admin.delivery.batch.index')
<a href="{{ route('admin.delivery.batch.index') }}" class="btn btn-sm btn--primary">
    <i class="las la-list"></i> @lang('View Batches')
</a>
@endpermit
@endpush