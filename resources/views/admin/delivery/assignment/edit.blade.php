@extends('admin.layouts.app')
@section('panel')
<div class="row" id="assignmentEditApp">
    <!-- Left Side: Batch Info -->
    <div class="col-lg-4 mb-30">
        <div class="card">
            <div class="card-header bg--primary">
                <h5 class="card-title text-white">@lang('Batch Information')</h5>
            </div>
            <div class="card-body">
                <div class="batch-info">
                    <div class="info-item">
                        <label>@lang('Batch Number')</label>
                        <h6>{{ $assignment->batch->batch_number }}</h6>
                    </div>
                    <div class="info-item">
                        <label>@lang('Delivery Date')</label>
                        <h6>{{ showDateTime($assignment->batch->delivery_date, 'd M, Y') }}</h6>
                    </div>
                    <div class="info-item">
                        <label>@lang('Total Orders')</label>
                        <h6>{{ $assignment->batch->total_orders }} orders</h6>
                    </div>
                    <div class="info-item">
                        <label>@lang('Total Amount')</label>
                        <h6 class="text--primary">{{ gs('cur_sym') }}{{ showAmount($assignment->batch->total_amount) }}</h6>
                    </div>
                    @if($assignment->batch->area)
                    <div class="info-item">
                        <label>@lang('Area')</label>
                        <h6><i class="las la-map-marker"></i> {{ $assignment->batch->area->name }}</h6>
                    </div>
                    @endif
                </div>

                <hr>

                <!-- Current Assignment Info -->
                <h6 class="mb-3">@lang('Current Assignment')</h6>
                <div class="current-assignment">
                    <div class="info-item">
                        <label>@lang('Vehicle')</label>
                        <h6>{{ $assignment->vehicle->vehicle_number }}</h6>
                        <small class="text-muted">{{ $assignment->vehicle->driver_name }}</small>
                    </div>
                    <div class="info-item">
                        <label>@lang('Assigned On')</label>
                        <h6>{{ showDateTime($assignment->assigned_at, 'd M, Y h:i A') }}</h6>
                    </div>
                    <div class="info-item">
                        <label>@lang('Status')</label>
                        <span class="badge badge--{{ $assignment->status == 'assigned' ? 'info' : 'warning' }}">
                            {{ ucfirst($assignment->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Edit Assignment -->
    <div class="col-lg-8 mb-30">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">@lang('Edit Vehicle Assignment')</h5>
            </div>
            <div class="card-body">
                <form @submit.prevent="submitUpdate">
                    @csrf
                    @method('PUT')

                    <!-- Vehicle Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Select Vehicle') <span class="text--danger">*</span></label>
                                <select class="form-control" v-model="assignmentData.vehicle_id" required>
                                    <option value="">@lang('Select Vehicle')</option>
                                    <option v-for="vehicle in vehicles" :key="vehicle.id" :value="vehicle.id" :selected="vehicle.id == {{ $assignment->vehicle_id }}">
                                        @{{ vehicle.vehicle_number }} - @{{ vehicle.driver_name }} (@{{ vehicle.vehicle_type }})
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>@lang('Starting KM')</label>
                                <input
                                    type="number"
                                    class="form-control"
                                    v-model.number="assignmentData.starting_km"
                                    step="0.01"
                                    min="0"
                                    placeholder="@lang('Enter starting odometer')"
                                    value="{{ $assignment->starting_km }}">
                            </div>
                        </div>
                    </div>

                    <!-- Containers Section -->
                    <div class="containers-section">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">@lang('Vehicle Containers')</h6>
                            <button
                                type="button"
                                class="btn btn-sm btn--primary"
                                @click="addContainer">
                                <i class="las la-plus"></i> @lang('Add Container')
                            </button>
                        </div>

                        <div class="containers-list">
                            <div
                                v-for="(container, cIndex) in assignmentData.containers"
                                :key="container.id || cIndex"
                                class="container-card">

                                <input type="hidden" :name="`containers[${cIndex}][id]`" :value="container.id">

                                <div class="container-header">
                                    <input
                                        type="text"
                                        class="form-control form-control-lg"
                                        v-model="container.name"
                                        :placeholder="'Container ' + (cIndex + 1)"
                                        required>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline--danger"
                                        @click="removeContainer(cIndex)"
                                        v-if="assignmentData.containers.length > 1">
                                        <i class="las la-trash"></i>
                                    </button>
                                </div>

                                <!-- Container Items -->
                                <div class="container-items">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">@lang('Items in this container')</small>
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-outline--primary"
                                            @click="addItem(cIndex)">
                                            <i class="las la-plus"></i> @lang('Add Item')
                                        </button>
                                    </div>

                                    <div
                                        v-for="(item, iIndex) in container.items"
                                        :key="item.id || iIndex"
                                        class="item-row">

                                        <input type="hidden" :name="`containers[${cIndex}][items][${iIndex}][id]`" :value="item.id">

                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <select class="form-control form-control-sm" v-model="item.type" required>
                                                    <option value="order">@lang('Order Item')</option>
                                                    <option value="extra">@lang('Extra Stock')</option>
                                                </select>
                                            </div>

                                            <div class="col-md-3" v-if="item.type === 'order'">
                                                <select class="form-control form-control-sm" v-model="item.sale_id">
                                                    <option value="">@lang('Select Order')</option>
                                                    <option v-for="order in batchOrders" :key="order.id" :value="order.sale_id">
                                                        #@{{ order.sale.invoice_no }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div :class="item.type === 'order' ? 'col-md-3' : 'col-md-6'">
                                                <select class="form-control form-control-sm" v-model="item.product_id" required>
                                                    <option value="">@lang('Select Product')</option>
                                                    <option v-for="product in availableProducts" :key="product.id" :value="product.id">
                                                        @{{ product.name }}
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="col-md-2">
                                                <input
                                                    type="number"
                                                    class="form-control form-control-sm"
                                                    v-model.number="item.quantity"
                                                    placeholder="Qty"
                                                    min="1"
                                                    required>
                                            </div>

                                            <div class="col-md-1">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline--danger w-100"
                                                    @click="removeItem(cIndex, iIndex)">
                                                    <i class="las la-times"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row mt-2" v-if="getProductDetails(item.product_id)">
                                            <div class="col-12">
                                                <div class="product-preview">
                                                    <small class="text-muted">
                                                        <strong>@{{ getProductDetails(item.product_id).name }}</strong>
                                                        <span v-if="item.type === 'extra'" class="badge badge--warning ms-2">Extra Stock</span>
                                                        <span v-else class="badge badge--info ms-2">Order Item</span>
                                                    </small>
                                                    <small class="d-block text-muted" v-if="item.sale_id && getOrderDetails(item.sale_id)">
                                                        Order: #@{{ getOrderDetails(item.sale_id).invoice_no }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="container.items.length === 0" class="text-center py-3 text-muted">
                                        <small>@lang('No items added yet')</small>
                                    </div>
                                </div>

                                <!-- Container Notes -->
                                <div class="mt-2">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm"
                                        v-model="container.notes"
                                        placeholder="@lang('Container notes (optional)')">
                                </div>
                            </div>

                            <div v-if="assignmentData.containers.length === 0" class="text-center py-5">
                                <i class="las la-box-open la-3x text-muted"></i>
                                <p class="mt-2 text-muted">@lang('No containers added yet')</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Load Options -->
                    <div class="quick-load-section mt-4">
                        <h6 class="mb-3">@lang('Quick Load Options')</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <button
                                    type="button"
                                    class="btn btn-outline--success w-100"
                                    @click="autoLoadFromOrders">
                                    <i class="las la-magic"></i> @lang('Auto-Load from Orders')
                                </button>
                            </div>
                            <div class="col-md-6 mb-2">
                                <button
                                    type="button"
                                    class="btn btn-outline--info w-100"
                                    @click="showLoadingSummary">
                                    <i class="las la-list"></i> @lang('View Loading Summary')
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment Notes -->
                    <div class="form-group mt-4">
                        <label>@lang('Assignment Notes')</label>
                        <textarea
                            class="form-control"
                            v-model="assignmentData.notes"
                            rows="3"
                            placeholder="@lang('Add any special instructions for the driver...')"></textarea>
                    </div>

                    <!-- Summary -->
                    <div class="assignment-summary mt-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="summary-box">
                                    <label>@lang('Total Containers')</label>
                                    <h4>@{{ assignmentData.containers.length }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-box">
                                    <label>@lang('Total Items')</label>
                                    <h4>@{{ totalItems }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="summary-box">
                                    <label>@lang('Total Quantity')</label>
                                    <h4>@{{ totalQuantity }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <a href="{{ route('admin.delivery.assignment.show', $assignment->id) }}" class="btn btn--secondary w-100 h-45">
                                <i class="las la-times"></i> @lang('Cancel')
                            </a>
                        </div>
                        <div class="col-md-6">
                            <button
                                type="submit"
                                class="btn btn--primary w-100 h-45"
                                :disabled="!canSubmit || isSubmitting">
                                <span v-if="isSubmitting">
                                    <i class="las la-spinner la-spin"></i> @lang('Updating...')
                                </span>
                                <span v-else>
                                    <i class="las la-save"></i> @lang('Update Assignment')
                                </span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Loading Summary Modal -->
<div class="modal fade" id="summaryModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Loading Summary')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div v-if="loadingSummary.length > 0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>@lang('Product')</th>
                                <th>@lang('Total Qty')</th>
                                <th>@lang('From Orders')</th>
                                <th>@lang('Extra Stock')</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in loadingSummary" :key="item.product_id">
                                <td>@{{ item.product_name }}</td>
                                <td><strong>@{{ item.total_qty }}</strong></td>
                                <td>@{{ item.order_qty }}</td>
                                <td>@{{ item.extra_qty }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-5">
                    <p class="text-muted">@lang('No items loaded yet')</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('breadcrumb-plugins')
<x-back route="{{ route('admin.delivery.assignment.show', $assignment->id) }}" />
@endpush

@push('style')
<style>
    .batch-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-item label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
        display: block;
    }

    .info-item h6 {
        margin: 0;
        font-weight: 600;
    }

    .current-assignment {
        background: #fff3cd;
        padding: 15px;
        border-radius: 6px;
        border: 1px solid #ffc107;
    }

    .containers-section {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        margin: 20px 0;
    }

    .containers-list {
        max-height: 600px;
        overflow-y: auto;
    }

    .container-card {
        background: white;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
    }

    .container-header {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 15px;
    }

    .container-header input {
        font-weight: bold;
        border: 2px solid var(--primary);
    }

    .container-items {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
    }

    .item-row {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 10px;
    }

    .product-preview {
        background: #e7f3ff;
        padding: 5px 10px;
        border-radius: 4px;
    }

    .assignment-summary {
        background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
        color: white;
        padding: 20px;
        border-radius: 8px;
    }

    .summary-box {
        text-align: center;
    }

    .summary-box label {
        color: rgba(255,255,255,0.8);
        font-size: 12px;
        text-transform: uppercase;
        display: block;
        margin-bottom: 5px;
    }

    .summary-box h4 {
        color: white;
        margin: 0;
        font-size: 32px;
        font-weight: bold;
    }

    .quick-load-section {
        background: #fff3cd;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ffc107;
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
                batchOrders: @json($assignment->batch->batchOrders),
                vehicles: @json($vehicles),
                assignmentData: {
                    id: {{ $assignment->id }},
                    batch_id: {{ $assignment->batch_id }},
                    vehicle_id: {{ $assignment->vehicle_id }},
                    starting_km: {{ $assignment->starting_km ?? 0 }},
                    notes: `{{ $assignment->notes }}`,
                    containers: @json($assignment->containers->map(function($container) {
                        return [
                            'id' => $container->id,
                            'name' => $container->container_name,
                            'notes' => $container->notes,
                            'items' => $container->items->map(function($item) {
                                return [
                                    'id' => $item->id,
                                    'type' => $item->item_type,
                                    'sale_id' => $item->sale_id,
                                    'product_id' => $item->product_id,
                                    'quantity' => $item->quantity,
                                    'notes' => $item->notes
                                ];
                            })->toArray()
                        ];
                    }))
                },
                isSubmitting: false
            }
        },
        computed: {
            availableProducts() {
                const products = new Map();
                this.batchOrders.forEach(order => {
                    order.sale.sale_details.forEach(detail => {
                        if (!products.has(detail.product.id)) {
                            products.set(detail.product.id, detail.product);
                        }
                    });
                });
                return Array.from(products.values());
            },
            totalItems() {
                return this.assignmentData.containers.reduce((sum, container) =>
                    sum + container.items.length, 0
                );
            },
            totalQuantity() {
                let total = 0;
                this.assignmentData.containers.forEach(container => {
                    container.items.forEach(item => {
                        total += parseInt(item.quantity) || 0;
                    });
                });
                return total;
            },
            canSubmit() {
                return this.assignmentData.vehicle_id &&
                       this.assignmentData.containers.length > 0 &&
                       this.totalItems > 0;
            },
            loadingSummary() {
                const summary = new Map();

                this.assignmentData.containers.forEach(container => {
                    container.items.forEach(item => {
                        if (!item.product_id) return;

                        const key = item.product_id;
                        if (!summary.has(key)) {
                            const product = this.getProductDetails(item.product_id);
                            summary.set(key, {
                                product_id: item.product_id,
                                product_name: product ? product.name : 'Unknown',
                                total_qty: 0,
                                order_qty: 0,
                                extra_qty: 0
                            });
                        }

                        const entry = summary.get(key);
                        const qty = parseInt(item.quantity) || 0;
                        entry.total_qty += qty;

                        if (item.type === 'order') {
                            entry.order_qty += qty;
                        } else {
                            entry.extra_qty += qty;
                        }
                    });
                });

                return Array.from(summary.values());
            }
        },
        methods: {
            addContainer() {
                this.assignmentData.containers.push({
                    name: `Container ${this.assignmentData.containers.length + 1}`,
                    items: [],
                    notes: ''
                });
            },
            removeContainer(index) {
                if (this.assignmentData.containers.length > 1) {
                    this.assignmentData.containers.splice(index, 1);
                }
            },
            addItem(containerIndex) {
                this.assignmentData.containers[containerIndex].items.push({
                    type: 'order',
                    sale_id: '',
                    product_id: '',
                    quantity: 1,
                    notes: ''
                });
            },
            removeItem(containerIndex, itemIndex) {
                this.assignmentData.containers[containerIndex].items.splice(itemIndex, 1);
            },
            getProductDetails(productId) {
                if (!productId) return null;
                return this.availableProducts.find(p => p.id == productId);
            },
            getOrderDetails(saleId) {
                if (!saleId) return null;
                return this.batchOrders.find(o => o.sale_id == saleId)?.sale;
            },
            autoLoadFromOrders() {
                if (!confirm('This will replace all current containers with auto-loaded containers. Continue?')) {
                    return;
                }

                this.assignmentData.containers = [];

                const orderGroups = new Map();

                this.batchOrders.forEach(order => {
                    const items = [];
                    order.sale.sale_details.forEach(detail => {
                        items.push({
                            type: 'order',
                            sale_id: order.sale_id,
                            product_id: detail.product.id,
                            quantity: detail.quantity,
                            notes: ''
                        });
                    });

                    orderGroups.set(order.sale.invoice_no, items);
                });

                let containerIndex = 1;
                orderGroups.forEach((items, invoiceNo) => {
                    this.assignmentData.containers.push({
                        name: `Order #${invoiceNo}`,
                        items: items,
                        notes: `Auto-loaded from order ${invoiceNo}`
                    });
                    containerIndex++;
                });

                this.showNotification('success', 'Orders auto-loaded successfully!');
            },
            showLoadingSummary() {
                const modal = new bootstrap.Modal(document.getElementById('summaryModal'));
                modal.show();
            },
            formatPrice(value) {
                return parseFloat(value || 0).toFixed(2);
            },
            submitUpdate() {
                if (!this.canSubmit) return;

                const emptyContainers = this.assignmentData.containers.filter(c => c.items.length === 0);
                if (emptyContainers.length > 0) {
                    this.showNotification('error', 'All containers must have at least one item');
                    return;
                }

                this.isSubmitting = true;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PUT');
                formData.append('batch_id', this.assignmentData.batch_id);
                formData.append('vehicle_id', this.assignmentData.vehicle_id);
                formData.append('starting_km', this.assignmentData.starting_km || 0);
                formData.append('notes', this.assignmentData.notes || '');

                this.assignmentData.containers.forEach((container, cIndex) => {
                    if (container.id) {
                        formData.append(`containers[${cIndex}][id]`, container.id);
                    }
                    formData.append(`containers[${cIndex}][name]`, container.name);
                    formData.append(`containers[${cIndex}][notes]`, container.notes || '');

                    container.items.forEach((item, iIndex) => {
                        if (item.id) {
                            formData.append(`containers[${cIndex}][items][${iIndex}][id]`, item.id);
                        }
                        formData.append(`containers[${cIndex}][items][${iIndex}][type]`, item.type);
                        formData.append(`containers[${cIndex}][items][${iIndex}][sale_id]`, item.sale_id || '');
                        formData.append(`containers[${cIndex}][items][${iIndex}][product_id]`, item.product_id);
                        formData.append(`containers[${cIndex}][items][${iIndex}][quantity]`, item.quantity);
                        formData.append(`containers[${cIndex}][items][${iIndex}][notes]`, item.notes || '');
                    });
                });

                fetch('{{ route("admin.delivery.assignment.update", $assignment->id) }}', {
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
                    this.showNotification('error', 'Failed to update assignment');
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
            console.log('Assignment data loaded:', this.assignmentData);
            console.log('Available products:', this.availableProducts);
        }
    }).mount('#assignmentEditApp');
</script>
@endpush