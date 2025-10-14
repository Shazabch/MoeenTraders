@extends('admin.layouts.app')

@section('panel')

    @permit('admin.dashboard')
    <div class="row gy-4">
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_product'] }}" title="Total Products" style="6" link="{{ route('admin.product.index') }}" icon="las la-box" bg="primary" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_customer'] }}" title="Total Customers" style="6" link="{{ route('admin.customer.index') }}" icon="las la-users" bg="success" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_supplier'] }}" title="Total Suppliers" style="6" link="{{ route('admin.supplier.index') }}" icon="las la-user-friends" bg="purple" />
        </div>
        <div class="col-xxl-3 col-sm-6">
            <x-widget value="{{ $widget['total_category'] }}" title="Total Categories" style="6" link="{{ route('admin.product.category.index') }}" icon="lab la-buffer" bg="warning" />
        </div>
    </div>

    <div class="row mt-2 gy-4">
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Sales')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a class="widget-card-link" href="{{ route('admin.manage_sale') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-shopping-cart"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $widget['total_sale_count'] }}</h6>
                                    <p class="widget-card-title">@lang('Total Sales')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a class="widget-card-link" href="{{ route('admin.sale.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-sack-dollar"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($widget['total_sale']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Sales Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a class="widget-card-link" href="{{ route('admin.sale.return.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-rotate-left"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $widget['total_sale_return_count'] }}</h6>
                                    <p class="widget-card-title">@lang('Total Sales Return')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a class="widget-card-link" href="{{ route('admin.sale.return.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-hand-holding-dollar"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($widget['total_sale_return']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Sales Return Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-6">
            <div class="card box-shadow3 h-100">
                <div class="card-body">
                    <h5 class="card-title">@lang('Purchases')</h5>
                    <div class="widget-card-wrapper">
                        <div class="widget-card bg--success">
                            <a class="widget-card-link" href="{{ route('admin.manage_purchase') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="las la-shopping-bag"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $widget['total_purchase_count'] }}</h6>
                                    <p class="widget-card-title">@lang('Total Purchases')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--warning">
                            <a class="widget-card-link" href="{{ route('admin.purchase.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-money-bill-trend-up"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($widget['total_purchase']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Purchases Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--danger">
                            <a class="widget-card-link" href="{{ route('admin.purchase.return.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-rotate-right"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ $widget['total_purchase_return_count'] }}</h6>
                                    <p class="widget-card-title">@lang('Total Purchases Return')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>

                        <div class="widget-card bg--primary">
                            <a class="widget-card-link" href="{{ route('admin.purchase.return.index') }}"></a>
                            <div class="widget-card-left">
                                <div class="widget-card-icon">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                </div>
                                <div class="widget-card-content">
                                    <h6 class="widget-card-amount">{{ showAmount($widget['total_purchase_return']) }}</h6>
                                    <p class="widget-card-title">@lang('Total Purchases Return Amount')</p>
                                </div>
                            </div>
                            <span class="widget-card-arrow">
                                <i class="las la-angle-right"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-none-30 mt-30">
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="card-title mb-0">@lang('Purchases & Sales Report')</h5>
                        <div class="border p-1 cursor-pointer rounded date-select" id="psDatePicker">
                            <i class="la la-calendar"></i>&nbsp;
                            <span></span> <i class="la la-caret-down"></i>
                        </div>
                    </div>
                    <div id="psChartArea"> </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="card-title mb-0">@lang('Sales & Sales Return Report')</h5>
                        <div class="border p-1 cursor-pointer rounded date-select" id="saleSaleReturnDatePicker">
                            <i class="la la-calendar"></i>&nbsp;
                            <span></span> <i class="la la-caret-down"></i>
                        </div>
                    </div>

                    <div id="sSrChartArea"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <h5 class="card-title mb-0">@lang('Purchase & Purchase Return Report')</h5>
                        <div class="border p-1 cursor-pointer rounded date-select" id="purchasePurchaseReturnDatePicker">
                            <i class="la la-calendar"></i>&nbsp;
                            <span></span> <i class="la la-caret-down"></i>
                        </div>
                    </div>

                    <div id="pPrChartArea"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-12 mb-30">
            <h5 class="mb-3">@lang('Top Selling Products') </h5>
            <div class="card">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--dark style--two">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <!-- <th>@lang('SKU')</th> -->
                                <th>@lang('Quantity')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellingProducts as $product)
                                <tr>
                                    <td>
                                        {{ $loop->iteration }}. &nbsp;
                                        <a class="text--dark"
                                            href="{{ route('admin.product.edit', $product->id) }}">{{ strLimit(__(getProductTitle($product->id)), 20) }}</a>
                                    </td>
                                    <!-- <td>{{ $product->sku }} </td> -->
                                    <td>{{ $product->total_sale }} {{ $product->unit?->name }} </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table><!-- table end -->
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <h5 class="mb-3">@lang('Stock Level Alert') </h5>
            <div class="card">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--dark style--two">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <!-- <th>@lang('Warehouse')</th> -->
                                <th>@lang('Alert')</th>
                                <th>@lang('Stock')</th>
                                <th>@lang('Unit')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($alertProductsQty as $product)
                                <tr>
                                    <td class="fw-bold"> {{ getProductTitle($product->id) }} </td>
                                    <!-- <td> {{ $product->warehouse_name }} </td> -->
                                    <td>
                                        <span class="bg--warning px-2 rounded">
                                            {{ $product->alert_quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="bg--danger px-2 rounded">
                                            {{ $product->quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $product->unit_name }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table><!-- table end -->
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-30">
            <h5 class="mb-3">@lang('Latest Sales Return') </h5>
            <div class="card">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--dark style--two">
                        <thead>
                            <tr>
                                <th>@lang('Date')</th>
                                <th>@lang('Invoice No.') </th>
                                <th>@lang('Customer')</th>
                                <th>@lang('Amount')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($saleReturns as $return)
                                <tr>
                                    <td>
                                        {{ showDateTime($return->return_date, 'd M, Y') }}
                                    </td>

                                    <td>
                                        <a class="text--dark"
                                            href="{{ route('admin.sale.return.edit', $return->id) }}">{{ $return->sale->invoice_no }}</a>
                                    </td>

                                    <td>
                                        {{ $return->customer?->name }}
                                    </td>

                                    <td>
                                        {{ showAmount($return->payable_amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table><!-- table end -->
                </div>
            </div>
        </div>
    </div>
     @endpermit
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/vendor/chart.js.2.8.0.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/charts.js') }}"></script>
@endpush

@push('style-lib')
    <link type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}" rel="stylesheet">
@endpush

@push('script')
    <script>
        "use strict";

        const start = moment().subtract(14, 'days');
        const end = moment();

        const dateRangeOptions = {
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },
            maxDate: moment()
        }

        const changeDatePickerText = (element, startDate, endDate) => {
            $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
        }

        let dwChart = barChart(
            document.querySelector("#psChartArea"),
            @json(__(gs('cur_text'))),
            [{
                    name: 'Purchases',
                    data: []
                },
                {
                    name: 'Sales',
                    data: []
                }
            ],
            [],
        );

        let sSrChart = lineChart(
            document.querySelector("#sSrChartArea"),
            [{
                    name: "Sales Chart",
                    data: []
                },
                {
                    name: "Sales Return Chart",
                    data: []
                }
            ],
            []
        );
        let pPrChart = lineChart(
            document.querySelector("#pPrChartArea"),
            [{
                    name: "Purchases Chart",
                    data: []
                },
                {
                    name: "Purchases Return Chart",
                    data: []
                }
            ],
            []
        );


        const purchaseSaleChart = (startDate, endDate) => {

            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.purchase.sale'));

            $.get(url, data,
                function(data, status) {
                    if (status == 'success') {
                        dwChart.updateSeries(data.data);
                        dwChart.updateOptions({
                            xaxis: {
                                categories: data.created_on,
                            }
                        });
                    }
                }
            );
        }

        const saleSaleReturnChart = (startDate, endDate) => {

            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.sales.return'));


            $.get(url, data,
                function(data, status) {
                    if (status == 'success') {


                        sSrChart.updateSeries(data.data);
                        sSrChart.updateOptions({
                            xaxis: {
                                categories: data.created_on,
                            }
                        });
                    }
                }
            );
        }

        const purchasePurchaseReturnChart = (startDate, endDate) => {

            const data = {
                start_date: startDate.format('YYYY-MM-DD'),
                end_date: endDate.format('YYYY-MM-DD')
            }

            const url = @json(route('admin.chart.purchases.return'));


            $.get(url, data,
                function(data, status) {
                    if (status == 'success') {


                        pPrChart.updateSeries(data.data);
                        pPrChart.updateOptions({
                            xaxis: {
                                categories: data.created_on,
                            }
                        });
                    }
                }
            );
        }



        $('#psDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#psDatePicker span', start, end));
        $('#saleSaleReturnDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#saleSaleReturnDatePicker span', start,
            end));
        $('#purchasePurchaseReturnDatePicker').daterangepicker(dateRangeOptions, (start, end) => changeDatePickerText('#saleSaleReturnDatePicker span',
            start, end));

        changeDatePickerText('#psDatePicker span', start, end);
        changeDatePickerText('#saleSaleReturnDatePicker span', start, end);
        changeDatePickerText('#purchasePurchaseReturnDatePicker span', start, end);

        purchaseSaleChart(start, end);
        saleSaleReturnChart(start, end);
        purchasePurchaseReturnChart(start, end);

        $('#psDatePicker').on('apply.daterangepicker', (event, picker) => purchaseSaleChart(picker.startDate, picker.endDate));
        $('#saleSaleReturnDatePicker').on('apply.daterangepicker', (event, picker) => saleSaleReturnChart(picker.startDate, picker.endDate));
        $('#purchasePurchaseReturnDatePicker').on('apply.daterangepicker', (event, picker) => purchasePurchaseReturnChart(picker.startDate, picker
            .endDate));
    </script>
@endpush
@push('style')
    <style>
        .apexcharts-menu {
            min-width: 120px !important;
        }
    </style>
@endpush
