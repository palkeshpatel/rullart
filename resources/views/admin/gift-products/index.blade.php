@extends('layouts.vertical', ['title' => 'Gift Products List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Gift Products List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.gift-products') }}" data-table-filters id="giftProductsFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category:</label>
                                <select name="category" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Categories--</option>
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}" {{ request('category') == $cat->categoryid ? 'selected' : '' }}>{{ $cat->category }}</option>
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
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search product..." value="{{ request('search') }}">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                                            @php
                                                $currentPerPage = request('per_page', 25);
                                            @endphp
                                            <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.partials.gift-products-table', ['products' => $products])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $products])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="giftProductModalContainer"></div>
    <div id="giftProductViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available
        (function() {
            function initGiftProductScript() {
                if (typeof jQuery === 'undefined' || typeof AdminAjax === 'undefined') {
                    setTimeout(initGiftProductScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    loadTableFromURL();

                    /* ADD GIFT PRODUCT BUTTON */
                    $(document).on('click', '.add-gift-product-btn', function(e) {
                        e.preventDefault();
                        openGiftProductModal(null);
                    });

                    /* VIEW GIFT PRODUCT BUTTON */
                    $(document).on('click', '.view-gift-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openGiftProductViewModal(productId);
                    });

                    /* EDIT GIFT PRODUCT BUTTON */
                    $(document).on('click', '.edit-gift-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openGiftProductModal(productId);
                    });

                    /* DELETE GIFT PRODUCT BUTTON */
                    $(document).on('click', '.delete-gift-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        const productName = $(this).data('product-name') || 'this gift product';
                        if (confirm(`Are you sure you want to remove ${productName} from gift products?`)) {
                            deleteGiftProduct(productId);
                        }
                    });

                    /* OPEN GIFT PRODUCT MODAL */
                    function openGiftProductModal(productId) {
                        const url = productId
                            ? '{{ route('admin.gift-products.edit', ':id') }}'.replace(':id', productId)
                            : '{{ route('admin.gift-products.create') }}';

                        $('#giftProductModalContainer').html('<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>');

                        AdminAjax.get(url).then(response => {
                            $('#giftProductModalContainer').html(response.html);
                            const modalEl = document.getElementById('giftProductModal');
                            if (modalEl) {
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                            }
                        }).catch(err => {
                            console.error('Error loading gift product form:', err);
                        });
                    }

                    /* OPEN GIFT PRODUCT VIEW MODAL */
                    function openGiftProductViewModal(productId) {
                        const url = '/admin/giftproducts/' + productId;

                        $('#giftProductViewModalContainer').html('<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>');

                        AdminAjax.get(url).then(response => {
                            $('#giftProductViewModalContainer').html(response.html);
                            const modalEl = document.getElementById('giftProductViewModal');
                            if (modalEl) {
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                            }
                        }).catch(err => {
                            console.error('Error loading gift product view:', err);
                        });
                    }

                    /* DELETE GIFT PRODUCT */
                    function deleteGiftProduct(productId) {
                        const url = '{{ route('admin.gift-products.destroy', ':id') }}'.replace(':id', productId);
                        
                        AdminAjax.request(url, 'DELETE')
                            .then(res => {
                                showToast('Gift product removed successfully', 'success');
                                loadTableFromURL();
                            })
                            .catch(err => {
                                console.error('Error deleting gift product:', err);
                                showToast(err.message || 'Failed to remove gift product.', 'error');
                            });
                    }

                    /* LOAD TABLE FROM URL */
                    function loadTableFromURL() {
                        const url = new URL(window.location.href);
                        const params = Object.fromEntries(url.searchParams);

                        AdminAjax.loadTable('{{ route('admin.gift-products') }}', $('.table-container')[0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                            }
                        });
                    }

                    /* SEARCH HANDLER */
                    $(document).on('keyup', '[data-search]', function() {
                        clearTimeout(window.searchTimeout);
                        window.searchTimeout = setTimeout(function() {
                            loadTableFromURL();
                        }, 500);
                    });

                    /* FILTER HANDLER */
                    $(document).on('change', '[data-filter]', function() {
                        loadTableFromURL();
                    });

                    /* PAGINATION HANDLER */
                    $(document).on('click', '.pagination a', function(e) {
                        e.preventDefault();
                        const url = $(this).attr('href');
                        if (url) {
                            window.history.pushState({}, '', url);
                            loadTableFromURL();
                        }
                    });

                    /* PER PAGE CHANGE */
                    $('#perPageSelect').on('change', function() {
                        const form = $('#giftProductsFilterForm');
                        const formData = new FormData(form[0]);
                        formData.set('per_page', this.value);
                        formData.delete('page');

                        const params = Object.fromEntries(formData);
                        const url = new URL('{{ route('admin.gift-products') }}');
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                url.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', url);
                        loadTableFromURL();
                    });
                });
            }
            initGiftProductScript();
        })();
    </script>
@endsection

