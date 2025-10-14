@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <div class="col-lg-4 mb-30">
        <div class="card">
            <div class="card-header bg--primary">
                <h5 class="card-title text-white">@lang('Batch Information')</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Batch Number')</span>
                        <strong>{{ $batch->batch_number }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Delivery Date')</span>
                        <strong>{{ showDateTime($batch->delivery_date, 'd M, Y') }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Total Orders')</span>
                        <span class="badge badge--primary">{{ $batch->total_orders }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Total Amount')</span>
                        <strong class="text--primary">{{ showAmount($batch->total_amount) }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Area')</span>
                        @if($batch->area)
                            <span class="badge badge--info">{{ $batch->area->name }}</span>
                        @else
                            <span class="text-muted">@lang('Mixed')</span>
                        @endif
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Status')</span>
                        @php
                            $statusClass = [
                                'pending' => 'badge--warning',
                                'assigned' => 'badge--info',
                                'in_transit' => 'badge--primary',
                                'delivered' => 'badge--success',
                                'cancelled' => 'badge--danger'
                            ];
                        @endphp
                        <span class="badge {{ $statusClass[$batch->status] ?? 'badge--secondary' }}">
                            {{ __(ucfirst($batch->status)) }}
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Created By')</span>
                        <strong>{{ $batch->creator->name ?? 'N/A' }}</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>@lang('Created At')</span>
                        <span>{{ showDateTime($batch->created_at, 'd M, Y h:i A') }}</span>
                    </li>
                </ul>

                @if($batch->notes)
                    <div class="mt-3">
                        <strong>@lang('Notes'):</strong>
                        <p class="mt-2">{{ $batch->notes }}</p>
                    </div>
                @endif

                @if($batch->status == 'pending' && !$batch->vehicleAssignment)
                    <div class="mt-3">
                        <a href="{{ route('admin.delivery.assignment.create', $batch->id) }}"
                           class="btn btn--success w-100">
                            <i class="las la-truck"></i> @lang('Assign Vehicle')
                        </a>
                    </div>
                @endif

                @if($batch->vehicleAssignment)
                    <div class="mt-3">
                        <a href="{{ route('admin.delivery.assignment.show', $batch->vehicleAssignment->id) }}"
                           class="btn btn--primary w-100">
                            <i class="las la-clipboard-list"></i> @lang('View Assignment')
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Orders in Batch')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light">
                        <thead>
                            <tr>
                                <th>@lang('Seq')</th>
                                <th>@lang('Invoice')</th>
                                <th>@lang('Customer')</th>
                                <th>@lang('Area')</th>
                                <th>@lang('Items')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->batchOrders->sortBy('sort_order') as $batchOrder)
                                <tr>
                                    <td>
                                        <span class="badge badge--dark">{{ $batchOrder->sort_order }}</span>
                                    </td>
                                    <td>
                                        <!-- <a href="{{ route('admin.order.edit', $batchOrder->sale->id) }}" target="_blank"> -->
                                            <strong>#{{ $batchOrder->sale->invoice_no }}</strong>
                                        <!-- </a> -->
                                    </td>
                                    <td>
                                        {{ $batchOrder->sale->customer->name }}<br>
                                        <small class="text-muted">{{ $batchOrder->sale->customer->mobile }}</small>
                                    </td>
                                    <td>
                                        @if($batchOrder->sale->customer->area)
                                            <span class="badge badge--info">{{ $batchOrder->sale->customer->area->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg--primary">{{ $batchOrder->sale->saleDetails->count() }} items</span>
                                    </td>
                                    <td>
                                        <strong>{{ showAmount($batchOrder->sale->total_price) }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $deliveryStatusClass = [
                                                'pending' => 'badge--warning',
                                                'delivered' => 'badge--success',
                                                'failed' => 'badge--danger',
                                                'returned' => 'badge--info'
                                            ];
                                        @endphp
                                        <span class="badge {{ $deliveryStatusClass[$batchOrder->delivery_status] ?? 'badge--secondary' }}">
                                            {{ __(ucfirst($batchOrder->delivery_status)) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr class="bg-light">
                                    <td colspan="7">
                                        <strong>@lang('Products'):</strong>
                                        @foreach($batchOrder->sale->saleDetails as $detail)
                                            <span class="badge badge--dark">
                                                {{ getProductTitle($detail->product->id) }} × {{ $detail->quantity }}
                                            </span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.delivery.batch.index') }}" />
@endpush