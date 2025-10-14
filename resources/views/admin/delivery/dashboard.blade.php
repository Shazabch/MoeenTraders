@extends('admin.layouts.app')
@section('panel')
<div class="row" id="dashboardApp">
    <!-- Statistics Cards -->
    <div class="col-xl-3 col-lg-6 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--primary b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-truck"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">@{{ stats.active_deliveries }}</span>
                </div>
                <div class="desciption">
                    <span>@lang('Active Deliveries')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--success b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-check-circle"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">@{{ stats.delivered_today }}</span>
                </div>
                <div class="desciption">
                    <span>@lang('Delivered Today')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--warning b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-clock"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">@{{ stats.pending_orders }}</span>
                </div>
                <div class="desciption">
                    <span>@lang('Pending Orders')</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-6 col-sm-6 mb-30">
        <div class="dashboard-w1 bg--info b-radius--10 box-shadow">
            <div class="icon">
                <i class="las la-car"></i>
            </div>
            <div class="details">
                <div class="numbers">
                    <span class="amount">@{{ stats.available_vehicles }}</span>
                </div>
                <div class="desciption">
                    <span>@lang('Available Vehicles')</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Deliveries Map/List -->
    <div class="col-lg-8 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Active Deliveries')</h5>
                <button class="btn btn-sm btn--primary" @click="refreshData">
                    <i class="las la-sync"></i> @lang('Refresh')
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Batch')</th>
                                <th>@lang('Vehicle')</th>
                                <th>@lang('Driver')</th>
                                <th>@lang('Orders')</th>
                                <th>@lang('Progress')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="delivery in activeDeliveries" :key="delivery.id">
                                <td>
                                    <strong>@{{ delivery.batch.batch_number }}</strong><br>
                                    <small class="text-muted">@{{ formatDate(delivery.batch.delivery_date) }}</small>
                                </td>
                                <td>@{{ delivery.vehicle.vehicle_number }}</td>
                                <td>
                                    @{{ delivery.vehicle.driver_name }}<br>
                                    <small class="text-muted">@{{ delivery.vehicle.driver_phone }}</small>
                                </td>
                                <td>
                                    <span class="badge badge--info">@{{ delivery.batch.total_orders }} orders</span>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div
                                            class="progress-bar bg--success"
                                            :style="{width: delivery.progress + '%'}">
                                            @{{ delivery.progress }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(delivery.status)">
                                        @{{ delivery.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td>
                                    <a :href="'/admin/delivery/assignment/' + delivery.id" class="btn btn-sm btn-outline--primary">
                                        <i class="las la-eye"></i> @lang('Track')
                                    </a>
                                </td>
                            </tr>
                            <tr v-if="activeDeliveries.length === 0">
                                <td colspan="7" class="text-center text-muted">
                                    @lang('No active deliveries')
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Batches -->
        <div class="card mt-30">
            <div class="card-header">
                <h5 class="card-title">@lang('Recent Batches')</h5>
                <a href="{{ route('admin.delivery.batch.create') }}" class="btn btn-sm btn--success">
                    <i class="las la-plus"></i> @lang('Create Batch')
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Batch No')</th>
                                <th>@lang('Area')</th>
                                <th>@lang('Orders')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="batch in recentBatches" :key="batch.id">
                                <td><strong>@{{ batch.batch_number }}</strong></td>
                                <td>
                                    <span v-if="batch.area">@{{ batch.area.name }}</span>
                                    <span v-else class="text-muted">Mixed</span>
                                </td>
                                <td>@{{ batch.total_orders }}</td>
                                <td>{{ gs('cur_sym') }}@{{ formatPrice(batch.total_amount) }}</td>
                                <td>@{{ formatDate(batch.delivery_date) }}</td>
                                <td>
                                    <span class="badge" :class="getStatusBadge(batch.status)">
                                        @{{ batch.status.toUpperCase() }}
                                    </span>
                                </td>
                                <td>
                                    <a :href="'/admin/delivery/batch/' + batch.id" class="btn btn-sm btn-outline--primary">
                                        <i class="las la-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4 mb-30">
        <!-- Ready Orders -->
        <div class="card mb-30">
            <div class="card-header bg--success">
                <h5 class="card-title text-white">@lang('Ready for Delivery')</h5>
            </div>
            <div class="card-body">
                <div class="ready-orders-list">
                    <div v-for="order in readyOrders" :key="order.id" class="order-mini-card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>#@{{ order.invoice_no }}</strong><br>
                                <small class="text-muted">@{{ order.customer.name }}</small>
                            </div>
                            <span class="badge badge--success">{{ gs('cur_sym') }}@{{ formatPrice(order.grand_total) }}</span>
                        </div>
                    </div>
                    <div v-if="readyOrders.length === 0" class="text-center py-3">
                        <p class="text-muted">@lang('No orders ready')</p>
                    </div>
                </div>
                <a href="{{ route('admin.delivery.batch.create') }}" class="btn btn--primary w-100 mt-3">
                    <i class="las la-plus"></i> @lang('Create Batch')
                </a>
            </div>
        </div>

        <!-- Vehicles Status -->
        <div class="card mb-30">
            <div class="card-header bg--info">
                <h5 class="card-title text-white">@lang('Vehicles Status')</h5>
            </div>
            <div class="card-body">
                <div class="vehicle-status-list">
                    <div v-for="vehicle in vehicles" :key="vehicle.id" class="vehicle-status-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <strong>@{{ vehicle.vehicle_number }}</strong><br>
                                <small class="text-muted">@{{ vehicle.driver_name }}</small>
                            </div>
                            <span class="badge" :class="getVehicleStatusBadge(vehicle.status)">
                                @{{ vehicle.status }}
                            </span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('admin.vehicle.index') }}" class="btn btn--primary w-100 mt-3">
                    <i class="las la-car"></i> @lang('Manage Vehicles')
                </a>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header bg--dark">
                <h5 class="card-title text-white">@lang('This Week Summary')</h5>
            </div>
            <div class="card-body">
                <div class="week-stats">
                    <div class="stat-row">
                        <span class="label">@lang('Total Deliveries')</span>
                        <span class="value text--primary">@{{ weekStats.total_deliveries }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="label">@lang('Successful')</span>
                        <span class="value text--success">@{{ weekStats.successful }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="label">@lang('Failed')</span>
                        <span class="value text--danger">@{{ weekStats.failed }}</span>
                    </div>
                    <div class="stat-row">
                        <span class="label">@lang('Success Rate')</span>
                        <span class="value text--info">@{{ weekStats.success_rate }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    .dashboard-w1 {
        padding: 30px;
        display: flex;
        align-items: center;
        color: white;
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

    .ready-orders-list {
        max-height: 300px;
        overflow-y: auto;
    }

    .order-mini-card {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
        border-left: 3px solid var(--success);
    }

    .vehicle-status-list {
        max-height: 250px;
        overflow-y: auto;
    }

    .vehicle-status-item {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .week-stats {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
    }

    .stat-row:last-child {
        border-bottom: none;
    }

    .stat-row .label {
        font-weight: 500;
    }

    .stat-row .value {
        font-weight: bold;
        font-size: 18px;
    }
</style>
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        data() {
            return {
                stats: {
                    active_deliveries: 0,
                    delivered_today: 0,
                    pending_orders: 0,
                    available_vehicles: 0
                },
                activeDeliveries: [],
                recentBatches: [],
                readyOrders: [],
                vehicles: [],
                weekStats: {
                    total_deliveries: 0,
                    successful: 0,
                    failed: 0,
                    success_rate: 0
                },
                refreshInterval: null
            }
        },
        methods: {
            async loadDashboardData() {
                try {
                    const response = await fetch('/admin/delivery/dashboard/data');
                    const data = await response.json();

                    if (data.success) {
                        this.stats = data.stats;
                        this.activeDeliveries = data.active_deliveries;
                        this.recentBatches = data.recent_batches;
                        this.readyOrders = data.ready_orders;
                        this.vehicles = data.vehicles;
                        this.weekStats = data.week_stats;
                    }
                } catch (error) {
                    console.error('Error loading dashboard data:', error);
                }
            },
            refreshData() {
                this.loadDashboardData();
            },
            formatPrice(value) {
                return parseFloat(value || 0).toFixed(2);
            },
            formatDate(date) {
                return new Date(date).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            },
            getStatusBadge(status) {
                const badges = {
                    'pending': 'badge--warning',
                    'assigned': 'badge--info',
                    'in_progress': 'badge--primary',
                    'in_transit': 'badge--primary',
                    'completed': 'badge--success',
                    'delivered': 'badge--success',
                    'cancelled': 'badge--danger'
                };
                return badges[status] || 'badge--secondary';
            },
            getVehicleStatusBadge(status) {
                const badges = {
                    'available': 'badge--success',
                    'assigned': 'badge--info',
                    'in_transit': 'badge--warning',
                    'maintenance': 'badge--danger'
                };
                return badges[status] || 'badge--secondary';
            }
        },
        mounted() {
            this.loadDashboardData();

            // Auto-refresh every 60 seconds
            this.refreshInterval = setInterval(() => {
                this.loadDashboardData();
            }, 60000);
        },
        beforeUnmount() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
            }
        }
    }).mount('#dashboardApp');
</script>
@endpush