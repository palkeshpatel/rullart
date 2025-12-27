@extends('layouts.vertical', ['title' => 'Discount Offers List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Discount Offers List'])

    <div class="row">
        <div class="col-12">
            <!-- Discounts Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Discount Offers List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-discount-btn">
                        <i class="ti ti-plus me-1"></i> Add Discount
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search discounts..." value="{{ request('search') }}">
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
                        @include('admin.masters.partials.discounts.discounts-table', ['discounts' => $discounts])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $discounts])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="discountModalContainer"></div>
    <div id="discountViewModalContainer"></div>
@endsection


@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initDiscountsScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initDiscountsScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {

                    console.log('✅ Document ready for Discounts');

                    // Load table from URL parameters on page load
                    loadTableFromURL();

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#discountForm');
                    $(document).on('submit', '#discountForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD DISCOUNT BUTTON (OPEN MODAL ONLY)
                    ----------------------------------- */
                    $(document).on('click', '.add-discount-btn', function(e) {
                        e.preventDefault();
                        console.log('➕ Add Discount clicked (open modal)');
                        openDiscountFormModal();
                    });

                    /* -----------------------------------
                     EDIT DISCOUNT BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-discount-btn', function(e) {
                        e.preventDefault();
                        const discountId = $(this).data('discount-id');
                        console.log('✏️ Edit Discount clicked, ID:', discountId);
                        openDiscountFormModal(discountId);
                    });

                    /* -----------------------------------
                     VIEW DISCOUNT BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-discount-btn', function(e) {
                        e.preventDefault();
                        const discountId = $(this).data('discount-id');
                        console.log('👁️ View Discount clicked, ID:', discountId);
                        openDiscountViewModal(discountId);
                    });

                    /* -----------------------------------
                     DELETE DISCOUNT BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.delete-discount-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const discountId = $(this).data('discount-id');
                        const discountRate = $(this).data('discount-rate') || 'this discount';
                        console.log('🗑️ Delete Discount clicked, ID:', discountId);
                        confirmDeleteDiscount(discountId, discountRate);
                    });

                    /* -----------------------------------
                     OPEN VIEW MODAL
                    ----------------------------------- */
                    function openDiscountViewModal(discountId) {
                        console.log('📦 Opening discount view modal, ID:', discountId);

                        cleanupModals();

                        const url = '{{ route('admin.discounts.show', ':id') }}'.replace(':id', discountId);

                        $('#discountViewModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#discountModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            console.log('📥 View HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#discountViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('discountViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle edit button click from view modal
                            $(modalEl).find('.edit-discount-btn').on('click', function(e) {
                                e.preventDefault();
                                const editDiscountId = $(this).data('discount-id');
                                modal.hide();
                                cleanupModals();
                                // Open edit modal
                                setTimeout(() => {
                                    openDiscountFormModal(editDiscountId);
                                }, 300);
                            });

                            // Cleanup on close
                            modalEl.addEventListener('hidden.bs.modal', function() {
                                cleanupModals();
                            }, {
                                once: true
                            });

                        }).catch(err => {
                            console.error('❌ Failed to load view', err);
                            loadingModal.hide();
                            cleanupModals();
                            showToastInModal(null, 'Failed to load discount details.', 'error');
                        });
                    }

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openDiscountFormModal(discountId = null) {

                        console.log('📦 Opening discount form modal, ID:', discountId);

                        cleanupModals();

                        const url = discountId ?
                            '{{ route('admin.discounts.edit', ':id') }}'.replace(':id', discountId) :
                            '{{ route('admin.discounts.create') }}';

                        $('#discountModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#discountModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {

                            console.log('📥 Form HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#discountModalContainer').html(response.html);

                            const modalEl = document.getElementById('discountModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // IMPORTANT
                            setupDiscountValidation(discountId, modal);

                        }).catch(err => {
                            console.error('❌ Failed to load form', err);
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupDiscountValidation(discountId, modal) {

                        const $form = $('#discountForm');

                        console.log('🧪 setupDiscountValidation called');
                        console.log('Form exists:', $form.length);

                        if (!$form.length) {
                            console.warn('❌ #discountForm not found');
                            return;
                        }

                        if ($form.data('validator')) {
                            console.warn('⚠️ Validator already exists');
                            return;
                        }

                        console.log('✅ Initializing jQuery Validation');

                        $form.validate({
                            rules: {
                                rate: {
                                    required: true,
                                    number: true,
                                    min: 0,
                                    max: 100
                                }
                            },
                            messages: {
                                rate: 'Discount Rate is required and must be between 0 and 100'
                            },
                            errorElement: 'div',
                            errorClass: 'invalid-feedback',
                            highlight(el) {
                                console.log('❌ Invalid:', el.name);
                                $(el).addClass('is-invalid');
                            },
                            unhighlight(el) {
                                console.log('✅ Valid:', el.name);
                                $(el).removeClass('is-invalid').addClass('is-valid');
                            },
                            errorPlacement(error, element) {
                                error.insertAfter(element);
                            },
                            invalidHandler(event, validator) {
                                console.warn('🚫 Validation failed');
                                console.log('Errors:', validator.errorList);
                            },
                            submitHandler(form) {
                                console.log('🚀 Validation passed → submitDiscountForm()');
                                submitDiscountForm(form, discountId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitDiscountForm(form, discountId, modal) {

                        console.log('📤 submitDiscountForm called');

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
                                console.log('✅ AJAX success:', res);
                                // Show success toast before closing modal
                                showToastInModal(modal, res.message || 'Discount saved successfully',
                                    'success');

                                // Close modal after a short delay to show success message
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);

                                // Reload table with current page preserved
                                reloadDiscountsTable();
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                // Get error message from server response
                                let errorMessage = 'Failed to save discount.';

                                if (err.message) {
                                    errorMessage = err.message;
                                } else if (err.errors) {
                                    // Handle validation errors
                                    const firstError = Object.values(err.errors)[0];
                                    if (Array.isArray(firstError)) {
                                        errorMessage = firstError[0];
                                    } else {
                                        errorMessage = firstError;
                                    }
                                }

                                // Show red error toast outside modal (top-right corner)
                                showToastInModal(modal, errorMessage, 'error');

                                // Clear any previous validation states (keep form clean - no field errors shown)
                                const $form = $('#discountForm');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') ||
                                    originalText;
                            });
                    }

                    /* -----------------------------------
                     CONFIRM DELETE DISCOUNT
                    ----------------------------------- */
                    function confirmDeleteDiscount(discountId, discountRate) {
                        // Remove existing delete modal if any
                        $('#deleteDiscountModal').remove();
                        $('.modal-backdrop').remove();

                        const modalHtml = `
                            <div class="modal fade" id="deleteDiscountModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete discount "<strong>${discountRate}</strong>"?</p>
                                            <p class="text-danger mb-0">This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-danger" id="confirmDeleteDiscountBtn">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $('body').append(modalHtml);

                        const modalEl = document.getElementById('deleteDiscountModal');
                        const modal = new bootstrap.Modal(modalEl);

                        modalEl.addEventListener('hidden.bs.modal', function() {
                            modalEl.remove();
                            cleanupModals();
                        }, {
                            once: true
                        });

                        const deleteBtn = document.getElementById('confirmDeleteDiscountBtn');
                        deleteBtn.onclick = function() {
                            deleteDiscount(discountId, modal, deleteBtn);
                        };

                        modal.show();
                    }

                    /* -----------------------------------
                     DELETE DISCOUNT
                    ----------------------------------- */
                    function deleteDiscount(discountId, modal, deleteBtn) {
                        const originalText = deleteBtn.innerHTML;
                        deleteBtn.disabled = true;
                        deleteBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                        const url = '{{ route('admin.discounts.destroy', ':id') }}'.replace(':id', discountId);

                        AdminAjax.request(url, 'DELETE')
                            .then(response => {
                                console.log('✅ Discount deleted successfully');
                                showToastInModal(null, response.message || 'Discount deleted successfully', 'success');
                                modal.hide();

                                // Reload table with current page preserved
                                reloadDiscountsTable();
                            })
                            .catch(error => {
                                console.error('❌ Error deleting discount:', error);
                                showToastInModal(null, error.message || 'Failed to delete discount.', 'error');
                                deleteBtn.disabled = false;
                                deleteBtn.innerHTML = originalText;
                            });
                    }

                    /* -----------------------------------
                     LOAD TABLE FROM URL ON PAGE LOAD
                    ----------------------------------- */
                    function loadTableFromURL() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const page = urlParams.get('page');
                        const perPage = urlParams.get('per_page');
                        const search = urlParams.get('search');

                        // Only load via AJAX if URL has parameters (otherwise use server-rendered content)
                        if (page || perPage || search) {
                            const params = {};
                            if (page) params.page = page;
                            if (perPage) params.per_page = perPage;
                            if (search) params.search = search;

                            // Update per page select if URL has per_page
                            if (perPage && $('#perPageSelect').length) {
                                $('#perPageSelect').val(perPage);
                            }

                            // Update search input if URL has search
                            if (search && $('[data-search]').length) {
                                $('[data-search]').val(search);
                            }

                            console.log('📄 Loading table from URL params:', params);

                            AdminAjax.loadTable('{{ route('admin.discounts') }}', $('.table-container')[0], {
                                params: params,
                    onSuccess: function(response) {
                        if (response.pagination) {
                                        $('.pagination-container').html(response.pagination);
                        }
                                    // Re-bind event handlers for dynamically loaded content
                                    bindPaginationHandlers();
                                }
                            });
                        } else {
                            // No URL params, just bind handlers for existing content
                            bindPaginationHandlers();
                        }
                    }

                    /* -----------------------------------
                     RELOAD DISCOUNTS TABLE (PRESERVE PAGE)
                    ----------------------------------- */
                    function reloadDiscountsTable() {
                        // Get current page from URL or pagination
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentPage = urlParams.get('page') || 1;
                        const currentPerPage = urlParams.get('per_page') || $('#perPageSelect').val() || 25;
                        const currentSearch = urlParams.get('search') || $('[data-search]').val() || '';
                    
                        const params = {
                            page: currentPage,
                            per_page: currentPerPage
                        };

                        if (currentSearch) {
                            params.search = currentSearch;
                        }

                        console.log('🔄 Reloading table with params:', params);
                    
                        AdminAjax.loadTable('{{ route('admin.discounts') }}', $('.table-container')[0], {
                            params: params,
                        onSuccess: function(response) {
                            if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                                // Re-bind event handlers for dynamically loaded content
                                bindPaginationHandlers();
                            }
                        });
                    }

                    /* -----------------------------------
                     BIND PAGINATION HANDLERS (AJAX)
                    ----------------------------------- */
                    function bindPaginationHandlers() {
                        // Remove existing handlers to prevent duplicates
                        $(document).off('click', '.pagination a');

                        // Bind pagination links to use AJAX
                        $(document).on('click', '.pagination a', function(e) {
                            e.preventDefault();
                            e.stopPropagation();

                            const url = $(this).attr('href');
                            if (!url || url === '#' || url === 'javascript:void(0)') {
                                return;
                            }

                            console.log('📄 Pagination clicked:', url);

                            // Extract page number from URL
                            const urlObj = new URL(url, window.location.origin);
                            const page = urlObj.searchParams.get('page') || 1;
                            const perPage = urlObj.searchParams.get('per_page') || $('#perPageSelect').val() || 25;
                            const search = urlObj.searchParams.get('search') || $('[data-search]').val() || '';

                            const params = {
                                page: page,
                                per_page: perPage
                            };

                            if (search) {
                                params.search = search;
                            }

                            // Update URL without reload
                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            // Load table via AJAX
                            AdminAjax.loadTable('{{ route('admin.discounts') }}', $('.table-container')[0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response.pagination);
                                    }
                                }
                            });
                        });
                    }

                    /* -----------------------------------
                     PER PAGE SELECT HANDLER
                    ----------------------------------- */
                    $(document).on('change', '#perPageSelect', function(e) {
                        e.preventDefault();
                        const perPage = $(this).val();
                        const currentPage = new URLSearchParams(window.location.search).get('page') ||
                            1;
                        const currentSearch = $('[data-search]').val() || '';

                        const params = {
                            page: 1, // Reset to page 1 when changing per page
                            per_page: perPage
                        };

                        if (currentSearch) {
                            params.search = currentSearch;
                        }

                        // Update URL without reload
                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        // Load table via AJAX
                        AdminAjax.loadTable('{{ route('admin.discounts') }}', $('.table-container')[0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                            }
                        });
                    });

                    /* -----------------------------------
                     SEARCH HANDLER
                    ----------------------------------- */
                    let searchTimeout;
                    $(document).on('input', '[data-search]', function(e) {
                        clearTimeout(searchTimeout);
                        const searchInput = $(this);

                        searchTimeout = setTimeout(function() {
                            const searchValue = searchInput.val();
                            const currentPage = new URLSearchParams(window.location.search).get(
                                'page') || 1;
                            const currentPerPage = $('#perPageSelect').val() || 25;

                            const params = {
                                page: 1, // Reset to page 1 when searching
                                per_page: currentPerPage
                            };

                            if (searchValue) {
                                params.search = searchValue;
                            }

                            // Update URL without reload
                            const newUrl = new URL(window.location.pathname, window.location
                                .origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            // Load table via AJAX
                            AdminAjax.loadTable('{{ route('admin.discounts') }}', $(
                                '.table-container')[0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response
                                            .pagination);
                                    }
                                }
                            });
                        }, 500); // Debounce search
                    });

                    /* -----------------------------------
                     INITIALIZE PAGINATION HANDLERS
                    ----------------------------------- */
                    bindPaginationHandlers();

                    /* -----------------------------------
                     SHOW TOAST (OUTSIDE MODAL - TOP RIGHT)
                    ----------------------------------- */
                    function showToastInModal(modal, message, type = 'error') {
                        // Create or get toast container at top-right corner of page
                        let toastContainer = $('#global-toast-container');

                        if (!toastContainer.length) {
                            toastContainer = $(
                                '<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>'
                            );
                            $('body').append(toastContainer);
                        }

                        // Remove any existing toasts of the same type to avoid stacking
                        toastContainer.find('.toast').each(function() {
                            const bsToast = bootstrap.Toast.getInstance(this);
                            if (bsToast) {
                                bsToast.hide();
                                }
                        });

                        // Create toast
                        const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                        const toastId = 'toast-' + Date.now();
                        const toast = $(`
                            <div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                        ${message}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        `);

                        toastContainer.append(toast);

                        // Initialize and show toast
                        const bsToast = new bootstrap.Toast(toast[0], {
                            autohide: true,
                            delay: 5000
                        });
                        bsToast.show();

                        // Remove toast element after it's hidden
                        toast.on('hidden.bs.toast', function() {
                            $(this).remove();
                            // Remove container if empty
                            if (toastContainer.find('.toast').length === 0) {
                                toastContainer.remove();
                            }
                        });
                    }

                    /* -----------------------------------
                     HELPERS
                    ----------------------------------- */
                    function cleanupModals() {
                        console.log('🧹 Cleaning modals');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({
                            overflow: '',
                            paddingRight: ''
                        });
                        $('#discountModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="discountModal">
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
            }

            // Start initialization
            initDiscountsScript();
        })();
    </script>
@endsection

