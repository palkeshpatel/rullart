@extends('layouts.vertical', ['title' => 'Orders List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Orders List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Status</label>
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="">--All Status--</option>
                                <option value="2">Process</option>
                                <option value="4">Delivered</option>
                                <option value="5">Cancelled</option>
                                <option value="6">Returned</option>
                                <option value="7">Shipped</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Country</label>
                            <select id="countryFilter" class="form-select form-select-sm">
                                <option value="">--All Country--</option>
                                @foreach ($countries ?? [] as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Orders List</h4>
                    <a href="{{ url('/admin/orders/export') }}" class="btn btn-success btn-sm" title="Export to Excel" id="exportBtn">
                        <i class="ti ti-file-excel me-1"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search order...">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="ordersTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Order#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Order Status</th>
                                    <th>Area</th>
                                    <th>Order Date</th>
                                    <th>Shipping Method</th>
                                    <th>Payment Method</th>
                                    <th>Ref #</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order View Modal Container -->
    <div id="orderViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            let dataTablesLoaded = false;

            function loadDataTables(callback) {
                if (dataTablesLoaded && typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
                    callback();
                    return;
                }

                if (typeof jQuery === 'undefined') {
                    setTimeout(function() {
                        loadDataTables(callback);
                    }, 50);
                    return;
                }

                // jQuery is ready, now load DataTables
                if (!dataTablesLoaded) {
                    const dtScript = document.createElement('script');
                    dtScript.src = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
                    dtScript.onload = function() {
                        const dtRespScript = document.createElement('script');
                        dtRespScript.src =
                            'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
                        dtRespScript.onload = function() {
                            dataTablesLoaded = true;
                            callback();
                        };
                        document.head.appendChild(dtRespScript);
                    };
                    document.head.appendChild(dtScript);
                } else {
                    setTimeout(function() {
                        loadDataTables(callback);
                    }, 50);
                }
            }

            function initOrdersDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initOrdersDataTable, 50);
                        return;
                    }

                    const $ = jQuery;

                    // Base URLs for actions (use URL instead of route helper)
                    const orderBaseUrl = '{{ url("/admin/orders") }}';

                    $(document).ready(function() {
                        // Loader modal for AJAX calls
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('orderDataTableLoader');
                                loadingModal = new bootstrap.Modal(modalEl, {
                                    backdrop: 'static',
                                    keyboard: false
                                });
                            }
                            loadingModal.show();
                        }
                        
                        function hideLoader() {
                            if (loadingModal) {
                                loadingModal.hide();
                                cleanupLoader();
                            }
                        }
                        
                        function cleanupLoader() {
                            $('#orderDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        // Track if this is the first draw (initial load)
                        let isFirstDraw = true;
                        
                        // Show loader on initial load
                        showLoader();
                        
                        let table = $('#ordersTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: orderBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    // Show loader on pagination/sorting changes (but not on first request)
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    // Add custom filters
                                    d.status = $('#statusFilter').val();
                                    d.country = $('#countryFilter').val();
                                    console.log('📤 DataTables request:', d);
                                },
                                dataSrc: function(json) {
                                    hideLoader();
                                    // Mark that first draw is complete
                                    isFirstDraw = false;
                                    console.log('📥 DataTables response:', json);
                                    if (json.error) {
                                        console.error('❌ Server error:', json.error);
                                        alert('Error: ' + json.error);
                                    }
                                    return json.data;
                                },
                                error: function(xhr, error, thrown) {
                                    hideLoader();
                                    console.error('❌ DataTables AJAX Error:', error);
                                    alert('Error loading data. Status: ' + xhr.status);
                                }
                            },
                            columns: [{
                                    data: 'orderid',
                                    name: 'orderid'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'email',
                                    name: 'email'
                                },
                                {
                                    data: 'total',
                                    name: 'total'
                                },
                                {
                                    data: 'status',
                                    name: 'status',
                                    orderable: false,
                                    render: function(data, type, row) {
                                        // Match CI project: status IDs 2, 4, 5, 6, 7 in order: Process, Delivered, Cancelled, Returned, Shipped
                                        const statusOptions = {
                                            '2': 'Process',
                                            '4': 'Delivered',
                                            '5': 'Cancelled',
                                            '6': 'Returned',
                                            '7': 'Shipped'
                                        };
                                        let html = '<select class="form-select form-select-sm order-status" data-order-id="' + row.action + '">';
                                        Object.keys(statusOptions).forEach(key => {
                                            html += '<option value="' + key + '"' + (data == key ? ' selected' : '') + '>' + statusOptions[key] + '</option>';
                                        });
                                        html += '</select>';
                                        return html;
                                    }
                                },
                                {
                                    data: 'country',
                                    name: 'country'
                                },
                                {
                                    data: 'orderdate',
                                    name: 'orderdate'
                                },
                                {
                                    data: 'shippingmethod',
                                    name: 'shippingmethod'
                                },
                                {
                                    data: 'paymentmethod',
                                    name: 'paymentmethod'
                                },
                                {
                                    data: 'ref',
                                    name: 'ref'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-order-btn" data-order-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="' + orderBaseUrl + '/' + row.action +
                                            '/edit" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">';
                                        html +=
                                            '<i class="ti ti-edit fs-lg"></i></a>';
                                        html += '</div>';
                                        return html;
                                    }
                                }
                            ],
                            pageLength: 25,
                            lengthMenu: [
                                [25, 50, 100],
                                [25, 50, 100]
                            ],
                            order: [
                                [6, 'desc']
                            ], // Default sort by Order Date desc
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No orders found",
                                zeroRecords: "No matching orders found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 2, 9] // Order#, Name, Email, Action - always visible
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [3, 4, 6] // Total, Status, Order Date
                                },
                                {
                                    responsivePriority: 3,
                                    targets: [5, 7, 8] // Area, Shipping, Payment
                                }
                            ]
                        });

                        // External Search & Filters
                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#statusFilter, #countryFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // Order Status Change Handler
                        $(document).on('change', '.order-status', function() {
                            const orderId = $(this).data('order-id');
                            const status = $(this).val();
                            const originalValue = $(this).data('original-value') || status;
                            
                            AdminAjax.post(orderBaseUrl + '/' + orderId + '/status', { status: status })
                                .then(response => {
                                    showToast('Order status updated successfully!', 'success');
                                    $(this).data('original-value', status);
                                })
                                .catch(error => {
                                    showToast('Failed to update order status.', 'error');
                                    $(this).val(originalValue);
                                });
                        });

                        // View Order Button
                        $(document).on('click', '.view-order-btn', function(e) {
                            e.preventDefault();
                            const orderId = $(this).data('order-id');
                            openOrderModal(orderId);
                        });

                        // Open Order Modal
                        function openOrderModal(orderId) {
                            const modalContainer = document.getElementById('orderViewModalContainer');
                            modalContainer.innerHTML = '<div class="modal fade" id="orderViewModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                            
                            const loadingModal = new bootstrap.Modal(document.getElementById('orderViewModal'));
                            loadingModal.show();

                            AdminAjax.get(orderBaseUrl + '/' + orderId)
                                .then(response => {
                                    loadingModal.hide();
                                    modalContainer.innerHTML = response.html;
                                    const modal = document.getElementById('orderViewModal');
                                    const bsModal = new bootstrap.Modal(modal);
                                    bsModal.show();
                                    
                                    modal.addEventListener('hidden.bs.modal', function() {
                                        modalContainer.innerHTML = '';
                                    }, { once: true });
                                })
                                .catch(error => {
                                    loadingModal.hide();
                                    showToast('Failed to load order details.', 'error');
                                    modalContainer.innerHTML = '';
                                });
                        }

                        // Show Toast
                        function showToast(message, type = 'error') {
                            let toastContainer = $('#global-toast-container');
                            if (!toastContainer.length) {
                                toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                                $('body').append(toastContainer);
                            }

                            toastContainer.find('.toast').each(function() {
                                const bsToast = bootstrap.Toast.getInstance(this);
                                if (bsToast) bsToast.hide();
                            });

                            const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                            const toastId = 'toast-' + Date.now();
                            const toast = $(`
                                <div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                            ${message}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                                    </div>
                                </div>
                            `);

                            toastContainer.append(toast);
                            const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 5000 });
                            bsToast.show();

                            toast.on('hidden.bs.toast', function() {
                                $(this).remove();
                                if (toastContainer.find('.toast').length === 0) {
                                    toastContainer.remove();
                                }
                            });
                        }

                        // Cleanup Loader
                        function cleanupLoader() {
                            $('#orderDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="orderDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Orders...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        }
                    });
                });
            }

            initOrdersDataTable();
        })();
    </script>
@endsection
