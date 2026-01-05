@extends('layouts.vertical', ['title' => 'Shopping Cart Not Complete Payment'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Shopping Cart not complete payment'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
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

            <!-- Carts Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Shopping Cart Not Complete Payment</h4>
                    <a href="{{ url('/admin/ordersnotprocess/export') }}" class="btn btn-success btn-sm"
                        title="Export to Excel" id="exportBtn">
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
                                        placeholder="Search cart...">
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
                        <table id="cartsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Ref #</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Total</th>
                                    <th>Order Date</th>
                                    <th>Payment Method</th>
                                    <th>Order From</th>
                                    <th>Email Count</th>
                                    <th>Email Send Date</th>
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
@endsection

@section('scripts')
    <script>
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

            function initCartsDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initCartsDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const cartBaseUrl = '{{ url('/admin/ordersnotprocess') }}';
                    let deleteCartId = null;

                    $(document).ready(function() {
                        let loadingModal = null;

                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('cartDataTableLoader');
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
                            $('#cartDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();

                        let table = $('#cartsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: cartBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    d.country = $('#countryFilter').val();
                                    console.log('📤 DataTables request:', d);
                                },
                                dataSrc: function(json) {
                                    hideLoader();
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
                                    data: 'ref',
                                    name: 'ref'
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
                                    data: 'orderdate',
                                    name: 'orderdate'
                                },
                                {
                                    data: 'paymentmethod',
                                    name: 'paymentmethod'
                                },
                                {
                                    data: 'orderfrom',
                                    name: 'orderfrom'
                                },
                                {
                                    data: 'emailcount',
                                    name: 'emailcount'
                                },
                                {
                                    data: 'emailsenddate',
                                    name: 'emailsenddate'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-cart-btn" data-cart-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-cart-btn" data-cart-id="' +
                                            row.action + '" title="Delete">';
                                        html += '<i class="ti ti-trash fs-lg"></i></a>';
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
                                [4, 'desc']
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No incomplete shopping carts found",
                                zeroRecords: "No matching carts found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 2, 9]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [3, 4]
                                },
                                {
                                    responsivePriority: 3,
                                    targets: [5, 6, 7, 8]
                                }
                            ]
                        });

                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#countryFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // View Cart Button
                        $(document).on('click', '.view-cart-btn', function(e) {
                            e.preventDefault();
                            const cartId = $(this).data('cart-id');
                            openCartModal(cartId);
                        });

                        // Delete Cart Button
                        $(document).on('click', '.delete-cart-btn', function(e) {
                            e.preventDefault();
                            deleteCartId = $(this).data('cart-id');
                            $('#deleteCartName').text('Cart #' + deleteCartId);
                            const deleteModal = new bootstrap.Modal(document.getElementById(
                                'deleteCartModal'));
                            deleteModal.show();
                        });

                        // Confirm Delete
                        $('#confirmDeleteCartBtn').on('click', function() {
                            if (deleteCartId) {
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;

                                AdminAjax.request(cartBaseUrl + '/' + deleteCartId, 'DELETE')
                                    .then(res => {
                                        bootstrap.Modal.getInstance(document.getElementById(
                                            'deleteCartModal')).hide();
                                        showToast('Cart deleted successfully', 'success');

                                        showLoader();
                                        table.ajax.reload(function() {
                                            hideLoader();
                                            const newTotalPages = table.page.info()
                                                .pages;
                                            if (currentPage >= newTotalPages &&
                                                newTotalPages > 0) {
                                                table.page(newTotalPages - 1).draw(
                                                    'page');
                                            } else {
                                                table.page(currentPage).draw(
                                                    'page');
                                            }
                                        }, false);
                                    })
                                    .catch(err => {
                                        showToast(err.message || 'Failed to delete cart.',
                                            'error');
                                    });
                            }
                        });

                        // Open Cart Modal
                        function openCartModal(cartId) {
                            const modalContainer = document.getElementById('cartViewModalContainer');
                            modalContainer.innerHTML =
                                '<div class="modal fade" id="cartViewModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';

                            const loadingModal = new bootstrap.Modal(document.getElementById(
                                'cartViewModal'));
                            loadingModal.show();

                            AdminAjax.get(cartBaseUrl + '/' + cartId)
                                .then(response => {
                                    loadingModal.hide();
                                    modalContainer.innerHTML = response.html;
                                    const modal = document.getElementById('cartViewModal');
                                    const bsModal = new bootstrap.Modal(modal);
                                    bsModal.show();

                                    modal.addEventListener('hidden.bs.modal', function() {
                                        modalContainer.innerHTML = '';
                                    }, {
                                        once: true
                                    });
                                })
                                .catch(error => {
                                    loadingModal.hide();
                                    showToast('Failed to load cart details.', 'error');
                                    modalContainer.innerHTML = '';
                                });
                        }

                        function showToast(message, type = 'error') {
                            let toastContainer = $('#global-toast-container');
                            if (!toastContainer.length) {
                                toastContainer = $(
                                    '<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>'
                                );
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
                            const bsToast = new bootstrap.Toast(toast[0], {
                                autohide: true,
                                delay: 5000
                            });
                            bsToast.show();

                            toast.on('hidden.bs.toast', function() {
                                $(this).remove();
                                if (toastContainer.find('.toast').length === 0) {
                                    toastContainer.remove();
                                }
                            });
                        }

                        function cleanupLoader() {
                            $('#cartDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="cartDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Carts...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        }
                    });
                });
            }

            initCartsDataTable();
        })();
    </script>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCartModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteCartName"></strong>?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCartBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cart View Modal Container -->
    <div id="cartViewModalContainer"></div>
@endsection
