@extends('layouts.vertical', ['title' => 'Gift Products 4 List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Gift Products 4 List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.gift-products4') }}" data-table-filters id="giftProducts4FilterForm">
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

            <!-- Gift Products 4 Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Gift Products 4 List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-gift-product4-btn">
                        <i class="ti ti-plus me-1"></i> Add Gift Product 4
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
                        @include('admin.partials.gift-products4-table', ['products' => $products])
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
    <div id="giftProduct4ModalContainer"></div>
    <div id="giftProduct4ViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available
        (function() {
            function initGiftProduct4Script() {
                if (typeof jQuery === 'undefined' || typeof AdminAjax === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initGiftProduct4Script, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    loadTableFromURL();

                    /* ADD GIFT PRODUCT 4 BUTTON */
                    $(document).on('click', '.add-gift-product4-btn', function(e) {
                        e.preventDefault();
                        openGiftProduct4Modal(null);
                    });

                    /* VIEW GIFT PRODUCT 4 BUTTON */
                    $(document).on('click', '.view-gift-product4-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openGiftProduct4ViewModal(productId);
                    });

                    /* EDIT GIFT PRODUCT 4 BUTTON */
                    $(document).on('click', '.edit-gift-product4-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        openGiftProduct4Modal(productId);
                    });

                    /* DELETE GIFT PRODUCT 4 BUTTON */
                    $(document).on('click', '.delete-gift-product4-btn', function(e) {
                        e.preventDefault();
                        const productId = $(this).data('product-id');
                        const productName = $(this).data('product-name') || 'this gift product 4';
                        if (confirm(`Are you sure you want to remove ${productName} from gift products 4?`)) {
                            deleteGiftProduct4(productId);
                        }
                    });

                    /* OPEN GIFT PRODUCT 4 MODAL */
                    function openGiftProduct4Modal(productId) {
                        const url = productId
                            ? '{{ route('admin.gift-products4.edit', ':id') }}'.replace(':id', productId)
                            : '{{ route('admin.gift-products4.create') }}';

                        $('#giftProduct4ModalContainer').html('<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>');

                        AdminAjax.get(url).then(response => {
                            $('#giftProduct4ModalContainer').html(response.html);
                            const modalEl = document.getElementById('giftProduct4Modal');
                            if (modalEl) {
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                                // Setup validation after modal is shown
                                setupGiftProduct4Validation(productId, modal);
                            }
                        }).catch(err => {
                            console.error('Error loading gift product 4 form:', err);
                        });
                    }

                    /* VALIDATION SETUP */
                    function setupGiftProduct4Validation(productId, modal) {
                        const $form = $('#giftProduct4Form');
                        if (!$form.length || $form.data('validator')) {
                            return;
                        }

                        $form.validate({
                            rules: {
                                fkcategoryid: { required: true },
                                productcategoryid4: { required: true },
                                title: { required: true },
                                titleAR: { required: true },
                                productcode: { required: true },
                                shortdescr: { required: true },
                                shortdescrAR: { required: true },
                                price: { required: true, number: true, min: 0 },
                                sellingprice: { required: true, number: true, min: 0 }
                            },
                            messages: {
                                fkcategoryid: 'Category is required.',
                                productcategoryid4: 'Gift Product Category 4 is required.',
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
                                submitGiftProduct4Form(form, productId, modal);
                            }
                        });
                    }

                    /* SUBMIT FORM */
                    function submitGiftProduct4Form(form, productId, modal) {
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.setAttribute('data-original-text', originalText);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        console.log('Submitting Gift Product 4 form...', { url, method, productId });

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                console.log('Response received:', res);
                                if (res.success === true) {
                                    console.log('Success! Redirecting to list page...');
                                    showToast(res.message || 'Gift Product 4 saved successfully', 'success');
                                    modal.hide();
                                    // Redirect to list page after successful save
                                    setTimeout(() => {
                                        console.log('Redirecting now...');
                                        window.location.href = '{{ route('admin.gift-products4') }}';
                                    }, 500);
                                } else {
                                    console.log('Response success is false:', res);
                                    showToast(res.message || 'Failed to save Gift Product 4', 'error');
                                }
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to save Gift Product 4.';
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
                                showToast(errorMessage, 'error');
                                const $form = $('#giftProduct4Form');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                if (err.errors) {
                                    Object.keys(err.errors).forEach(field => {
                                        const input = $form.find(`[name="${field}"]`);
                                        input.addClass('is-invalid');
                                        const feedback = input.siblings('.invalid-feedback');
                                        if (feedback.length) {
                                            feedback.text(Array.isArray(err.errors[field]) ? err.errors[field][0] : err.errors[field]);
                                        }
                                    });
                                }
                            })
                            .finally(() => {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            });
                    }

                    /* OPEN GIFT PRODUCT 4 VIEW MODAL */
                    function openGiftProduct4ViewModal(productId) {
                        const url = '/admin/giftproducts4/' + productId;

                        $('#giftProduct4ViewModalContainer').html('<div class="text-center p-5"><div class="spinner-border" role="status"></div></div>');

                        AdminAjax.get(url).then(response => {
                            $('#giftProduct4ViewModalContainer').html(response.html);
                            const modalEl = document.getElementById('giftProduct4ViewModal');
                            if (modalEl) {
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                            }
                        }).catch(err => {
                            console.error('Error loading gift product 4 view:', err);
                        });
                    }

                    /* DELETE GIFT PRODUCT 4 */
                    function deleteGiftProduct4(productId) {
                        const url = '{{ route('admin.gift-products4.destroy', ':id') }}'.replace(':id', productId);
                        
                        AdminAjax.request(url, 'DELETE')
                            .then(res => {
                                showToast('Gift product 4 removed successfully', 'success');
                                loadTableFromURL();
                            })
                            .catch(err => {
                                console.error('Error deleting gift product 4:', err);
                                showToast(err.message || 'Failed to remove gift product 4.', 'error');
                            });
                    }

                    /* LOAD TABLE FROM URL */
                    function loadTableFromURL() {
                        const url = new URL(window.location.href);
                        const params = Object.fromEntries(url.searchParams);

                        AdminAjax.loadTable('{{ route('admin.gift-products4') }}', $('.table-container')[0], {
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
                        const form = $('#giftProducts4FilterForm');
                        const formData = new FormData(form[0]);
                        formData.set('per_page', this.value);
                        formData.delete('page');

                        const params = Object.fromEntries(formData);
                        const url = new URL('{{ route('admin.gift-products4') }}');
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                url.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', url);
                        loadTableFromURL();
                    });

                });

                /* TOAST FUNCTION */
                function showToast(message, type = 'error') {
                    let toastContainer = $('#global-toast-container');

                    if (!toastContainer.length) {
                        toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
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
            }
            initGiftProduct4Script();
        })();
    </script>
@endsection

