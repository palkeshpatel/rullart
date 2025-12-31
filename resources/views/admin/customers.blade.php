@extends('layouts.vertical', ['title' => 'Customers List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Customers List'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Customers List</h4>
                    {{-- <a href="javascript:void(0);" class="btn btn-success btn-sm add-customer-btn">
                        <i class="ti ti-plus me-1"></i> Add Customer
                    </a> --}}
                </div>
                <div class="card-body">
                    <!-- Filters Form -->
                    <form method="GET" action="{{ route('admin.customers') }}" data-table-filters id="customersFilterForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="mb-0">Show
                                    <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                        id="perPageSelect">
                                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25
                                        </option>
                                        <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50
                                        </option>
                                        <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100
                                        </option>
                                    </select> entries
                                </label>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="input-group" style="max-width: 300px; margin-left: auto;">
                                    <span class="input-group-text">Search:</span>
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.partials.customers-table', ['customers' => $customers])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $customers])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="customerModalContainer"></div>
    <div id="customerViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initCustomersScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initCustomersScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {

                    console.log('✅ Document ready');

                    // Load table from URL parameters on page load
                    loadTableFromURL();

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#customerForm');
                    $(document).on('submit', '#customerForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD CUSTOMER BUTTON (OPEN MODAL ONLY)
                    ----------------------------------- */
                    $(document).on('click', '.add-customer-btn', function(e) {
                        e.preventDefault();
                        console.log('➕ Add Customer clicked (open modal)');
                        openCustomerFormModal();
                    });

                    /* -----------------------------------
                     EDIT CUSTOMER BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-customer-btn', function(e) {
                        e.preventDefault();
                        const customerId = $(this).data('customer-id');
                        console.log('✏️ Edit Customer clicked, ID:', customerId);
                        openCustomerFormModal(customerId);
                    });

                    /* -----------------------------------
                     VIEW CUSTOMER BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-customer-btn', function(e) {
                        e.preventDefault();
                        const customerId = $(this).data('customer-id');
                        console.log('👁️ View Customer clicked, ID:', customerId);
                        openCustomerViewModal(customerId);
                    });

                    /* -----------------------------------
                     OPEN VIEW MODAL
                    ----------------------------------- */
                    function openCustomerViewModal(customerId) {
                        console.log('📦 Opening customer view modal, ID:', customerId);

                        cleanupModals();

                        const url = '{{ route('admin.customers.show', ':id') }}'.replace(':id', customerId);

                        $('#customerViewModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#customerModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            console.log('📥 View HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#customerViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('customerViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle edit button click from view modal
                            $(modalEl).find('.edit-customer-btn').on('click', function(e) {
                                e.preventDefault();
                                const editCustomerId = $(this).data('customer-id');
                                modal.hide();
                                cleanupModals();
                                // Open edit modal
                                setTimeout(() => {
                                    openCustomerFormModal(editCustomerId);
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
                            AdminAjax.showError('Failed to load customer details.');
                        });
                    }

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openCustomerFormModal(customerId = null) {

                        console.log('📦 Opening customer form modal, ID:', customerId);

                        cleanupModals();

                        const url = customerId ?
                            '{{ route('admin.customers.edit', ':id') }}'.replace(':id', customerId) :
                            '{{ route('admin.customers.create') }}';

                        $('#customerModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#customerModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {

                            console.log('📥 Form HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#customerModalContainer').html(response.html);

                            const modalEl = document.getElementById('customerModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // IMPORTANT
                            setupCustomerValidation(customerId, modal);

                        }).catch(err => {
                            console.error('❌ Failed to load form', err);
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupCustomerValidation(customerId, modal) {

                        const $form = $('#customerForm');

                        console.log('🧪 setupCustomerValidation called');
                        console.log('Form exists:', $form.length);

                        if (!$form.length) {
                            console.warn('❌ #customerForm not found');
                            return;
                        }

                        if ($form.data('validator')) {
                            console.warn('⚠️ Validator already exists');
                            return;
                        }

                        console.log('✅ Initializing jQuery Validation');

                        $form.validate({
                            rules: {
                                firstname: {
                                    required: true
                                },
                                email: {
                                    required: true,
                                    email: true
                                },
                                password: {
                                    required: !customerId, // Required only for new customers
                                    minlength: 6
                                }
                            },
                            messages: {
                                firstname: 'First name is required',
                                email: {
                                    required: 'Email is required',
                                    email: 'Please enter a valid email address'
                                },
                                password: {
                                    required: 'Password is required',
                                    minlength: 'Password must be at least 6 characters'
                                }
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
                                console.log('🚀 Validation passed → submitCustomerForm()');
                                submitCustomerForm(form, customerId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitCustomerForm(form, customerId, modal) {

                        console.log('📤 submitCustomerForm called');

                        // Create FormData
                        const formData = new FormData(form);

                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';

                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        // Store original text as data attribute for recovery
                        submitBtn.setAttribute('data-original-text', originalText);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                console.log('✅ AJAX success:', res);
                                // Show success toast before closing modal
                                showToastInModal(modal, res.message || 'Customer saved successfully',
                                    'success');

                                // Close modal after a short delay to show success message
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);

                                // Reload table with current page preserved
                                reloadCustomersTable();
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                // Get error message from server response
                                let errorMessage = 'Failed to save customer.';

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
                                const $form = $('#customerForm');
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

                            AdminAjax.loadTable('{{ route('admin.customers') }}', $('.table-container')[0], {
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
                     RELOAD CUSTOMERS TABLE (PRESERVE PAGE)
                    ----------------------------------- */
                    function reloadCustomersTable() {
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

                        AdminAjax.loadTable('{{ route('admin.customers') }}', $('.table-container')[0], {
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
                            const perPage = urlObj.searchParams.get('per_page') || $('#perPageSelect')
                                .val() || 25;
                            const search = urlObj.searchParams.get('search') || $('[data-search]')
                                .val() || '';

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
                            AdminAjax.loadTable('{{ route('admin.customers') }}', $(
                                '.table-container')[
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
                        AdminAjax.loadTable('{{ route('admin.customers') }}', $('.table-container')[
                            0], {
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
                            AdminAjax.loadTable('{{ route('admin.customers') }}', $(
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
                        $('#customerModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="customerModal">
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
            initCustomersScript();
        })();
    </script>
@endsection
