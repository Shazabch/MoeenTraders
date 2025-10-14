@extends('admin.layouts.app')
@section('panel')
<div class="row">
    <!-- Filter Section -->
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Generate Report')</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.delivery.reports') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>@lang('Date Range')</label>
                                <select name="date_range" class="form-control" id="dateRange">
                                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>@lang('Today')</option>
                                    <option value="week" {{ request('date_range') == 'week' ? 'selected' : '' }}>@lang('This Week')</option>
                                    <option value="month" {{ request('date_range') == 'month' ? 'selected' : '' }}>@lang('This Month')</option>
                                    <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>@lang('Custom Range')</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3" id="customDateFields" style="display: none;">
                            <div class="form-group">
                                <label>@lang('Start Date')</label>
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-3" id="customDateFieldsEnd" style="display: none;">
                            <div class="form-group">
                                <label>@lang('End Date')</label>
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate ?? '') }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn--primary w-100">
                                    <i class="las la-search"></i> @lang('Generate Report')
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delivery Performance -->
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Delivery Performance')</h5>
                <small class="text-muted">
                    @if(isset($startDate) && isset($endDate))
                        {{ showDateTime($startDate, 'd M, Y') }} - {{ showDateTime($endDate, 'd M, Y') }}
                    @endif
                </small>
            </div>
            <div class="card-body">
                @if(isset($deliveries))
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="dashboard-w1 bg--primary b-radius--10 box-shadow mb-3">
                                <div class="icon">
                                    <i class="las la-box"></i>
                                </div>
                                <div class="details">
                                    <div class="numbers">
                                        <span class="amount">{{ $deliveries->total ?? 0 }}</span>
                                    </div>
                                    <div class="desciption">
                                        <span>@lang('Total Deliveries')</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="dashboard-w1 bg--success b-radius--10 box-shadow mb-3">
                                <div class="icon">
                                    <i class="las la-check-circle"></i>
                                </div>
                                <div class="details">
                                    <div class="numbers">
                                        <span class="amount">{{ $deliveries->delivered ?? 0 }}</span>
                                    </div>
                                    <div class="desciption">
                                        <span>@lang('Delivered')</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="dashboard-w1 bg--danger b-radius--10 box-shadow mb-3">
                                <div class="icon">
                                    <i class="las la-times-circle"></i>
                                </div>
                                <div class="details">
                                    <div class="numbers">
                                        <span class="amount">{{ $deliveries->failed ?? 0 }}</span>
                                    </div>
                                    <div class="desciption">
                                        <span>@lang('Failed')</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="dashboard-w1 bg--warning b-radius--10 box-shadow mb-3">
                                <div class="icon">
                                    <i class="las la-clock"></i>
                                </div>
                                <div class="details">
                                    <div class="numbers">
                                        <span class="amount">{{ $deliveries->pending ?? 0 }}</span>
                                    </div>
                                    <div class="desciption">
                                        <span>@lang('Pending')</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $total = ($deliveries->total ?? 0);
                        $delivered = ($deliveries->delivered ?? 0);
                        $failed = ($deliveries->failed ?? 0);
                        $pending = ($deliveries->pending ?? 0);

                        // Prevent division by zero
                        if ($total > 0) {
                            $deliveredPercent = round(($delivered / $total) * 100);
                            $failedPercent = round(($failed / $total) * 100);
                            $pendingPercent = round(($pending / $total) * 100);
                        } else {
                            $deliveredPercent = 0;
                            $failedPercent = 0;
                            $pendingPercent = 0;
                        }
                    @endphp

                    @if($total > 0)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="progress" style="height: 30px;">
                                <div class="progress-bar bg--success" style="width: {{ $deliveredPercent }}%">
                                    {{ $deliveredPercent }}% @lang('Delivered')
                                </div>
                                <div class="progress-bar bg--danger" style="width: {{ $failedPercent }}%">
                                    {{ $failedPercent }}% @lang('Failed')
                                </div>
                                <div class="progress-bar bg--warning" style="width: {{ $pendingPercent }}%">
                                    {{ $pendingPercent }}% @lang('Pending')
                                </div>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-info text-center mt-3">
                        <i class="las la-info-circle"></i> @lang('No deliveries found for selected date range')
                    </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="las la-chart-bar la-3x text-muted"></i>
                        <p class="mt-3 text-muted">@lang('Select date range to generate report')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Vehicle Performance -->
    @if(isset($vehiclePerformance) && $vehiclePerformance->count() > 0)
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Vehicle Performance')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light">
                        <thead>
                            <tr>
                                <th>@lang('Vehicle')</th>
                                <th>@lang('Driver')</th>
                                <th>@lang('Total Orders')</th>
                                <th>@lang('Delivered')</th>
                                <th>@lang('Success Rate')</th>
                                <th>@lang('Distance (km)')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vehiclePerformance as $performance)
                                <tr>
                                    <td><strong>{{ $performance['vehicle'] }}</strong></td>
                                    <td>{{ $performance['driver'] }}</td>
                                    <td><span class="badge badge--primary">{{ $performance['total_orders'] }}</span></td>
                                    <td><span class="badge badge--success">{{ $performance['delivered'] }}</span></td>
                                    <td>
                                        @php
                                            $successRate = $performance['success_rate'] ?? 0;
                                        @endphp
                                        <div class="progress" style="height: 25px; min-width: 100px;">
                                            <div class="progress-bar
                                                @if($successRate >= 80) bg--success
                                                @elseif($successRate >= 60) bg--warning
                                                @else bg--danger
                                                @endif"
                                                style="width: {{ $successRate }}%">
                                                {{ $successRate }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong>{{ number_format($performance['distance'] ?? 0, 2) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Area Performance -->
    @if(isset($areaPerformance) && $areaPerformance->count() > 0)
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Area Performance')</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table--light">
                        <thead>
                            <tr>
                                <th>@lang('Area')</th>
                                <th>@lang('Total Orders')</th>
                                <th>@lang('Delivered')</th>
                                <th>@lang('Success Rate')</th>
                                <th>@lang('Performance')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($areaPerformance as $performance)
                                @php
                                    $successRate = $performance['success_rate'] ?? 0;
                                @endphp
                                <tr>
                                    <td><strong>{{ $performance['area'] }}</strong></td>
                                    <td><span class="badge badge--primary">{{ $performance['total_orders'] }}</span></td>
                                    <td><span class="badge badge--success">{{ $performance['delivered'] }}</span></td>
                                    <td><strong>{{ $successRate }}%</strong></td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar
                                                @if($successRate >= 80) bg--success
                                                @elseif($successRate >= 60) bg--warning
                                                @else bg--danger
                                                @endif"
                                                style="width: {{ $successRate }}%">
                                                {{ $successRate }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Export Options -->
    @if(isset($deliveries) && ($deliveries->total ?? 0) > 0)
    <div class="col-lg-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Export Report')</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <button onclick="alert('Export functionality coming soon!')" class="btn btn--success w-100">
                            <i class="las la-file-csv"></i> @lang('Export as CSV')
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button onclick="alert('Export functionality coming soon!')" class="btn btn--danger w-100">
                            <i class="las la-file-pdf"></i> @lang('Export as PDF')
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button onclick="alert('Export functionality coming soon!')" class="btn btn--info w-100">
                            <i class="las la-file-excel"></i> @lang('Export as Excel')
                        </button>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="las la-info-circle"></i> @lang('Export features can be implemented using packages like maatwebsite/excel or dompdf')
                </small>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('style')
<style>
    .dashboard-w1 {
        padding: 30px;
        display: flex;
        align-items: center;
        color: white;
        min-height: 150px;
    }

    .dashboard-w1 .icon {
        font-size: 50px;
        margin-right: 20px;
        opacity: 0.8;
    }

    .dashboard-w1 .amount {
        font-size: 36px;
        font-weight: bold;
    }

    .dashboard-w1 .desciption {
        font-size: 14px;
        opacity: 0.9;
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        $('#dateRange').on('change', function() {
            if ($(this).val() === 'custom') {
                $('#customDateFields').show();
                $('#customDateFieldsEnd').show();
            } else {
                $('#customDateFields').hide();
                $('#customDateFieldsEnd').hide();
            }
        });

        // Trigger on page load
        if ($('#dateRange').val() === 'custom') {
            $('#customDateFields').show();
            $('#customDateFieldsEnd').show();
        }

    })(jQuery);
</script>
@endpush