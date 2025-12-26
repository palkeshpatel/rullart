@extends('layouts.vertical', ['title' => 'Colors List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Colors List'])

    <div class="row">
        <div class="col-12">
            <!-- Colors Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Colors List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-color-btn">
                        <i class="ti ti-plus me-1"></i> Add Color
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search colors..." value="{{ request('search') }}">
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
                        @include('admin.masters.partials.color.colors-table', ['colors' => $colors])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $colors])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="colorModalContainer"></div>
    <div id="colorViewModalContainer"></div>
@endsection


@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initColorsScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initColorsScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {

                    console.log('✅ Document ready');

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#colorForm');
                    $(document).on('submit', '#colorForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD COLOR BUTTON (OPEN MODAL ONLY)
                    ----------------------------------- */
                    $(document).on('click', '.add-color-btn', function(e) {
                        e.preventDefault();
                        console.log('➕ Add Color clicked (open modal)');
                        openColorFormModal();
                    });

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openColorFormModal(colorId = null) {

                        console.log('📦 Opening color form modal, ID:', colorId);

                        cleanupModals();

                        const url = colorId ?
                            '{{ route('admin.colors.edit', ':id') }}'.replace(':id', colorId) :
                            '{{ route('admin.colors.create') }}';

                        $('#colorModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#colorModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {

                            console.log('📥 Form HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#colorModalContainer').html(response.html);

                            const modalEl = document.getElementById('colorModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // IMPORTANT
                            setupColorValidation(colorId, modal);

                        }).catch(err => {
                            console.error('❌ Failed to load form', err);
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupColorValidation(colorId, modal) {

                        const $form = $('#colorForm');

                        console.log('🧪 setupColorValidation called');
                        console.log('Form exists:', $form.length);

                        if (!$form.length) {
                            console.warn('❌ #colorForm not found');
                            return;
                        }

                        if ($form.data('validator')) {
                            console.warn('⚠️ Validator already exists');
                            return;
                        }

                        console.log('✅ Initializing jQuery Validation');

                        $form.validate({
                            rules: {
                                filtervalue: {
                                    required: true
                                },
                                filtervalueAR: {
                                    required: true
                                }
                            },
                            messages: {
                                filtervalue: 'Color Name (EN) is required',
                                filtervalueAR: 'Color Name (AR) is required'
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
                                console.log('🚀 Validation passed → submitColorForm()');
                                submitColorForm(form, colorId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitColorForm(form, colorId, modal) {

                        console.log('📤 submitColorForm called');

                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';

                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                console.log('✅ AJAX success:', res);
                                // Show success toast before closing modal
                                showToastInModal(modal, res.message || 'Color saved successfully',
                                    'success');

                                // Close modal after a short delay to show success message
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);

                                // Reload table
                                AdminAjax.loadTable('{{ route('admin.colors') }}', $('.table-container')[
                                    0], {
                                    params: {},
                                    onSuccess: function(response) {
                                        if (response.pagination) {
                                            $('.pagination-container').html(response
                                                .pagination);
                                        }
                                    }
                                });
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                // Get error message from server response
                                let errorMessage = 'Failed to save color.';

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

                                // Show red error toast inside modal
                                showToastInModal(modal, errorMessage, 'error');

                                // Show field errors if validation errors exist
                                if (err.errors) {
                                    const $form = $('#colorForm');
                                    // Clear previous errors
                                    $form.find('.is-invalid').removeClass('is-invalid');
                                    $form.find('.invalid-feedback').html('').removeClass('d-block').hide();

                                    Object.keys(err.errors).forEach(fieldName => {
                                        const field = $form.find(`[name="${fieldName}"]`);
                                        if (field.length) {
                                            field.addClass('is-invalid');
                                            const feedbackDiv = field.closest('.mb-3').find(
                                                '.invalid-feedback');
                                            if (feedbackDiv.length) {
                                                const errorMsg = Array.isArray(err.errors[
                                                        fieldName]) ?
                                                    err.errors[fieldName][0] :
                                                    err.errors[fieldName];
                                                feedbackDiv.html(errorMsg).addClass('d-block')
                                                    .show();
                                            }
                                        }
                                    });
                                }

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            });
                    }

                    /* -----------------------------------
                     SHOW TOAST IN MODAL
                    ----------------------------------- */
                    function showToastInModal(modal, message, type = 'error') {
                        // Get modal element - handle both Bootstrap Modal instance and DOM element
                        let modalElement;
                        if (modal && modal._element) {
                            modalElement = modal._element;
                        } else if (typeof modal === 'string') {
                            modalElement = document.getElementById(modal);
                        } else if (modal && modal.nodeType) {
                            modalElement = modal;
                        } else {
                            modalElement = document.getElementById('colorModal');
                        }

                        if (!modalElement) {
                            console.error('❌ Modal element not found');
                            return;
                        }

                        const $modal = $(modalElement);

                        // Remove existing toasts in modal
                        $modal.find('.toast-container').remove();

                        // Create toast container inside modal - position it at the top of modal-body
                        const toastContainer = $('<div class="toast-container position-relative mb-2"></div>');
                        const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                        const toast = $(`
                            <div class="toast ${toastBg} text-white border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
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

                        // Insert toast at the top of modal-body
                        const $modalBody = $modal.find('.modal-body');
                        if ($modalBody.length) {
                            $modalBody.prepend(toastContainer);
                        } else {
                            // Fallback: prepend to modal-content
                            $modal.find('.modal-content').prepend(toastContainer);
                        }

                        // Initialize and show toast
                        const bsToast = new bootstrap.Toast(toast[0], {
                            autohide: true,
                            delay: 5000
                        });
                        bsToast.show();

                        // Remove toast element after it's hidden
                        toast.on('hidden.bs.toast', function() {
                            $(this).closest('.toast-container').remove();
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
                        $('#colorModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="colorModal">
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
            initColorsScript();
        })();
    </script>
@endsection
