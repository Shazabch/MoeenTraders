@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="las la-map-marked-alt"></i> @lang('Areas Map View')
                </h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline--info" id="fitBoundsBtn">
                        <i class="las la-compress-arrows-alt"></i> @lang('Fit All')
                    </button>
                    <a href="{{ route('admin.areas.index') }}" class="btn btn-sm btn-outline--primary">
                        <i class="las la-list"></i> @lang('List View')
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    {{-- Sidebar with Areas List --}}
                    <div class="col-lg-3 border-end">
                        <div class="areas-sidebar">
                            <div class="sidebar-header">
                                <input
                                    type="text"
                                    class="form-control form-control-sm"
                                    id="areaSearch"
                                    placeholder="@lang('Search areas...')">
                            </div>

                            <div class="areas-list" id="areasList">
                                @forelse($areas as $area)
                                <div class="area-item" data-area-id="{{ $area->id }}" data-area-name="{{ strtolower($area->name) }}">
                                    <div class="area-color" style="background-color: {{ $area->color ?? '#' . substr(md5($area->id), 0, 6) }};"></div>
                                    <div class="area-info">
                                        <h6 class="area-name">{{ $area->name }}</h6>
                                        <div class="area-meta">
                                            <span class="badge badge--sm {{ $area->status ? 'badge--success' : 'badge--danger' }}">
                                                {{ $area->status ? __('Active') : __('Inactive') }}
                                            </span>
                                            <span class="customer-count">
                                                <i class="las la-store"></i> {{ $area->customers_count }}
                                            </span>
                                        </div>
                                        @if($area->description)
                                        <p class="area-description">{{ Str::limit($area->description, 50) }}</p>
                                        @endif
                                    </div>
                                    <div class="area-actions">
                                        <button class="btn btn-sm btn-outline--primary view-area-btn" data-area-id="{{ $area->id }}">
                                            <i class="las la-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-5">
                                    <i class="las la-map" style="font-size: 3rem; color: #ccc;"></i>
                                    <p class="text-muted">@lang('No areas found')</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- Map Container --}}
                    <div class="col-lg-9">
                        <div id="map" style="height: 700px; width: 100%;"></div>

                        {{-- Map Legend --}}
                        <div class="map-legend">
                            <h6 class="legend-title">@lang('Legend')</h6>
                            <div class="legend-items">
                                <div class="legend-item">
                                    <div class="legend-marker" style="background: #4CAF50;"></div>
                                    <span>@lang('Active Area')</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-marker" style="background: #f44336;"></div>
                                    <span>@lang('Inactive Area')</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Customer Details Modal --}}
<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="las la-store"></i> <span id="modalCustomerName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="customer-detail-item">
                    <strong>@lang('Area'):</strong>
                    <span id="modalAreaName"></span>
                </div>
                <div class="customer-detail-item">
                    <strong>@lang('Address'):</strong>
                    <span id="modalCustomerAddress"></span>
                </div>
                <div class="customer-detail-item">
                    <strong>@lang('Coordinates'):</strong>
                    <span id="modalCustomerCoords"></span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                <button type="button" class="btn btn--primary" id="directionsBtn">
                    <i class="las la-directions"></i> @lang('Get Directions')
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />

<style>
    /* Sidebar Styles */
    .areas-sidebar {
        height: 700px;
        overflow-y: auto;
        background: #f8f9fa;
    }

    .sidebar-header {
        padding: 15px;
        background: white;
        border-bottom: 1px solid #dee2e6;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .areas-list {
        padding: 10px;
    }

    /* Area Item */
    .area-item {
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 10px;
        display: flex;
        gap: 12px;
        align-items: flex-start;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .area-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
        transform: translateY(-2px);
    }

    .area-item.active {
        border-color: #007bff;
        background: #f0f8ff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .area-color {
        width: 4px;
        min-height: 60px;
        border-radius: 2px;
    }

    .area-info {
        flex: 1;
    }

    .area-name {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
    }

    .area-meta {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-bottom: 8px;
    }

    .customer-count {
        font-size: 12px;
        color: #6c757d;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .area-description {
        font-size: 12px;
        color: #6c757d;
        margin: 0;
    }

    .area-actions {
        display: flex;
        align-items: center;
    }

    /* Map Styles */
    #map {
        position: relative;
    }

    .map-legend {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        z-index: 1000;
    }

    .legend-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }

    .legend-items {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
    }

    .legend-marker {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.3);
    }

    /* Custom Marker Styles */
    .custom-marker {
        background: #4CAF50;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
    }

    .custom-marker.inactive {
        background: #f44336;
    }

    /* Popup Styles */
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
        box-shadow: 0 3px 14px rgba(0, 0, 0, 0.2);
    }

    .popup-content {
        padding: 5px;
    }

    .popup-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .popup-detail {
        font-size: 12px;
        color: #6c757d;
        margin-bottom: 4px;
    }

    .popup-detail i {
        margin-right: 4px;
    }

    /* Customer Modal */
    .customer-detail-item {
        padding: 10px 0;
        border-bottom: 1px solid #e5e5e5;
    }

    .customer-detail-item:last-child {
        border-bottom: none;
    }

    .customer-detail-item strong {
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    /* Scrollbar */
    .areas-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .areas-sidebar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .areas-sidebar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }

    .areas-sidebar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* Hidden state for search */
    .area-item.hidden {
        display: none;
    }

    /* Responsive */
    @media (max-width: 991px) {
        .areas-sidebar {
            height: 300px;
        }

        #map {
            height: 500px !important;
        }
    }
</style>
@endpush

@push('script')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>

<script>
    (function($) {
        "use strict";

        // Map data from Laravel
        const areasData = @json($areas->map(function($area) {
            return [
                'id' => $area->id,
                'name' => $area->name,
                'description' => $area->description,
                'status' => $area->status,
                'color' => $area->color ?? '#' . substr(md5($area->id), 0, 6),
                'customers' => $area->customers->map(function($customer) {
                    return [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'address' => $customer->address,
                        'latitude' => $customer->latitude ?? null,
                        'longitude' => $customer->longitude ?? null,
                    ];
                })
            ];
        }));

        let map;
        let markers = {};
        let markerClusterGroup;
        let selectedAreaId = null;
        let currentCustomerData = null;

        // Initialize map
        function initMap() {
            // Default center (you can change this to your preferred location)
            const defaultLat = 31.4504; // Okara, Pakistan
            const defaultLng = 73.4496;

            map = L.map('map').setView([defaultLat, defaultLng], 12);

            // Add tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Initialize marker cluster group
            markerClusterGroup = L.markerClusterGroup({
                chunkedLoading: true,
                spiderfyOnMaxZoom: true,
                showCoverageOnHover: false,
                zoomToBoundsOnClick: true
            });

            map.addLayer(markerClusterGroup);

            // Load all areas
            loadAllAreas();
        }

        // Load all areas and their customers
        function loadAllAreas() {
            areasData.forEach(function(area) {
                loadAreaMarkers(area);
            });

            // Fit bounds to show all markers
            if (markerClusterGroup.getLayers().length > 0) {
                map.fitBounds(markerClusterGroup.getBounds(), { padding: [50, 50] });
            }
        }

        // Load markers for a specific area
        function loadAreaMarkers(area) {
            if (!markers[area.id]) {
                markers[area.id] = [];
            }

            area.customers.forEach(function(customer) {
                // Skip if no coordinates
                if (!customer.latitude || !customer.longitude) {
                    return;
                }

                // Create custom icon
                const iconHtml = `
                    <div class="custom-marker ${area.status ? '' : 'inactive'}"
                         style="background-color: ${area.color}">
                        <i class="las la-store"></i>
                    </div>
                `;

                const customIcon = L.divIcon({
                    html: iconHtml,
                    className: 'custom-div-icon',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15]
                });

                // Create marker
                const marker = L.marker([customer.latitude, customer.longitude], {
                    icon: customIcon,
                    areaId: area.id,
                    customerId: customer.id
                });

                // Create popup content
                const popupContent = `
                    <div class="popup-content">
                        <div class="popup-title">${customer.name}</div>
                        <div class="popup-detail">
                            <i class="las la-map-marker"></i> ${customer.address || 'N/A'}
                        </div>
                        <div class="popup-detail">
                            <i class="las la-layer-group"></i> Area: ${area.name}
                        </div>
                        <button class="btn btn-sm btn-outline--primary mt-2 view-details-btn"
                                data-customer='${JSON.stringify(customer)}'
                                data-area-name="${area.name}">
                            <i class="las la-info-circle"></i> View Details
                        </button>
                    </div>
                `;

                marker.bindPopup(popupContent);
                markers[area.id].push(marker);
                markerClusterGroup.addLayer(marker);
            });
        }

        // View specific area
        function viewArea(areaId) {
            selectedAreaId = areaId;

            // Update UI
            $('.area-item').removeClass('active');
            $(`.area-item[data-area-id="${areaId}"]`).addClass('active');

            // Clear and show only selected area markers
            markerClusterGroup.clearLayers();

            if (markers[areaId] && markers[areaId].length > 0) {
                markers[areaId].forEach(function(marker) {
                    markerClusterGroup.addLayer(marker);
                });

                // Fit bounds to selected area
                const group = L.featureGroup(markers[areaId]);
                map.fitBounds(group.getBounds(), { padding: [50, 50] });
            } else {
                showNotification('info', 'This area has no customers with location data');
            }
        }

        // Fit all markers in view
        function fitAllMarkers() {
            selectedAreaId = null;
            $('.area-item').removeClass('active');

            markerClusterGroup.clearLayers();

            areasData.forEach(function(area) {
                if (markers[area.id]) {
                    markers[area.id].forEach(function(marker) {
                        markerClusterGroup.addLayer(marker);
                    });
                }
            });

            if (markerClusterGroup.getLayers().length > 0) {
                map.fitBounds(markerClusterGroup.getBounds(), { padding: [50, 50] });
            }
        }

        // Search areas
        $('#areaSearch').on('input', function() {
            const query = $(this).val().toLowerCase().trim();

            if (query === '') {
                $('.area-item').removeClass('hidden');
            } else {
                $('.area-item').each(function() {
                    const name = $(this).data('area-name');
                    if (name.includes(query)) {
                        $(this).removeClass('hidden');
                    } else {
                        $(this).addClass('hidden');
                    }
                });
            }
        });

        // Area item click
        $(document).on('click', '.area-item', function(e) {
            if (!$(e.target).closest('.view-area-btn').length) {
                const areaId = $(this).data('area-id');
                viewArea(areaId);
            }
        });

        // View area button
        $(document).on('click', '.view-area-btn', function(e) {
            e.stopPropagation();
            const areaId = $(this).data('area-id');
            viewArea(areaId);
        });

        // Fit bounds button
        $('#fitBoundsBtn').on('click', function() {
            fitAllMarkers();
        });

        // View details button in popup
        $(document).on('click', '.view-details-btn', function() {
            const customer = JSON.parse($(this).attr('data-customer'));
            const areaName = $(this).data('area-name');

            currentCustomerData = customer;

            $('#modalCustomerName').text(customer.name);
            $('#modalAreaName').text(areaName);
            $('#modalCustomerAddress').text(customer.address || 'N/A');
            $('#modalCustomerCoords').text(`${customer.latitude}, ${customer.longitude}`);

            $('#customerModal').modal('show');
        });

        // Get directions button
        $('#directionsBtn').on('click', function() {
            if (currentCustomerData && currentCustomerData.latitude && currentCustomerData.longitude) {
                const url = `https://www.google.com/maps/dir/?api=1&destination=${currentCustomerData.latitude},${currentCustomerData.longitude}`;
                window.open(url, '_blank');
            }
        });

        // Show notification
        function showNotification(type, message) {
            if (typeof iziToast !== 'undefined') {
                iziToast[type]({
                    title: type === 'success' ? 'Success' : type === 'info' ? 'Info' : 'Error',
                    message: message,
                    position: 'topRight'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(type.toUpperCase() + ': ' + message);
            }
        }

        // Initialize map on page load
        $(document).ready(function() {
            initMap();
        });

    })(jQuery);
</script>
@endpush