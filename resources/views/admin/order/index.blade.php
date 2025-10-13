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
                                    <th>@lang('Order No.')</th>
                                    <th>@lang('Date')</th>
                                    <th>@lang('Customer')</th>
                                    <th>@lang('Total Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Discount')</th>
                                    <th>@lang('Receivable')</th>
                                    <th>@lang('Paid')</th>
                                    <th>@lang('Due')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sales as $sale)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $sale->invoice_no }}</span>
                                        </td>

                                        <td>
                                            {{ showDateTime($sale->sale_date, 'd M, Y') }}
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ __($sale->customer?->name) }}</span>
                                            <br>
                                            <span class="small">{{ $sale->customer?->mobile }}</span>
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ gs('cur_sym') }}{{ showAmount($sale->total_price) }}</span>
                                        </td>

                                        <td>
                                            @php
                                                $statusClass = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'processing' => 'primary',
                                                    'shipped' => 'dark',
                                                    'delivered' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $class = $statusClass[$sale->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge--{{ $class }}">
                                                {{ ucfirst($sale->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <span>{{ gs('cur_sym') }}{{ showAmount($sale->discount_amount) }}</span>
                                        </td>

                                        <td>
                                            <span class="fw-bold">{{ gs('cur_sym') }}{{ showAmount($sale->receivable_amount) }}</span>
                                        </td>

                                        <td>
                                            <span class="text--success">{{ gs('cur_sym') }}{{ showAmount($sale->received_amount) }}</span>
                                        </td>

                                        <td>
                                            @if($sale->due_amount > 0)
                                                <span class="text--danger fw-bold">{{ gs('cur_sym') }}{{ showAmount($sale->due_amount) }}</span>
                                            @else
                                                <span class="text--success">{{ gs('cur_sym') }}0.00</span>
                                            @endif
                                        </td>

                                        <td>
                                            <div class="button-group">
                                                <a class="btn btn-sm btn-outline--primary"
                                                   href="{{ route('admin.order.edit', $sale->id) }}"
                                                   title="@lang('Edit')">
                                                    <i class="la la-pen"></i> @lang('Edit')
                                                </a>

                                                <a class="btn btn-sm btn-outline--info"
                                                   href="{{ route('admin.order.invoice', $sale->id) }}"
                                                   title="@lang('Download Invoice')">
                                                    <i class="la la-download"></i> @lang('Invoice')
                                                </a>

                                                <button class="btn btn-sm btn-outline--dark statusBtn"
                                                        data-id="{{ $sale->id }}"
                                                        data-status="{{ $sale->status }}"
                                                        title="@lang('Update Status')">
                                                    <i class="la la-sync"></i> @lang('Status')
                                                </button>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($sales->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($sales) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Status Update Modal --}}
    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Update Order Status')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form action="" method="POST" id="statusForm">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Order Status')</label>
                            <select class="form-control" name="status" required>
                                <option value="pending">@lang('Pending')</option>
                                <option value="confirmed">@lang('Confirmed')</option>
                                <option value="processing">@lang('Processing')</option>
                                <option value="shipped">@lang('Shipped')</option>
                                <option value="delivered">@lang('Delivered')</option>
                                <option value="cancelled">@lang('Cancelled')</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--primary">@lang('Update')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder="Search by order no., customer name/mobile" />

    @if($pdfButton)
        <a class="btn btn-sm btn-outline--info" href="{{ $routePDF }}" target="_blank">
            <i class="las la-file-pdf"></i> @lang('PDF')
        </a>
        <a class="btn btn-sm btn-outline--success" href="{{ $routeCSV }}">
            <i class="las la-file-csv"></i> @lang('CSV')
        </a>
    @endif

    <a class="btn btn-sm btn-outline--primary" href="{{ route('admin.order.create') }}">
        <i class="las la-plus"></i>@lang('Add New')
    </a>
@endpush

@push('script')
    <script>
        (function($) {
            'use strict';

            $('.statusBtn').on('click', function() {
                let modal = $('#statusModal');
                let id = $(this).data('id');
                let currentStatus = $(this).data('status');

                modal.find('[name=status]').val(currentStatus);

                let action = "{{ route('admin.order.update.status', ':id') }}";
                action = action.replace(':id', id);

                $('#statusForm').attr('action', action);
                modal.modal('show');
            });

        })(jQuery);
    </script>
@endpush