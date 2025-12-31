@extends('layouts.vertical', ['title' => 'Occasion List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Occasion List'])

    <div class="row">
        <div class="col-12">
            <!-- Occasion Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Occasion List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-occassion-btn">
                        <i class="ti ti-plus me-1"></i> Add Occasion
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search occasion..." value="{{ request('search') }}">
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
                        @include('admin.partials.occassions-table', ['occassions' => $occassions])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $occassions])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="occassionModalContainer"></div>
    <div id="occassionViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initOccassionScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initOccassionScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    // Load table from URL parameters on page load
                    loadTableFromURL();

                    /* -----------------------------------
                     ADD OCCASION BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.add-occassion-btn', function(e) {
                        e.preventDefault();
                        openOccassionModal(null);
                    });

                    /* -----------------------------------
                     VIEW OCCASION BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-occassion-btn', function(e) {
                        e.preventDefault();
                        const occassionId = $(this).data('occassion-id');
                        openOccassionViewModal(occassionId);
                    });

                    /* -----------------------------------
                     EDIT OCCASION BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-occassion-btn', function(e) {
                        e.preventDefault();
                        const occassionId = $(this).data('occassion-id');
                        openOccassionModal(occassionId);
                    });

                    /* -----------------------------------
                     DELETE OCCASION BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.delete-occassion-btn', function(e) {
                        e.preventDefault();
                        const occassionId = $(this).data('occassion-id');
                        const occassionName = $(this).data('occassion-name') || 'this occasion';
                        if (confirm(`Are you sure you want to delete ${occassionName}?`)) {
                            deleteOccassion(occassionId);
                        }
                    });

                    /* -----------------------------------
                     OPEN OCCASION MODAL (ADD/EDIT)
                    ----------------------------------- */
                    function openOccassionModal(occassionId) {
                        cleanupModals();
                        const url = occassionId
                            ? '{{ route('admin.occassion.edit', ':id') }}'.replace(':id', occassionId)
                            : '{{ route('admin.occassion.create') }}';

                        $('#occassionModalContainer').html(loaderHtml());
                        const loadingModal = new bootstrap.Modal($('#occassionModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#occassionModalContainer').html(response.html);

                            const modalEl = document.getElementById('occassionModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            setupOccassionValidation(occassionId, modal);

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     OPEN OCCASION VIEW MODAL
                    ----------------------------------- */
                    function openOccassionViewModal(occassionId) {
                        cleanupModals();
                        const url = '{{ route('admin.occassion.show', ':id') }}'.replace(':id', occassionId);

                        $('#occassionViewModalContainer').html(loaderHtml());
                        const loadingModal = new bootstrap.Modal($('#occassionModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#occassionViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('occassionViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     DELETE OCCASION
                    ----------------------------------- */
                    function deleteOccassion(occassionId) {
                        const url = '{{ route('admin.occassion.destroy', ':id') }}'.replace(':id', occassionId);
                        AdminAjax.request(url, 'DELETE')
                            .then(res => {
                                showToast('Occasion deleted successfully', 'success');
                                reloadOccassionTable();
                            })
                            .catch(err => {
                                showToast(err.message || 'Failed to delete occasion.', 'error');
                            });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupOccassionValidation(occassionId, modal) {
                        const $form = $('#occassionForm');
                        if (!$form.length || $form.data('validator')) {
                            return;
                        }

                        $form.validate({
                            rules: {
                                occassion: {
                                    required: true
                                },
                                occassionAR: {
                                    required: true
                                },
                                occassioncode: {
                                    required: true
                                }
                            },
                            messages: {
                                occassion: 'Occasion name (EN) is required.',
                                occassionAR: 'Occasion name (AR) is required.',
                                occassioncode: 'Occasion code is required.'
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
                                submitOccassionForm(form, occassionId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitOccassionForm(form, occassionId, modal) {
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.setAttribute('data-original-text', originalText);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                showToastInModal(modal, res.message || 'Occasion saved successfully', 'success');
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);
                                reloadOccassionTable();
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to save occasion.';
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
                                const $form = $('#occassionForm');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            });
                    }

                    /* -----------------------------------
                     RELOAD OCCASION TABLE (PRESERVE PAGE)
                    ----------------------------------- */
                    function reloadOccassionTable() {
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

                        AdminAjax.loadTable('{{ route('admin.occassion') }}', $('.table-container')[0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                                bindPaginationHandlers();
                            }
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

                            if (perPage && $('#perPageSelect').length) {
                                $('#perPageSelect').val(perPage);
                            }

                            if (search && $('[data-search]').length) {
                                $('[data-search]').val(search);
                            }

                            AdminAjax.loadTable('{{ route('admin.occassion') }}', $('.table-container')[0], {
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

                    /* -----------------------------------
                     BIND PAGINATION HANDLERS (AJAX)
                    ----------------------------------- */
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

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.occassion') }}', $('.table-container')[0], {
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
                        const currentSearch = $('[data-search]').val() || '';

                        const params = {
                            page: 1,
                            per_page: perPage
                        };
                        if (currentSearch) {
                            params.search = currentSearch;
                        }

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.occassion') }}', $('.table-container')[0], {
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
                            const currentPerPage = $('#perPageSelect').val() || 25;

                            const params = {
                                page: 1,
                                per_page: currentPerPage
                            };
                            if (searchValue) {
                                params.search = searchValue;
                            }

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.occassion') }}', $('.table-container')[0], {
                                params: params,
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        $('.pagination-container').html(response.pagination);
                                    }
                                }
                            });
                        }, 500);
                    });

                    /* -----------------------------------
                     INITIALIZE PAGINATION HANDLERS
                    ----------------------------------- */
                    bindPaginationHandlers();

                    /* -----------------------------------
                     SHOW TOAST (OUTSIDE MODAL - TOP RIGHT)
                    ----------------------------------- */
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

                    /* -----------------------------------
                     HELPERS
                    ----------------------------------- */
                    function cleanupModals() {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({
                            overflow: '',
                            paddingRight: ''
                        });
                        $('#occassionModal').remove();
                        $('#occassionViewModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="occassionModal">
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
            initOccassionScript();
        })();
    </script>
@endsection

