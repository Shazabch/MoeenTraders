@extends('admin.layouts.app')
@section('panel')
<div class="row" id="batchApp">
    <!-- Left Side: Available Orders -->
    <div class="col-lg-5 mb-30">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title">@lang('Available Orders')</h5>
            </div>
            <div class="card-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Filter by Area')</label>
                            <select class="form-control" v-model="selectedArea" @change="filterOrders">
                                <option value="">@lang('All Areas')</option>
                                <option v-for="area in areas" :key="area.id" :value="area.id">
                                    @{{ area.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>@lang('Search')</label>
                            <input
                                type="text"
                                class="form-control"
                                v-model="searchQuery"
                                placeholder="@lang('Search by invoice or customer...')">
                        </div>
                    </div>
                </div>

                <!-- Orders List -->
                <div class="orders-list">
                    <div
                        v-for="order in filteredOrders"
                        :key="order.id"
                        class="order-card"
                        :class="{ 'selected': isOrderSelected(order.id) }"
                        @click="toggleOrder(order)">

                        <div class="order-header">
                            <div>
                                <h6 class="mb-0">#@{{ order.invoice_no }}</h6>
                                <small class="text-muted">@{{ order.customer.name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge badge--success">{{ gs('cur_sym') }}@{{ formatPrice(order.total_price) }}</span>
                                <div v-if="order.customer.area">
                                    <small class="text-muted"><i class="las la-map-marker"></i> @{{ order.customer.area.name }}</small>
                                </div>
                            </div>
                        </div>

                        <div class="order-products mt-2">
                            <small class="text-muted">
                                <i class="las la-box"></i> @{{ order.sale_details.length }} items
                            </small>
                        </div>

                        <div class="order-checkbox">
                            <input
                                type="checkbox"
                                :checked="isOrderSelected(order.id)"
                                @click.stop="toggleOrder(order)">
                        </div>
                    </div>

                    <div v-if="filteredOrders.length === 0" class="text-center py-5">
                        <img src="{{ getImage('assets/images/empty_list.png') }}" alt="empty" style="width: 60px;">
                        <p class="mt-3 text-muted">@lang('No orders found')</p>
                    </div>
                </div>

                <div class="mt-3" v-if="availableOrders.length > 0">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline--primary w-100"
                        @click="selectAllFiltered"
                        v-if="filteredOrders.length > selectedOrders.length">
                        <i class="las la-check-double"></i> @lang('Select All Filtered')
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Batch Details -->
    <div class="col-lg-7 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Batch Details')</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitBatch">
                    @csrf
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Delivery Date') <span class="text--danger">*</span></label>
                                <input
                                    type="date"
                                    class="form-control"
                                    v-model="batchData.delivery_date"
                                    :min="today"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Delivery Area')</label>
                                <select class="form-control" v-model="batchData.area_id">
                                    <option value="">@lang('Mixed Areas')</option>
                                    <option v-for="area in areas" :key="area.id" :value="area.id">
                                        @{{ area.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Orders -->
                    <div class="batch-orders-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">@lang('Selected Orders') (@{{ selectedOrders.length }})</h6>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline--danger"
                                @click="clearSelection"
                                v-if="selectedOrders.length > 0">
                                <i class="las la-times"></i> @lang('Clear All')
                            </button>
                        </div>

                        <div class="selected-orders-container">
                            <draggable
                                v-model="selectedOrders"
                                @start="drag=true"
                                @end="drag=false"
                                item-key="id"
                                handle=".drag-handle"
                                class="draggable-list">
                                <template #item="{element, index}">
                                    <div class="batch-order-item">
                                        <div class="drag-handle">
                                            <i class="las la-grip-vertical"></i>
                                            <span class="order-number">#@{{ index + 1 }}</span>
                                        </div>

                                        <div class="order-info">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <strong>@{{ element.invoice_no }}</strong>
                                                    <br>
                                                    <small class="text-muted">@{{ element.customer.name }}</small>
                                                </div>
                                                <div class="text-end">
                                                    <strong class="text--primary">{{ gs('cur_sym') }}@{{ formatPrice(element.total_price) }}</strong>
                                                    <br>
                                                    <small class="text-muted" v-if="element.customer.area">
                                                        <i class="las la-map-marker"></i> @{{ element.customer.area.name }}
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <div class="product-summary">
                                                    <span v-for="(detail, idx) in element.sale_details.slice(0, 3)" :key="idx">
                                                        <small class="badge badge--dark">
                                                            @{{ detail.product.name }} × @{{ detail.quantity }}
                                                        </small>
                                                    </span>
                                                    <small class="text-muted" v-if="element.sale_details.length > 3">
                                                        +@{{ element.sale_details.length - 3 }} more
                                                    </small>
                                                </div>
                                            </div>
                                        </div>

                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline--danger"
                                            @click="removeOrder(element.id)">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </div>
                                </template>
                            </draggable>

                            <div v-if="selectedOrders.length === 0" class="text-center py-5">
                                <i class="las la-truck la-3x text-muted"></i>
                                <p class="mt-2 text-muted">@lang('No orders selected. Click on orders from the left to add them.')</p>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Summary -->
                    <div class="batch-summary mt-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="summary-item">
                                    <label>@lang('Total Orders')</label>
                                    <h4>@{{ selectedOrders.length }}</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="summary-item">
                                    <label>@lang('Total Amount')</label>
                                    <h4 class="text--primary">{{ gs('cur_sym') }}@{{ formatPrice(totalAmount) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group mt-3">
                        <label>@lang('Notes')</label>
                        <textarea
                            class="form-control"
                            v-model="batchData.notes"
                            rows="3"
                            placeholder="@lang('Add any special instructions or notes...')"></textarea>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn btn--primary w-100 h-45 mt-3"
                        :disabled="!canSubmit || isSubmitting">
                        <span v-if="isSubmitting">
                            <i class="las la-spinner la-spin"></i> @lang('Creating Batch...')
                        </span>
                        <span v-else>
                            <i class="las la-check"></i> @lang('Create Delivery Batch')
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<x-back route="{{ route('admin.delivery.batch.index') }}" />
@endpush

@push('style')
<style>
    .orders-list {
        max-height: calc(100vh - 400px);
        overflow-y: auto;
    }

    .order-card {
        position: relative;
        border: 2px solid #e5e5e5;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: white;
    }

    .order-card:hover {
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .order-card.selected {
        border-color: var(--primary);
        background: #f0f8ff;
    }

    .order-checkbox {
        position: absolute;
        top: 15px;
        right: 15px;
    }

    .order-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        padding-right: 30px;
    }

    .selected-orders-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 15px;
        background: #f8f9fa;
    }

    .batch-order-item {
        display: flex;
        align-items: center;
        gap: 15px;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .batch-order-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .drag-handle {
        cursor: move;
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6c757d;
    }

    .order-number {
        display: inline-block;
        width: 30px;
        height: 30px;
        line-height: 30px;
        text-align: center;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        font-weight: bold;
        font-size: 12px;
    }

    .order-info {
        flex: 1;
    }

    .product-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .batch-summary {
        background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
    }

    .summary-item {
        text-align: center;
    }

    .summary-item label {
        color: rgba(255,255,255,0.8);
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .summary-item h4 {
        color: white;
        margin: 0;
        font-size: 28px;
        font-weight: bold;
    }

    .draggable-list {
        min-height: 100px;
    }

    .sortable-ghost {
        opacity: 0.4;
    }

    .batch-orders-section {
        margin: 20px 0;
    }
</style>
@endpush

@push('script')
<script src="https://cdn.jsdelivr.net/npm/vue@3.3.4/dist/vue.global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vuedraggable@4.1.0/dist/vuedraggable.umd.js"></script>
<script>
    const { createApp } = Vue;

    createApp({
        components: {
            draggable: window.vuedraggable
        },
        data() {
            return {
                areas: @json($areas),
                availableOrders: @json($orders),
                selectedOrders: [],
                selectedArea: '',
                searchQuery: '',
                batchData: {
                    delivery_date: '',
                    area_id: '',
                    notes: ''
                },
                drag: false,
                isSubmitting: false,
                today: new Date().toISOString().split('T')[0]
            }
        },
        computed: {
            filteredOrders() {
                let orders = this.availableOrders.filter(order =>
                    !this.isOrderSelected(order.id)
                );

                // Filter by area
                if (this.selectedArea) {
                    orders = orders.filter(order =>
                        order.customer.area && order.customer.area.id == this.selectedArea
                    );
                }

                // Filter by search
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase();
                    orders = orders.filter(order =>
                        order.invoice_no.toLowerCase().includes(query) ||
                        order.customer.name.toLowerCase().includes(query)
                    );
                }

                return orders;
            },
            totalAmount() {
                return this.selectedOrders.reduce((sum, order) =>
                    sum + parseFloat(order.total_price), 0
                );
            },
            canSubmit() {
                return this.selectedOrders.length > 0 &&
                       this.batchData.delivery_date;
            }
        },
        methods: {
            isOrderSelected(orderId) {
                return this.selectedOrders.some(order => order.id === orderId);
            },
            toggleOrder(order) {
                const index = this.selectedOrders.findIndex(o => o.id === order.id);
                if (index > -1) {
                    this.selectedOrders.splice(index, 1);
                } else {
                    this.selectedOrders.push(order);
                }
            },
            removeOrder(orderId) {
                const index = this.selectedOrders.findIndex(o => o.id === orderId);
                if (index > -1) {
                    this.selectedOrders.splice(index, 1);
                }
            },
            selectAllFiltered() {
                this.filteredOrders.forEach(order => {
                    if (!this.isOrderSelected(order.id)) {
                        this.selectedOrders.push(order);
                    }
                });
            },
            clearSelection() {
                this.selectedOrders = [];
            },
            filterOrders() {
                // Auto-select area if all selected orders are from same area
                if (this.selectedArea && this.selectedOrders.length > 0) {
                    this.batchData.area_id = this.selectedArea;
                }
            },
            formatPrice(value) {
                return parseFloat(value || 0).toFixed(2);
            },
            submitBatch() {
                if (!this.canSubmit) return;

                this.isSubmitting = true;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('delivery_date', this.batchData.delivery_date);
                formData.append('area_id', this.batchData.area_id || '');
                formData.append('notes', this.batchData.notes || '');

                // Add order IDs
                this.selectedOrders.forEach((order, index) => {
                    formData.append(`orders[${index}]`, order.id);
                });

                fetch('{{ route("admin.delivery.batch.store") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        this.showNotification('success', data.message);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        this.isSubmitting = false;
                        this.showNotification('error', data.message);
                    }
                })
                .catch(error => {
                    this.isSubmitting = false;
                    console.error('Error:', error);
                    this.showNotification('error', 'Failed to create batch');
                });
            },
            showNotification(type, message) {
                if (typeof iziToast !== 'undefined') {
                    iziToast[type]({
                        message: message,
                        position: 'topRight'
                    });
                } else {
                    alert(message);
                }
            }
        },
        mounted() {
            // Set default delivery date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            this.batchData.delivery_date = tomorrow.toISOString().split('T')[0];
        }
    }).mount('#batchApp');
</script>
@endpush