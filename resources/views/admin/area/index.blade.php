@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10 ">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title">@lang($pageTitle)</h5>
                <button class="btn btn-sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#areaModal" id="addNewBtn">
                    <i class="las la-plus"></i> @lang('Add New Area')
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>@lang('Name')</th>
                                <th>@lang('Description')</th>
                                <th>@lang('Customers Assigned')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($areas as $area)
                            <tr>
                                <td data-label="@lang('Name')">{{ $area->name }}</td>
                                <td data-label="@lang('Description')">{{ strLimit($area->description, 50) }}</td>
                                <td data-label="@lang('Customers Assigned')">
                                    <span class="badge badge--dark">{{ $area->customers_count }}</span>
                                </td>
                                <td data-label="@lang('Status')">
                                    @if($area->status)
                                    <span class="badge badge--success">@lang('Active')</span>
                                    @else
                                    <span class="badge badge--danger">@lang('Disabled')</span>
                                    @endif
                                </td>
                                <td data-label="@lang('Action')">
                                    <div class="btn-group">
                                        <button
                                            class="btn btn-sm btn-outline--primary edit-btn"
                                            data-bs-toggle="modal"
                                            data-bs-target="#areaModal"
                                            data-area-id="{{ $area->id }}"
                                            title="@lang('Edit')">
                                            <i class="las la-pen"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-outline--danger delete-btn"
                                            data-area-id="{{ $area->id }}"
                                            title="@lang('Delete')">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage ?? 'No areas found') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($areas->hasPages())
            <div class="card-footer py-4">
                {{ paginateLinks($areas) }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Area Create/Edit Modal --}}
<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="areaForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="areaModalLabel">@lang('Create New Area')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="areaId" name="area_id">
                    <input type="hidden" id="isEdit" value="0">

                    <div class="row">
                        {{-- Area Details Column --}}
                        <div class="col-lg-4">
                            <div class="form-group mb-3">
                                <label for="name" class="form-label required">@lang('Area Name')</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback" id="error-name"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description" class="form-label">@lang('Description')</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                <div class="invalid-feedback" id="error-description"></div>
                            </div>

                            <div class="form-group mb-3" id="statusGroup" style="display: none;">
                                <label for="status" class="form-label required">@lang('Status')</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="1">@lang('Active')</option>
                                    <option value="0">@lang('Disabled')</option>
                                </select>
                                <div class="invalid-feedback" id="error-status"></div>
                            </div>

                            {{-- Customer Stats --}}
                            <div class="customer-stats">
                                <div class="stat-card">
                                    <i class="las la-store text-primary"></i>
                                    <div class="stat-info">
                                        <span class="stat-label">@lang('Total Customers')</span>
                                        <span class="stat-value" id="totalCustomers">{{ $customers->count() }}</span>
                                    </div>
                                </div>
                                <div class="stat-card mt-2">
                                    <i class="las la-check-circle text-success"></i>
                                    <div class="stat-info">
                                        <span class="stat-label">@lang('Selected')</span>
                                        <span class="stat-value" id="selectedCustomers">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Customer Assignment Column --}}
                        <div class="col-lg-8">
                            <div class="customer-selection-header">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="mb-0">
                                        <i class="las la-users"></i> @lang('Assign Customers')
                                    </h6>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllCustomers">
                                        <label class="form-check-label" for="selectAllCustomers">
                                            @lang('Select All')
                                        </label>
                                    </div>
                                </div>

                                {{-- Search Box --}}
                                <div class="customer-search-box mb-3">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="las la-search"></i>
                                        </span>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="customerSearch"
                                            placeholder="@lang('Search customers by name or address...')">
                                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                            <i class="las la-times"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        <span id="searchResultCount">{{ $customers->count() }}</span> @lang('customer(s) found')
                                    </small>
                                </div>
                            </div>

                            {{-- Customers List with Scroll --}}
                            <div class="customer-assignment-list">
                                <div id="customersList">
                                    @foreach($customers as $customer)
                                    <div class="customer-item" data-customer-id="{{ $customer->id }}" data-customer-name="{{ strtolower($customer->name) }}" data-customer-address="{{ strtolower($customer->address ?? '') }}">
                                        <div class="form-check">
                                            <input
                                                class="form-check-input customer-checkbox"
                                                type="checkbox"
                                                name="customer_ids[]"
                                                value="{{ $customer->id }}"
                                                id="customer_{{ $customer->id }}">
                                            <label class="form-check-label" for="customer_{{ $customer->id }}">
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        <i class="las la-store"></i>
                                                    </div>
                                                    <div class="customer-details">
                                                        <strong class="customer-name">{{ $customer->name }}</strong>
                                                        @if($customer->address)
                                                        <small class="customer-address">
                                                            <i class="las la-map-marker"></i> {{ $customer->address }}
                                                        </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                {{-- No Results Message --}}
                                <div id="noResults" style="display: none;" class="text-center py-5">
                                    <i class="las la-search" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted">@lang('No customers found matching your search')</p>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="error-customer_ids"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn--primary" id="submitBtn">
                        <span class="btn-text">@lang('Save Area')</span>
                        <span class="btn-loading" style="display: none;">
                            <i class="las la-spinner la-spin"></i> @lang('Processing...')
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Confirm Deletion')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you sure you want to delete this area? This will remove all customer assignments but will not delete the customers themselves.')</p>
                <p class="text-danger">@lang('This action cannot be undone.')</p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="deleteAreaId">
                <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Cancel')</button>
                <button type="button" class="btn btn--danger" id="confirmDeleteBtn">
                    <span class="btn-text">@lang('Delete')</span>
                    <span class="btn-loading" style="display: none;">
                        <i class="las la-spinner la-spin"></i> @lang('Deleting...')
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<style>
    /* Customer Assignment List */
    .customer-assignment-list {
        max-height: 450px;
        overflow-y: auto;
        padding: 15px;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        background: #f8f9fa;
    }

    /* Custom Scrollbar */
    .customer-assignment-list::-webkit-scrollbar {
        width: 8px;
    }

    .customer-assignment-list::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .customer-assignment-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 10px;
    }

    .customer-assignment-list::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Customer Item */
    .customer-item {
        margin-bottom: 10px;
        padding: 12px;
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        transition: all 0.3s ease;
    }

    .customer-item:hover {
        background: #f0f8ff;
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        transform: translateY(-2px);
    }

    .customer-item .form-check {
        display: flex;
        align-items: flex-start;
    }

    .customer-item .form-check-input {
        margin-top: 8px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .customer-item .form-check-label {
        width: 100%;
        cursor: pointer;
        margin-left: 10px;
    }

    /* Customer Info Layout */
    .customer-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .customer-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .customer-avatar i {
        font-size: 20px;
        color: white;
    }

    .customer-details {
        display: flex;
        flex-direction: column;
        gap: 4px;
        flex: 1;
    }

    .customer-name {
        font-size: 14px;
        color: #333;
        display: block;
    }

    .customer-address {
        color: #6c757d;
        font-size: 12px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .customer-address i {
        font-size: 14px;
    }

    /* Search Box */
    .customer-search-box {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
    }

    .customer-search-box .input-group-text {
        background: white;
        border-right: 0;
    }

    .customer-search-box input {
        border-left: 0;
        border-right: 0;
    }

    .customer-search-box input:focus {
        box-shadow: none;
        border-color: #dee2e6;
    }

    #clearSearch {
        border-left: 0;
    }

    /* Customer Stats */
    .customer-stats {
        margin-top: 20px;
    }

    .stat-card {
        background: white;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stat-card i {
        font-size: 32px;
    }

    .stat-info {
        display: flex;
        flex-direction: column;
    }

    .stat-label {
        font-size: 12px;
        color: #6c757d;
        font-weight: 500;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    /* Selection Header */
    .customer-selection-header h6 {
        font-weight: 600;
        color: #333;
    }

    .customer-selection-header h6 i {
        color: #007bff;
        margin-right: 5px;
    }

    /* Other Styles */
    .required::after {
        content: " *";
        color: #dc3545;
    }

    .btn-group .btn {
        margin-right: 5px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .invalid-feedback {
        display: none;
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }

    .invalid-feedback.d-block {
        display: block !important;
    }

    .form-control.is-invalid {
        border-color: #dc3545;
    }

    /* Hidden State for Search */
    .customer-item.hidden {
        display: none;
    }
</style>
@endpush

@push('script')
<script>
    (function($) {
        "use strict";

        // Store areas data
        const areasData = @json($areasData);
        const totalCustomers = {{ $customers->count() }};

        // Update selected customer count
        function updateSelectedCount() {
            const count = $('.customer-checkbox:checked').length;
            $('#selectedCustomers').text(count);
        }

        // Reset form to initial state
        function resetForm() {
            $('#areaForm')[0].reset();
            $('#areaId').val('');
            $('#isEdit').val('0');
            $('#areaModalLabel').text('@lang("Create New Area")');
            $('#submitBtn .btn-text').text('@lang("Save Area")');
            $('#statusGroup').hide();
            $('.customer-checkbox').prop('checked', false);
            $('#selectAllCustomers').prop('checked', false);
            $('#customerSearch').val('');
            $('.customer-item').removeClass('hidden');
            $('#noResults').hide();
            updateSelectedCount();
            updateSearchCount();
            clearErrors();
        }

        // Clear all error messages
        function clearErrors() {
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').removeClass('d-block').text('');
        }

        // Display validation errors
        function displayErrors(errors) {
            clearErrors();
            $.each(errors, function(field, messages) {
                const input = $('[name="' + field + '"], [name="' + field + '[]"]').first();
                input.addClass('is-invalid');
                $('#error-' + field).addClass('d-block').text(messages[0]);
            });
        }

        // Show notification
        function showNotification(type, message) {
            if (typeof iziToast !== 'undefined') {
                iziToast[type]({
                    title: type === 'success' ? 'Success' : 'Error',
                    message: message,
                    position: 'topRight'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(type.toUpperCase() + ': ' + message);
            }
        }

        // Update search result count
        function updateSearchCount() {
            const visibleCount = $('.customer-item:not(.hidden)').length;
            $('#searchResultCount').text(visibleCount);
        }

        // Search functionality
        function searchCustomers(query) {
            query = query.toLowerCase().trim();
            let visibleCount = 0;

            if (query === '') {
                $('.customer-item').removeClass('hidden');
                visibleCount = totalCustomers;
                $('#noResults').hide();
            } else {
                $('.customer-item').each(function() {
                    const name = $(this).data('customer-name');
                    const address = $(this).data('customer-address') || '';

                    if (name.includes(query) || address.includes(query)) {
                        $(this).removeClass('hidden');
                        visibleCount++;
                    } else {
                        $(this).addClass('hidden');
                    }
                });

                if (visibleCount === 0) {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }
            }

            $('#searchResultCount').text(visibleCount);
        }

        // Customer search input
        $('#customerSearch').on('input', function() {
            searchCustomers($(this).val());
        });

        // Clear search button
        $('#clearSearch').on('click', function() {
            $('#customerSearch').val('');
            searchCustomers('');
        });

        // Add new area button
        $('#addNewBtn').on('click', function() {
            resetForm();
        });

        // Select all customers checkbox
        $('#selectAllCustomers').on('change', function() {
            const isChecked = $(this).is(':checked');
            // Only check/uncheck visible customers
            $('.customer-item:not(.hidden) .customer-checkbox').prop('checked', isChecked);
            updateSelectedCount();
        });

        // Update count when individual checkbox changes
        $(document).on('change', '.customer-checkbox', function() {
            updateSelectedCount();

            // Update "Select All" checkbox state
            const totalVisible = $('.customer-item:not(.hidden)').length;
            const checkedVisible = $('.customer-item:not(.hidden) .customer-checkbox:checked').length;
            $('#selectAllCustomers').prop('checked', totalVisible === checkedVisible && totalVisible > 0);
        });

        // Edit area button
        $(document).on('click', '.edit-btn', function() {
            resetForm();
            const areaId = $(this).data('area-id');
            const area = areasData.find(a => a.id === areaId);

            if (area) {
                $('#isEdit').val('1');
                $('#areaId').val(area.id);
                $('#name').val(area.name);
                $('#description').val(area.description || '');
                $('#status').val(area.status ? 1 : 0);
                $('#statusGroup').show();
                $('#areaModalLabel').text('@lang("Edit Area"): ' + area.name);
                $('#submitBtn .btn-text').text('@lang("Update Area")');

                // Check assigned customers
                if (area.customers && area.customers.length > 0) {
                    area.customers.forEach(function(customer) {
                        $('#customer_' + customer.id).prop('checked', true);
                    });
                }

                updateSelectedCount();
            }
        });

        // Submit form (Create/Update)
        $('#areaForm').on('submit', function(e) {
            e.preventDefault();

            const isEdit = $('#isEdit').val() === '1';
            const areaId = $('#areaId').val();
            const formData = new FormData(this);

            let url = "{{ route('admin.areas.store') }}";

            if (isEdit) {
                url = "{{ route('admin.areas.update', ':id') }}".replace(':id', areaId);
                formData.append('_method', 'PUT');
            }

            // Show loading state
            $('#submitBtn').prop('disabled', true);
            $('#submitBtn .btn-text').hide();
            $('#submitBtn .btn-loading').show();
            clearErrors();

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').show();
                    $('#submitBtn .btn-loading').hide();

                    if (response.success) {
                        showNotification('success', response.message);
                        $('#areaModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('error', response.message || 'An error occurred');
                    }
                },
                error: function(xhr) {
                    $('#submitBtn').prop('disabled', false);
                    $('#submitBtn .btn-text').show();
                    $('#submitBtn .btn-loading').hide();

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        displayErrors(errors);
                        showNotification('error', 'Please check the form for errors.');
                    } else {
                        const message = xhr.responseJSON?.message || 'An unexpected error occurred.';
                        showNotification('error', message);
                    }
                }
            });
        });

        // Delete area button
        $(document).on('click', '.delete-btn', function() {
            const areaId = $(this).data('area-id');
            $('#deleteAreaId').val(areaId);
            $('#deleteModal').modal('show');
        });

        // Confirm delete
        $('#confirmDeleteBtn').on('click', function() {
            const areaId = $('#deleteAreaId').val();
            const url = "{{ route('admin.areas.destroy', ':id') }}".replace(':id', areaId);

            $(this).prop('disabled', true);
            $(this).find('.btn-text').hide();
            $(this).find('.btn-loading').show();

            $.ajax({
                url: url,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    $('#confirmDeleteBtn').prop('disabled', false);
                    $('#confirmDeleteBtn .btn-text').show();
                    $('#confirmDeleteBtn .btn-loading').hide();

                    if (response.success) {
                        showNotification('success', response.message);
                        $('#deleteModal').modal('hide');
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showNotification('error', response.message);
                    }
                },
                error: function(xhr) {
                    $('#confirmDeleteBtn').prop('disabled', false);
                    $('#confirmDeleteBtn .btn-text').show();
                    $('#confirmDeleteBtn .btn-loading').hide();

                    const message = xhr.responseJSON?.message || 'Failed to delete area.';
                    showNotification('error', message);
                }
            });
        });

        // Reset form when modal is hidden
        $('#areaModal').on('hidden.bs.modal', function() {
            resetForm();
        });

    })(jQuery);
</script>
@endpush