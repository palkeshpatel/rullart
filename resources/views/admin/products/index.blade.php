@extends('layouts.vertical', ['title' => 'Products List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Products List'])

    <div class="row">
        <div class="col-12">
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
                                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100
                                            </option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.partials.products-table', ['products' => $products])
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
    <div id="productModalContainer"></div>
    <div id="productViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available
        (function() {
            function initProductScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initProductScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    loadTableFromURL();

                    /* ADD PRODUCT BUTTON */
                    $(document).on('click', '.add-product-btn', function(e) {
                        e.preventDefault();
                        openProductModal(null);
                    });

                    /* VIEW PRODUCT BUTTON */
                    $(document).on('click', '.view-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openProductViewModal(productId);
                    });

                    /* EDIT PRODUCT BUTTON */
                    $(document).on('click', '.edit-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openProductModal(productId);
                    });

                    /* DELETE PRODUCT BUTTON */
                    $(document).on('click', '.delete-product-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        const productName = $(this).data('product-name') || 'this product';
                        if (confirm(`Are you sure you want to delete ${productName}?`)) {
                            deleteProduct(productId);
                        }
                    });

                    /* OPEN PRODUCT MODAL */
                    function openProductModal(productId) {
                        cleanupModals();
                        const url = productId ?
                            '{{ route('admin.products.edit', ':id') }}'.replace(':id', productId) :
                            '{{ route('admin.products.create') }}';

                        $('#productModalContainer').html(loaderHtml());
                        const loadingModal = new bootstrap.Modal($('#productModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#productModalContainer').html(response.html);

                            const modalEl = document.getElementById('productModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            setupProductValidation(productId, modal);

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* OPEN PRODUCT VIEW MODAL */
                    function openProductViewModal(productId) {
                        cleanupModals();
                        const url = '{{ route('admin.products.show', ':id') }}'.replace(':id', productId);

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

                    /* DELETE PRODUCT */
                    function deleteProduct(productId) {
                        const url = '{{ route('admin.products.destroy', ':id') }}'.replace(':id', productId);
                        AdminAjax.request(url, 'DELETE')
                            .then(res => {
                                showToast('Product deleted successfully', 'success');
                                reloadProductTable();
                            })
                            .catch(err => {
                                showToast(err.message || 'Failed to delete product.', 'error');
                            });
                    }

                    /* VALIDATION SETUP */
                    function setupProductValidation(productId, modal) {
                        const $form = $('#productForm');
                        if (!$form.length || $form.data('validator')) {
                            return;
                        }

                        $form.validate({
                            rules: {
                                fkcategoryid: {
                                    required: true
                                },
                                title: {
                                    required: true
                                },
                                titleAR: {
                                    required: true
                                },
                                productcode: {
                                    required: true
                                },
                                shortdescr: {
                                    required: true
                                },
                                shortdescrAR: {
                                    required: true
                                },
                                price: {
                                    required: true,
                                    number: true,
                                    min: 0
                                },
                                sellingprice: {
                                    required: true,
                                    number: true,
                                    min: 0
                                }
                            },
                            messages: {
                                fkcategoryid: 'Category is required.',
                                title: 'Product title (EN) is required.',
                                titleAR: 'Product title (AR) is required.',
                                productcode: 'Product code is required.',
                                shortdescr: 'Short description (EN) is required.',
                                shortdescrAR: 'Short description (AR) is required.',
                                price: 'Price is required and must be a valid number.',
                                sellingprice: 'Selling price is required and must be a valid number.'
                            },
                            errorElement: 'div',
                            errorClass: 'invalid-feedback',
                            highlight(el) {
                                $(el).addClass('is-invalid');
                            },
                            unhighlight(el) {
                                $(el).removeClass('is-invalid').addClass('is-valid');
                            },
                            errorPlacement(error, element) {
                                error.insertAfter(element);
                            },
                            submitHandler(form) {
                                submitProductForm(form, productId, modal);
                            }
                        });
                    }

                    /* SUBMIT FORM */
                    function submitProductForm(form, productId, modal) {
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.setAttribute('data-original-text', originalText);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                showToastInModal(modal, res.message || 'Product saved successfully',
                                    'success');
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);
                                reloadProductTable();
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to save product.';
                                if (err.message) {
                                    errorMessage = err.message;
                                } else if (err.errors) {
                                    const firstError = Object.values(err.errors)[0];
                                    if (Array.isArray(firstError)) {
                                        errorMessage = firstError[0];
                                    } else {
                                        errorMessage = firstError;
                                    }
                                }
                                showToastInModal(modal, errorMessage, 'error');
                                const $form = $('#productForm');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') ||
                                    originalText;
                            });
                    }

                    /* RELOAD PRODUCT TABLE */
                    function reloadProductTable() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentPage = urlParams.get('page') || 1;
                        const currentPerPage = urlParams.get('per_page') || $('#perPageSelect').val() || 25;
                        const currentSearch = urlParams.get('search') || $('[data-search]').val() || '';
                        const currentCategory = urlParams.get('category') || $('[name="category"]').val() || '';
                        const currentPublished = urlParams.get('published') || $('[name="published"]').val() ||
                            '';

                        const params = {
                            page: currentPage,
                            per_page: currentPerPage
                        };
                        if (currentSearch) params.search = currentSearch;
                        if (currentCategory) params.category = currentCategory;
                        if (currentPublished) params.published = currentPublished;

                        AdminAjax.loadTable('{{ route('admin.products') }}', $('.table-container')[0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                                bindPaginationHandlers();
                            }
                        });
                    }

                    /* LOAD TABLE FROM URL */
                    function loadTableFromURL() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const page = urlParams.get('page');
                        const perPage = urlParams.get('per_page');
                        const search = urlParams.get('search');
                        const category = urlParams.get('category');
                        const published = urlParams.get('published');

                        if (page || perPage || search || category || published) {
                            const params = {};
                            if (page) params.page = page;
                            if (perPage) params.per_page = perPage;
                            if (search) params.search = search;
                            if (category) params.category = category;
                            if (published) params.published = published;

                            if (perPage && $('#perPageSelect').length) {
                                $('#perPageSelect').val(perPage);
                            }
                            if (search && $('[data-search]').length) {
                                $('[data-search]').val(search);
                            }
                            if (category && $('[name="category"]').length) {
                                $('[name="category"]').val(category);
                            }
                            if (published && $('[name="published"]').length) {
                                $('[name="published"]').val(published);
                            }

                            AdminAjax.loadTable('{{ route('admin.products') }}', $('.table-container')[0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response.pagination);
                                    }
                                    bindPaginationHandlers();
                                }
                            });
                        } else {
                            bindPaginationHandlers();
                        }
                    }

                    /* BIND PAGINATION HANDLERS */
                    function bindPaginationHandlers() {
                        $(document).off('click', '.pagination a');
                        $(document).on('click', '.pagination a', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            const url = $(this).attr('href');
                            if (!url || url === '#' || url === 'javascript:void(0)') {
                                return;
                            }

                            const urlObj = new URL(url, window.location.origin);
                            const params = {
                                page: urlObj.searchParams.get('page') || 1,
                                per_page: urlObj.searchParams.get('per_page') || $('#perPageSelect')
                                    .val() || 25,
                                search: urlObj.searchParams.get('search') || $('[data-search]')
                                    .val() || '',
                                category: urlObj.searchParams.get('category') || $(
                                    '[name="category"]').val() || '',
                                published: urlObj.searchParams.get('published') || $(
                                    '[name="published"]').val() || ''
                            };

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.products') }}', $('.table-container')[
                                0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response
                                            .pagination);
                                    }
                                }
                            });
                        });
                    }

                    /* PER PAGE SELECT HANDLER */
                    $(document).on('change', '#perPageSelect', function(e) {
                        e.preventDefault();
                        const perPage = $(this).val();
                        const currentSearch = $('[data-search]').val() || '';
                        const currentCategory = $('[name="category"]').val() || '';
                        const currentPublished = $('[name="published"]').val() || '';

                        const params = {
                            page: 1,
                            per_page: perPage
                        };
                        if (currentSearch) params.search = currentSearch;
                        if (currentCategory) params.category = currentCategory;
                        if (currentPublished) params.published = currentPublished;

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.products') }}', $('.table-container')[
                            0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                            }
                        });
                    });

                    /* SEARCH HANDLER */
                    let searchTimeout;
                    $(document).on('input', '[data-search]', function(e) {
                        clearTimeout(searchTimeout);
                        const searchInput = $(this);
                        searchTimeout = setTimeout(function() {
                            const searchValue = searchInput.val();
                            const currentPerPage = $('#perPageSelect').val() || 25;
                            const currentCategory = $('[name="category"]').val() || '';
                            const currentPublished = $('[name="published"]').val() || '';

                            const params = {
                                page: 1,
                                per_page: currentPerPage
                            };
                            if (searchValue) params.search = searchValue;
                            if (currentCategory) params.category = currentCategory;
                            if (currentPublished) params.published = currentPublished;

                            const newUrl = new URL(window.location.pathname, window.location
                                .origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.products') }}', $(
                                '.table-container')[0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response
                                            .pagination);
                                    }
                                }
                            });
                        }, 500);
                    });

                    /* FILTER HANDLERS */
                    $(document).on('change', '[data-filter]', function(e) {
                        const category = $('[name="category"]').val() || '';
                        const published = $('[name="published"]').val() || '';
                        const currentSearch = $('[data-search]').val() || '';
                        const currentPerPage = $('#perPageSelect').val() || 25;

                        const params = {
                            page: 1,
                            per_page: currentPerPage
                        };
                        if (currentSearch) params.search = currentSearch;
                        if (category) params.category = category;
                        if (published) params.published = published;

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.products') }}', $('.table-container')[
                            0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                            }
                        });
                    });

                    /* TOAST FUNCTIONS */
                    function showToastInModal(modal, message, type = 'error') {
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

                    function showToast(message, type = 'error') {
                        showToastInModal(null, message, type);
                    }

                    /* HELPERS */
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
        <div class="modal fade" id="productModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="spinner-border"></div>
                    </div>
                </div>
            </div>
        </div>`;
                    }

                    bindPaginationHandlers();
                });
            }

            initProductScript();
        })();
    </script>
@endsection
