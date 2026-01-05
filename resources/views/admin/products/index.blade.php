@extends('layouts.vertical', ['title' => 'Products List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Products List'])

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
                                @foreach ($categories ?? [] as $cat)
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

            <!-- Products Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Products List</h4>
                    @unless (\App\Helpers\ViewHelper::isView('products'))
                        <a href="{{ route('admin.products.create') }}" class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i> Add Product
                        </a>
                    @endunless
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
                        <table id="productsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Product Code</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Reg Price</th>
                                    <th>Disc(%)</th>
                                    <th>Sell Price</th>
                                    <th>Photo</th>
                                    <th>Quantity</th>
                                    <th>Active</th>
                                    <th>Up.Date</th>
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
    <div id="productModalContainer"></div>
    <div id="productViewModalContainer"></div>

    {{-- ============================================ --}}
    {{-- OLD IMPLEMENTATION (COMMENTED FOR REFERENCE) --}}
    {{-- ============================================ --}}
    {{--
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.products') }}" data-table-filters id="productsFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category:</label>
                                <select name="category" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Categories--</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ request('category') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Published:</label>
                                <select name="published" class="form-select form-select-sm" data-filter>
                                    <option value="">--All--</option>
                                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search product..." value="{{ request('search') }}">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
        @include('admin.partials.products-table', ['products' => $products ?? []])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
        @include('admin.partials.pagination', ['items' => $products ?? []])
    </div>
    --}}
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

            function initProductsDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initProductsDataTable, 50);
                        return;
                    }

                    const $ = jQuery;

                    // Base URLs for actions (global scope)
                    const productsBaseUrl = '{{ url('/admin/products') }}';

                    $(document).ready(function() {
                        // ============================================
                        // NEW DATATABLES IMPLEMENTATION
                        // ============================================

                        // Loader modal for AJAX calls
                        let loadingModal = null;

                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('productDataTableLoader');
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
                            $('#productDataTableLoader').remove();
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

                        let table = $('#productsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: '{{ route('admin.products') }}',
                                type: 'GET',
                                data: function(d) {
                                    // Show loader on pagination/sorting changes (but not on first request)
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    // Add custom filters
                                    d.category = $('#categoryFilter').val();
                                    d.published = $('#publishedFilter').val();
                                    console.log('📤 DataTables request:', d);
                                },
                                dataSrc: function(json) {
                                    hideLoader();
                                    // Mark that first draw is complete
                                    isFirstDraw = false;
                                    console.log('📥 DataTables response:', json);
                                    console.log('📊 Records Total:', json.recordsTotal);
                                    console.log('📊 Records Filtered:', json
                                        .recordsFiltered);
                                    console.log('📊 Data count:', json.data ? json.data
                                        .length : 0);
                                    if (json.error) {
                                        console.error('❌ Server error:', json.error);
                                        alert('Error: ' + json.error);
                                    }
                                    return json.data;
                                },
                                error: function(xhr, error, thrown) {
                                    hideLoader();
                                    console.error('❌ DataTables AJAX Error:', error);
                                    console.error('❌ Status:', xhr.status);
                                    console.error('❌ Response:', xhr.responseText);
                                    alert('Error loading data. Status: ' + xhr.status +
                                        '. Check console for details.');
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
                                    data: 'category',
                                    name: 'category',
                                    orderable: false
                                },
                                {
                                    data: 'price',
                                    name: 'price'
                                },
                                {
                                    data: 'discount',
                                    name: 'discount'
                                },
                                {
                                    data: 'sellingprice',
                                    name: 'sellingprice'
                                },
                                {
                                    data: 'photo',
                                    name: 'photo',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data) {
                                        if (data) {
                                            return '<img src="' + data +
                                                '" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">';
                                        }
                                        return '<span class="text-muted">-</span>';
                                    }
                                },
                                {
                                    data: 'quantity',
                                    name: 'quantity',
                                    orderable: false
                                },
                                {
                                    data: 'ispublished',
                                    name: 'ispublished'
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
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-product-btn" data-product-id="' +
                                            row.productid + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        @unless (\App\Helpers\ViewHelper::isView('products'))
                                            html += '<a href="' + productsBaseUrl +
                                                '/' + row.productid +
                                                '/edit" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">';
                                            html +=
                                                '<i class="ti ti-edit fs-lg"></i></a>';
                                            html +=
                                                '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-product-btn" data-product-id="' +
                                                row.productid +
                                                '" data-product-name="' + row.title +
                                                '" title="Delete">';
                                            html +=
                                                '<i class="ti ti-trash fs-lg"></i></a>';
                                        @endunless
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
                            ], // Default sort by productid desc
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No products found",
                                zeroRecords: "No matching products found"
                            },
                        });

                        // 🔍 External Search
                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        // 🔽 External Filters
                        $('#categoryFilter, #publishedFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // ❌ Delete Product
                        $(document).on('click', '.delete-product-btn', function(e) {
                            e.preventDefault();
                            const productId = $(this).data('product-id');
                            const productName = $(this).data('product-name') || 'this product';

                            // Remove existing delete modal if any
                            $('#deleteProductModal').remove();
                            $('.modal-backdrop').remove();

                            const modalHtml = `
                                <div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header border-0 pb-0">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-center px-4 pb-4">
                                                <div class="mb-3">
                                                    <i class="ti ti-alert-triangle text-danger" style="font-size: 48px;"></i>
                                                </div>
                                                <h5 class="modal-title mb-3" id="deleteProductModalLabel">Confirm Delete</h5>
                                                <p class="text-muted mb-2">Are you sure you want to delete <strong>"${productName}"</strong>?</p>
                                                <p class="text-danger mb-0 small">This action cannot be undone.</p>
                                            </div>
                                            <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="button" class="btn btn-danger" id="confirmDeleteProductBtn">Delete</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            $('body').append(modalHtml);

                            const modalEl = document.getElementById('deleteProductModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle confirm button click
                            $('#confirmDeleteProductBtn').off('click').on('click', function() {
                                modal.hide();

                                // Get current page info before deletion
                                const currentPageInfo = table.page.info();
                                const currentPage = currentPageInfo.page;

                                // Show loader
                                showLoader();

                                const url = productsBaseUrl + '/' + productId;

                                AdminAjax.request(url, 'DELETE')
                                    .then(res => {
                                        showToast('Product deleted successfully',
                                            'success');

                                        // Save current page before reload
                                        const savedPage = currentPage;

                                        // One-time event listener to restore page after reload
                                        $('#productsTable').one('draw.dt',
                                            function() {
                                                const newPageInfo = table.page
                                                    .info();
                                                const newTotalPages =
                                                    newPageInfo.pages;

                                                // If saved page still exists, go to it
                                                if (savedPage < newTotalPages) {
                                                    table.page(savedPage).draw(
                                                        'page');
                                                } else if (newTotalPages > 0) {
                                                    // If saved page doesn't exist anymore, go to last page
                                                    table.page(newTotalPages -
                                                        1).draw('page');
                                                }
                                                // If newTotalPages is 0, we're already on page 0, no need to change
                                            });

                                        // Reload table (this will trigger the draw event)
                                        table.ajax.reload(null,
                                            false
                                            ); // false = don't reset pagination
                                    })
                                    .catch(err => {
                                        hideLoader();
                                        showToast(err.message ||
                                            'Failed to delete product.', 'error'
                                        );
                                    });

                                // Clean up modal
                                setTimeout(() => {
                                    $('#deleteProductModal').remove();
                                    $('.modal-backdrop').remove();
                                    $('body').removeClass('modal-open').css({
                                        overflow: '',
                                        paddingRight: ''
                                    });
                                }, 300);
                            });

                            // Clean up when modal is hidden
                            modalEl.addEventListener('hidden.bs.modal', function() {
                                $('#deleteProductModal').remove();
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open').css({
                                    overflow: '',
                                    paddingRight: ''
                                });
                            }, {
                                once: true
                            });
                        });

                        // 👁️ View Product
                        $(document).on('click', '.view-product-btn', function(e) {
                            e.preventDefault();
                            const productId = $(this).data('product-id');
                            openProductViewModal(productId);
                        });

                        // 📝 Edit Product (handled by link)
                        // Edit button is a regular link, no need for handler

                        // ============================================
                        // OLD IMPLEMENTATION (COMMENTED FOR REFERENCE)
                        // ============================================
                        /*
                    function initProductScript() {
                        if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                            setTimeout(initProductScript, 50);
                            return;
                        }

                        const $ = jQuery;

                        $(document).ready(function() {
                            loadTableFromURL();

                                        // ... all old code ...
                                    });
                                }

                                initProductScript();
                                */
                    });

                    // Helper functions (keep for modals and toasts)
                    function openProductViewModal(productId) {
                        cleanupModals();
                        const url = productsBaseUrl + '/' + productId;

                        $('#productViewModalContainer').html(loaderHtml());
                        const loadingModal = new bootstrap.Modal($('#productModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#productViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('productViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
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
                            if (bsToast) {
                                bsToast.hide();
                            }
                        });

                        const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                        const toastId = 'toast-' + Date.now();
                        const toast = $(
                            `<div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                        ${message}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>`
                        );

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

                    function cleanupModals() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({
                            overflow: '',
                            paddingRight: ''
                        });
                        $('#productModal').remove();
                        $('#productViewModal').remove();
                    }

                    function loaderHtml() {
                        return `
                        <div class="modal fade" id="productDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <h5 class="mt-3 mb-1">Loading Products...</h5>
                                        <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                    </div>
                </div>
            </div>
        </div>`;
                    }
                }); // End of loadDataTables callback
            } // End of initProductsDataTable function

            initProductsDataTable();
        })();
    </script>
@endsection
