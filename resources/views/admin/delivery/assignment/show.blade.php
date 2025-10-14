@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Assignment Header -->
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header bg--primary">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">
                        <i class="las la-truck"></i> @lang('Assignment') #{{ $assignment->id }}
                    </h5>
                    <span class="badge badge--{{ $assignment->status == 'completed' ? 'success' : ($assignment->status == 'in_progress' ? 'warning' : 'info') }}">
                        {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box">
                            <label>@lang('Vehicle')</label>
                            <h6>{{ $assignment->vehicle->vehicle_number }}</h6>
                            <small class="text-muted">{{ $assignment->vehicle->vehicle_type }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <label>@lang('Driver')</label>
                            <h6>{{ $assignment->vehicle->driver_name }}</h6>
                            <small class="text-muted">{{ $assignment->vehicle->driver_phone }}</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <label>@lang('Batch')</label>
                            <h6>{{ $assignment->batch->batch_number }}</h6>
                            <small class="text-muted">{{ $assignment->batch->total_orders }} orders</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box">
                            <label>@lang('Assigned By')</label>
                            <h6>{{ $assignment->assignedBy->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}</small>
                        </div>
                    </div>
                     <div class="col-md-3">
                        <div class="info-box">
                            <label>@lang('Assigned To')</label>
                            <h6>{{ $assignment->assignedTo->name ?? 'N/A' }}</h6>
                            <small class="text-muted">{{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Containers & Items -->
        <div class="card mb-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-boxes"></i> @lang('Loaded Containers')
                </h5>
            </div>
            <div class="card-body">
                @forelse($assignment->containers as $container)
                <div class="container-detail mb-4">
                    <div class="container-header-info">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">
                                <i class="las la-box"></i> {{ $container->container_name }}
                            </h6>
                            <span class="badge badge--primary">{{ $container->items->count() }} items</span>
                        </div>
                        @if($container->notes)
                        <small class="text-muted"><i class="las la-sticky-note"></i> {{ $container->notes }}</small>
                        @endif
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Order')</th>
                                    <th>@lang('Product')</th>
                                    <th>@lang('Quantity')</th>
                                    <th>@lang('Status')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($container->items as $item)
                                <tr>
                                    <td>
                                        <span class="badge badge--{{ $item->item_type == 'order' ? 'info' : 'warning' }}">
                                            {{ ucfirst($item->item_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->sale)
                                            <a href="{{ route('admin.order.edit', $item->sale_id) }}" target="_blank">
                                                #{{ $item->sale->invoice_no }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <!-- <strong>{{ $item->product->name ?? 'N/A' }}</strong> -->
                                        @if($item->product)
                                        <br><small class="text-muted">{{ getProductTitle($item->product->id) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $item->quantity }}</strong>
                                        @if($item->delivered_quantity ?? false)
                                        <br><small class="text-success">Delivered: {{ $item->delivered_quantity }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if(($item->delivered_quantity ?? 0) == $item->quantity)
                                            <span class="badge bg--success">Delivered</span>
                                        @elseif(($item->delivered_quantity ?? 0) > 0)
                                            <span class="badge bg--warning">Partial</span>
                                        @else
                                            <span class="badge bg--secondary">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="las la-box-open la-3x text-muted"></i>
                    <p class="mt-3 text-muted">@lang('No containers loaded')</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Orders in Batch -->
        <div class="card mb-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-shopping-cart"></i> @lang('Orders in Batch')
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Invoice')</th>
                                <th>@lang('Customer')</th>
                                <th>@lang('Area')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignment->batch->batchOrders as $batchOrder)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.order.edit', $batchOrder->sale_id) }}" target="_blank">
                                        #{{ $batchOrder->sale->invoice_no }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $batchOrder->sale->customer->name }}</strong>
                                    <br><small class="text-muted">{{ $batchOrder->sale->customer->phone }}</small>
                                </td>
                                <td>
                                    @if($batchOrder->sale->customer->area)
                                        <i class="las la-map-marker"></i> {{ $batchOrder->sale->customer->area->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong> {{ showAmount($batchOrder->sale->total_price) }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge--{{ $batchOrder->delivery_status == 'delivered' ? 'success' : 'warning' }}">
                                        {{ ucfirst($batchOrder->delivery_status ?? 'pending') }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Delivery Timeline -->
        <div class="card mb-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-clock"></i> @lang('Delivery Timeline')
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item {{ $assignment->assigned_at ? 'completed' : '' }}">
                        <div class="timeline-icon bg--primary">
                            <i class="las la-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>@lang('Assigned')</h6>
                            @if($assignment->assigned_at)
                                <small>{{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item {{ $assignment->started_at ? 'completed' : '' }}">
                        <div class="timeline-icon {{ $assignment->started_at ? 'bg--warning' : 'bg--light' }}">
                            <i class="las la-truck"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>@lang('Started')</h6>
                            @if($assignment->started_at)
                                <small>{{ showDateTime($assignment->started_at, 'd M, Y h:i A') }}</small>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item {{ $assignment->completed_at ? 'completed' : '' }}">
                        <div class="timeline-icon {{ $assignment->completed_at ? 'bg--success' : 'bg--light' }}">
                            <i class="las la-flag-checkered"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>@lang('Completed')</h6>
                            @if($assignment->completed_at)
                                <small>{{ showDateTime($assignment->completed_at, 'd M, Y h:i A') }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Odometer Info -->
        <div class="card mb-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-tachometer-alt"></i> @lang('Odometer Reading')
                </h5>
            </div>
            <div class="card-body">
                <div class="odometer-info">
                    <div class="info-row">
                        <label>@lang('Starting KM')</label>
                        <h5>{{ $assignment->starting_km ?? 'N/A' }} km</h5>
                    </div>
                    @if($assignment->ending_km)
                    <div class="info-row">
                        <label>@lang('Ending KM')</label>
                        <h5>{{ $assignment->ending_km }} km</h5>
                    </div>
                    <div class="info-row">
                        <label>@lang('Distance Traveled')</label>
                        <h5 class="text--primary">{{ $assignment->ending_km - $assignment->starting_km }} km</h5>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="card mb-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-list-alt"></i> @lang('Summary')
                </h5>
            </div>
            <div class="card-body">
                <div class="summary-info">
                    <div class="summary-item">
                        <span>@lang('Total Containers')</span>
                        <strong>{{ $assignment->containers->count() }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>@lang('Total Items')</span>
                        <strong>{{ $assignment->containers->sum(function($c) { return $c->items->count(); }) }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>@lang('Total Quantity')</span>
                        <strong>{{ $assignment->containers->sum(function($c) { return $c->items->sum('quantity'); }) }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>@lang('Total Orders')</span>
                        <strong>{{ $assignment->batch->total_orders }}</strong>
                    </div>
                    <div class="summary-item">
                        <span>@lang('Total Amount')</span>
                        <strong class="text--primary">{{ showAmount($assignment->batch->total_amount) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-cogs"></i> @lang('Actions')
                </h5>
            </div>
            <div class="card-body">
                @if($assignment->status == 'assigned')
                    <button class="btn btn--success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#startModal">
                        <i class="las la-play"></i> @lang('Start Delivery')
                    </button>
                    <!-- <a href="{{ route('admin.delivery.assignment.edit', $assignment->id) }}" class="btn btn--primary w-100 mb-2">
                        <i class="las la-edit"></i> @lang('Edit Assignment')
                    </a> -->
                    <button class="btn btn--danger w-100" onclick="confirmDelete()">
                        <i class="las la-trash"></i> @lang('Delete Assignment')
                    </button>
                @elseif($assignment->status == 'in_progress')
                    <button class="btn btn--success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="las la-check-circle"></i> @lang('Complete Delivery')
                    </button>
                    <a href="{{ route('admin.delivery.assignment.index') }}" class="btn btn--info w-100">
                        <i class="las la-list"></i> @lang('View All Assignments')
                    </a>
                @else
                    <a href="{{ route('admin.delivery.assignment.index') }}" class="btn btn--dark w-100">
                        <i class="las la-list"></i> @lang('View All Assignments')
                    </a>
                @endif
            </div>
        </div>

        @if($assignment->notes)
        <div class="card mt-30">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="las la-sticky-note"></i> @lang('Notes')
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $assignment->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Start Delivery Modal -->
<div class="modal fade" id="startModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.delivery.assignment.start', $assignment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Start Delivery')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>@lang('Current Odometer Reading (KM)') <span class="text--danger">*</span></label>
                        <input type="number" name="starting_km" class="form-control" step="0.01" min="0" required value="{{ $assignment->starting_km }}">
                        <small class="text-muted">@lang('Enter the current odometer reading before starting delivery')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success">
                        <i class="las la-play"></i> @lang('Start Delivery')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Delivery Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.delivery.assignment.complete', $assignment->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Complete Delivery')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="las la-info-circle"></i> @lang('Starting Odometer'): <strong>{{ $assignment->starting_km }} km</strong>
                    </div>
                    <div class="form-group">
                        <label>@lang('Ending Odometer Reading (KM)') <span class="text--danger">*</span></label>
                        <input type="number" name="ending_km" class="form-control" step="0.01" min="{{ $assignment->starting_km + 0.01 }}" required>
                        <small class="text-muted">@lang('Enter the final odometer reading after completing delivery')</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--secondary" data-bs-dismiss="modal">@lang('Cancel')</button>
                    <button type="submit" class="btn btn--success">
                        <i class="las la-check-circle"></i> @lang('Complete Delivery')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Form -->
<form id="deleteForm" action="{{ route('admin.delivery.assignment.destroy', $assignment->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('breadcrumb-plugins')
<x-back route="{{ route('admin.delivery.batch.show', $assignment->batch_id) }}" />
@endpush

@push('style')
<style>
    .info-box {
        padding: 15px 0;
        border-bottom: 1px solid #e5e5e5;
    }

    .info-box:last-child {
        border-bottom: none;
    }

    .info-box label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }

    .info-box h6 {
        margin: 0;
        font-weight: 600;
    }

    .container-detail {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border-left: 4px solid var(--primary);
    }

    .container-header-info {
        padding-bottom: 10px;
        border-bottom: 2px solid #dee2e6;
    }

    .timeline {
        position: relative;
        padding: 20px 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 30px;
        bottom: 30px;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        padding-left: 50px;
        margin-bottom: 30px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-icon {
        position: absolute;
        left: 0;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 16px;
    }

    .timeline-item.completed .timeline-content h6 {
        color: #28a745;
    }

    .odometer-info .info-row {
        padding: 15px 0;
        border-bottom: 1px solid #e5e5e5;
    }

    .odometer-info .info-row:last-child {
        border-bottom: none;
    }

    .odometer-info label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }

    .summary-info .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #e5e5e5;
    }

    .summary-info .summary-item:last-child {
        border-bottom: none;
    }

    .summary-info .summary-item span {
        color: #6c757d;
        font-size: 14px;
    }

    .summary-info .summary-item strong {
        font-size: 16px;
    }
</style>
@endpush

@push('script')
<script>
    function confirmDelete() {
        if (confirm('Are you sure you want to delete this assignment? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>
@endpush