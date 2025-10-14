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
                                <th>@lang('Batch No')</th>
                                <th>@lang('Area')</th>
                                <th>@lang('Delivery Date')</th>
                                <th>@lang('Orders')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Created By')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr>
                                    <td>
                                        <strong>{{ $batch->batch_number }}</strong>
                                    </td>
                                    <td>
                                        @if($batch->area)
                                            <span class="badge badge--info">{{ $batch->area->name }}</span>
                                        @else
                                            <span class="text-muted">@lang('Mixed')</span>
                                        @endif
                                    </td>
                                    <td>{{ showDateTime($batch->delivery_date, 'd M, Y') }}</td>
                                    <td>
                                        <span class="badge badge--primary">{{ $batch->total_orders }} orders</span>
                                    </td>
                                    <td>
                                        <strong>{{ gs('cur_sym') }}{{ showAmount($batch->total_amount) }}</strong>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        {{ $batch->creator->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.delivery.batch.show', $batch->id) }}"
                                           class="btn btn-sm btn-outline--primary">
                                            <i class="las la-eye"></i> @lang('View')
                                        </a>

                                        @if($batch->status == 'pending' && !$batch->vehicleAssignment)
                                            <a href="{{ route('admin.delivery.assignment.create', $batch->id) }}"
                                               class="btn btn-sm btn-outline--success">
                                                <i class="las la-truck"></i> @lang('Assign Vehicle')
                                            </a>
                                        @endif

                                        @if($batch->vehicleAssignment)
                                            <a href="{{ route('admin.delivery.assignment.show', $batch->vehicleAssignment->id) }}"
                                               class="btn btn-sm btn-outline--info">
                                                <i class="las la-clipboard-list"></i> @lang('Track')
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" style="width: 80px;">
                                        <p class="mt-3">@lang('No batches found')</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($batches->hasPages())
                <div class="card-footer py-4">
                    {{ paginateLinks($batches) }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.delivery.batch.create') }}" class="btn btn-sm btn--primary">
        <i class="las la-plus"></i> @lang('Create Batch')
    </a>
@endpush