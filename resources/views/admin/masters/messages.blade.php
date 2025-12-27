@extends('layouts.vertical', ['title' => 'Gift Messages List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Gift Messages List'])

    <div class="row">
        <div class="col-12">
            <!-- Messages Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Gift Messages List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-message-btn">
                        <i class="ti ti-plus me-1"></i> Add Message
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search messages..." value="{{ request('search') }}">
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
                        @include('admin.masters.partials.messages.messages-table', ['messages' => $messages])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $messages])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="messageModalContainer"></div>
    <div id="messageViewModalContainer"></div>
@endsection


@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initMessagesScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initMessagesScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {

                    console.log('✅ Document ready for Gift Messages');

                    // Load table from URL parameters on page load
                    loadTableFromURL();

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#messageForm');
                    $(document).on('submit', '#messageForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD MESSAGE BUTTON (OPEN MODAL ONLY)
                    ----------------------------------- */
                    $(document).on('click', '.add-message-btn', function(e) {
                        e.preventDefault();
                        console.log('➕ Add Message clicked (open modal)');
                        openMessageFormModal();
                    });

                    /* -----------------------------------
                     EDIT MESSAGE BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-message-btn', function(e) {
                        e.preventDefault();
                        const messageId = $(this).data('message-id');
                        console.log('✏️ Edit Message clicked, ID:', messageId);
                        openMessageFormModal(messageId);
                    });

                    /* -----------------------------------
                     VIEW MESSAGE BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-message-btn', function(e) {
                        e.preventDefault();
                        const messageId = $(this).data('message-id');
                        console.log('👁️ View Message clicked, ID:', messageId);
                        openMessageViewModal(messageId);
                    });

                    /* -----------------------------------
                     DELETE MESSAGE BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.delete-message-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const messageId = $(this).data('message-id');
                        const messageText = $(this).data('message-text') || 'this message';
                        console.log('🗑️ Delete Message clicked, ID:', messageId);
                        confirmDeleteMessage(messageId, messageText);
                    });

                    /* -----------------------------------
                     OPEN VIEW MODAL
                    ----------------------------------- */
                    function openMessageViewModal(messageId) {
                        console.log('📦 Opening message view modal, ID:', messageId);

                        cleanupModals();

                        const url = '{{ route('admin.messages.show', ':id') }}'.replace(':id', messageId);

                        $('#messageViewModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#messageModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            console.log('📥 View HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#messageViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('messageViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle edit button click from view modal
                            $(modalEl).find('.edit-message-btn').on('click', function(e) {
                                e.preventDefault();
                                const editMessageId = $(this).data('message-id');
                                modal.hide();
                                cleanupModals();
                                // Open edit modal
                                setTimeout(() => {
                                    openMessageFormModal(editMessageId);
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
                            showToastInModal(null, 'Failed to load message details.', 'error');
                        });
                    }

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openMessageFormModal(messageId = null) {

                        console.log('📦 Opening message form modal, ID:', messageId);

                        cleanupModals();

                        const url = messageId ?
                            '{{ route('admin.messages.edit', ':id') }}'.replace(':id', messageId) :
                            '{{ route('admin.messages.create') }}';

                        $('#messageModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#messageModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {

                            console.log('📥 Form HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#messageModalContainer').html(response.html);

                            const modalEl = document.getElementById('messageModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // IMPORTANT
                            setupMessageValidation(messageId, modal);

                        }).catch(err => {
                            console.error('❌ Failed to load form', err);
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupMessageValidation(messageId, modal) {

                        const $form = $('#messageForm');

                        console.log('🧪 setupMessageValidation called');
                        console.log('Form exists:', $form.length);

                        if (!$form.length) {
                            console.warn('❌ #messageForm not found');
                            return;
                        }

                        if ($form.data('validator')) {
                            console.warn('⚠️ Validator already exists');
                            return;
                        }

                        console.log('✅ Initializing jQuery Validation');

                        $form.validate({
                            rules: {
                                message: {
                                    required: true,
                                    maxlength: 500
                                }
                            },
                            messages: {
                                message: 'Message(EN) is required and cannot exceed 500 characters'
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
                                console.log('🚀 Validation passed → submitMessageForm()');
                                submitMessageForm(form, messageId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitMessageForm(form, messageId, modal) {

                        console.log('📤 submitMessageForm called');

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
                                showToastInModal(modal, res.message || 'Gift message saved successfully',
                                    'success');

                                // Close modal after a short delay to show success message
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);

                                // Reload table with current page preserved
                                reloadMessagesTable();
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                // Get error message from server response
                                let errorMessage = 'Failed to save gift message.';

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
                                const $form = $('#messageForm');
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
                     CONFIRM DELETE MESSAGE
                    ----------------------------------- */
                    function confirmDeleteMessage(messageId, messageText) {
                        // Remove existing delete modal if any
                        $('#deleteMessageModal').remove();
                        $('.modal-backdrop').remove();

                        const modalHtml = `
                            <div class="modal fade" id="deleteMessageModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete this message "<strong>${messageText}</strong>"?</p>
                                            <p class="text-danger mb-0">This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-danger" id="confirmDeleteMessageBtn">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $('body').append(modalHtml);

                        const modalEl = document.getElementById('deleteMessageModal');
                        const modal = new bootstrap.Modal(modalEl);

                        modalEl.addEventListener('hidden.bs.modal', function() {
                            modalEl.remove();
                            cleanupModals();
                        }, {
                            once: true
                        });

                        const deleteBtn = document.getElementById('confirmDeleteMessageBtn');
                        deleteBtn.onclick = function() {
                            deleteMessage(messageId, modal, deleteBtn);
                        };

                        modal.show();
                    }

                    /* -----------------------------------
                     DELETE MESSAGE
                    ----------------------------------- */
                    function deleteMessage(messageId, modal, deleteBtn) {
                        const originalText = deleteBtn.innerHTML;
                        deleteBtn.disabled = true;
                        deleteBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                        const url = '{{ route('admin.messages.destroy', ':id') }}'.replace(':id', messageId);

                        AdminAjax.request(url, 'DELETE')
                            .then(response => {
                                console.log('✅ Message deleted successfully');
                                showToastInModal(null, response.message || 'Gift message deleted successfully', 'success');
                                modal.hide();

                                // Reload table with current page preserved
                                reloadMessagesTable();
                            })
                            .catch(error => {
                                console.error('❌ Error deleting message:', error);
                                showToastInModal(null, error.message || 'Failed to delete gift message.', 'error');
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

                            AdminAjax.loadTable('{{ route('admin.messages') }}', $('.table-container')[0], {
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
                     RELOAD MESSAGES TABLE (PRESERVE PAGE)
                    ----------------------------------- */
                    function reloadMessagesTable() {
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
                    
                        AdminAjax.loadTable('{{ route('admin.messages') }}', $('.table-container')[0], {
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
                            AdminAjax.loadTable('{{ route('admin.messages') }}', $('.table-container')[0], {
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
                        AdminAjax.loadTable('{{ route('admin.messages') }}', $('.table-container')[0], {
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
                            AdminAjax.loadTable('{{ route('admin.messages') }}', $(
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
                        $('#messageModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="messageModal">
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
            initMessagesScript();
        })();
    </script>
@endsection

