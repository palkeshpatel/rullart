@extends('layouts.vertical', ['title' => 'Gift Products List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Gift Products List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Category:</label>
                            <select id="categoryFilter" class="form-select form-select-sm">
                                <option value="">--All Categories--</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->categoryid }}">{{ $cat->category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Published:</label>
                            <select id="publishedFilter" class="form-select form-select-sm">
                                <option value="">--All--</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gift Products Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Gift Products List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-gift-product-btn">
                        <i class="ti ti-plus me-1"></i> Add Gift Product
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search product...">
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
                        <table id="giftProductsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Product Code</th>
                                    <th>Title</th>
                                    <th>Selling Price</th>
                                    <th>Category</th>
                                    <th>Photo</th>
                                    <th>Quantity</th>
                                    <th>Active</th>
                                    <th>Updated Date</th>
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

    <!-- Modal Container -->
    <div id="giftProductModalContainer"></div>
    <div id="giftProductViewModalContainer"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteGiftProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove <strong id="deleteGiftProductName"></strong> from gift products?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteGiftProductBtn">Delete</button>
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
                        dtRespScript.src = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
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

            function initGiftProductDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initGiftProductDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const giftProductBaseUrl = '{{ url("/admin/giftproducts") }}';
                    let deleteProductId = null;

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('giftProductDataTableLoader');
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
                            $('#giftProductDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#giftProductsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: giftProductBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    d.category = $('#categoryFilter').val();
                                    d.published = $('#publishedFilter').val();
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
                                    data: 'productcode',
                                    name: 'productcode'
                                },
                                {
                                    data: 'title',
                                    name: 'title'
                                },
                                {
                                    data: 'sellingprice',
                                    name: 'sellingprice'
                                },
                                {
                                    data: 'category',
                                    name: 'category'
                                },
                                {
                                    data: 'photo',
                                    name: 'photo',
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    data: 'quantity',
                                    name: 'quantity',
                                    orderable: false
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive'
                                },
                                {
                                    data: 'updateddate',
                                    name: 'updateddate'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-gift-product-btn" data-product-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-gift-product-btn" data-product-id="' +
                                            row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-gift-product-btn" data-product-id="' +
                                            row.action + '" data-product-name="' + (row.title || 'this gift product') +
                                            '" title="Delete">';
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
                                [0, 'desc']
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No gift products found",
                                zeroRecords: "No matching gift products found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 8]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3, 4]
                                },
                                {
                                    responsivePriority: 3,
                                    targets: [5, 6, 7]
                                }
                            ]
                        });

                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#categoryFilter, #publishedFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // View/Edit/Add/Delete handlers
                        $(document).on('click', '.view-gift-product-btn', function(e) {
                            e.preventDefault();
                            const productId = $(this).data('product-id');
                            openGiftProductViewModal(productId);
                        });

                        $(document).on('click', '.edit-gift-product-btn', function(e) {
                            e.preventDefault();
                            const productId = $(this).data('product-id');
                            openGiftProductFormModal(productId);
                        });

                        $(document).on('click', '.add-gift-product-btn', function(e) {
                            e.preventDefault();
                            openGiftProductFormModal();
                        });

                        $(document).on('click', '.delete-gift-product-btn', function(e) {
                            e.preventDefault();
                            deleteProductId = $(this).data('product-id');
                            const productName = $(this).data('product-name') || 'this gift product';
                            $('#deleteGiftProductName').text(productName);
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteGiftProductModal'));
                            deleteModal.show();
                        });

                        $('#confirmDeleteGiftProductBtn').on('click', function() {
                            if (deleteProductId) {
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;
                                
                                AdminAjax.request(giftProductBaseUrl + '/' + deleteProductId, 'DELETE')
                                    .then(res => {
                                        bootstrap.Modal.getInstance(document.getElementById('deleteGiftProductModal')).hide();
                                        showToast('Gift product removed successfully', 'success');
                                        
                                        showLoader();
                                        table.ajax.reload(function() {
                                            hideLoader();
                                            const newTotalPages = table.page.info().pages;
                                            if (currentPage >= newTotalPages && newTotalPages > 0) {
                                                table.page(newTotalPages - 1).draw('page');
                                            } else {
                                                table.page(currentPage).draw('page');
                                            }
                                        }, false);
                                    })
                                    .catch(err => {
                                        showToast(err.message || 'Failed to remove gift product.', 'error');
                                    });
                            }
                        });

                        // Modal functions (similar to Category)
                        function openGiftProductViewModal(productId) {
                            cleanupModals();
                            const url = giftProductBaseUrl + '/' + productId;
                            $('#giftProductViewModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#giftProductModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#giftProductViewModalContainer').html(response.html);
                                const modalEl = document.getElementById('giftProductViewModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();

                                modalEl.addEventListener('hidden.bs.modal', function() {
                                    cleanupModals();
                                }, { once: true });
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                                AdminAjax.showError('Failed to load gift product details.');
                            });
                        }

                        function openGiftProductFormModal(productId = null) {
                            cleanupModals();
                            const url = productId ? giftProductBaseUrl + '/' + productId + '/edit' : giftProductBaseUrl + '/create';
                            $('#giftProductModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#giftProductModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#giftProductModalContainer').html(response.html);
                                const modalEl = document.getElementById('giftProductModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                                // Setup validation if needed
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                            });
                        }

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

                        function cleanupModals() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            $('#giftProductModal').remove();
                            $('#giftProductViewModal').remove();
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="giftProductDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Gift Products...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="giftProductModal">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        }
                    });
                });
            }

            initGiftProductDataTable();
        })();
    </script>
@endsection
