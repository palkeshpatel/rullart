@extends('layouts.vertical', ['title' => 'Home Gallery List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Home Gallery List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section -->
            <form method="GET" action="{{ route('admin.home-gallery') }}" data-table-filters id="homeGalleryFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Published:</label>
                                <select name="published" class="form-select form-select-sm" data-filter>
                                    <option value="">All</option>
                                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Published</option>
                                    <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Unpublished</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Home Gallery Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Home Gallery List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-home-gallery-btn">
                        <i class="ti ti-plus me-1"></i> Add Photo
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search gallery..." value="{{ request('search') }}">
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
                        @include('admin.pages.partials.home-gallery.home-gallery-table', ['homeGalleries' => $homeGalleries])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $homeGalleries])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="homeGalleryModalContainer"></div>
    <div id="homeGalleryViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initHomeGalleryScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initHomeGalleryScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    console.log('✅ Document ready for Home Gallery');
                    loadTableFromURL();

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#homeGalleryForm');
                    $(document).on('submit', '#homeGalleryForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD HOME GALLERY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.add-home-gallery-btn', function(e) {
                        e.preventDefault();
                        console.log('➕ Add Photo clicked');
                        openHomeGalleryFormModal();
                    });

                    /* -----------------------------------
                     EDIT HOME GALLERY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-home-gallery-btn', function(e) {
                        e.preventDefault();
                        const homeGalleryId = $(this).data('home-gallery-id');
                        console.log('✏️ Edit Photo clicked, ID:', homeGalleryId);
                        openHomeGalleryFormModal(homeGalleryId);
                    });

                    /* -----------------------------------
                     VIEW HOME GALLERY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-home-gallery-btn', function(e) {
                        e.preventDefault();
                        const homeGalleryId = $(this).data('home-gallery-id');
                        console.log('👁️ View Photo clicked, ID:', homeGalleryId);
                        openHomeGalleryViewModal(homeGalleryId);
                    });

                    /* -----------------------------------
                     DELETE HOME GALLERY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.delete-home-gallery-btn', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        const homeGalleryId = $(this).data('home-gallery-id');
                        const homeGalleryTitle = $(this).data('home-gallery-title') || 'this photo';
                        console.log('🗑️ Delete Photo clicked, ID:', homeGalleryId);
                        confirmDeleteHomeGallery(homeGalleryId, homeGalleryTitle);
                    });

                    /* -----------------------------------
                     OPEN VIEW MODAL
                    ----------------------------------- */
                    function openHomeGalleryViewModal(homeGalleryId) {
                        console.log('📦 Opening home gallery view modal, ID:', homeGalleryId);

                        cleanupModals();

                        const url = '{{ route('admin.home-gallery.show', ':id') }}'.replace(':id', homeGalleryId);

                        $('#homeGalleryViewModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#homeGalleryModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            console.log('📥 View HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#homeGalleryViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('homeGalleryViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle edit button click from view modal
                            $(modalEl).find('.edit-home-gallery-btn').on('click', function(e) {
                                e.preventDefault();
                                const editHomeGalleryId = $(this).data('home-gallery-id');
                                modal.hide();
                                cleanupModals();
                                setTimeout(() => {
                                    openHomeGalleryFormModal(editHomeGalleryId);
                                }, 300);
                            });

                            // Cleanup on close
                            modalEl.addEventListener('hidden.bs.modal', function() {
                                cleanupModals();
                            }, { once: true });

                        }).catch(err => {
                            console.error('❌ Failed to load view', err);
                            loadingModal.hide();
                            cleanupModals();
                            showToastInModal(null, 'Failed to load photo details.', 'error');
                        });
                    }

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openHomeGalleryFormModal(homeGalleryId = null) {
                        console.log('📦 Opening home gallery form modal, ID:', homeGalleryId);

                        cleanupModals();

                        const url = homeGalleryId ?
                            '{{ route('admin.home-gallery.edit', ':id') }}'.replace(':id', homeGalleryId) :
                            '{{ route('admin.home-gallery.create') }}';

                        $('#homeGalleryModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#homeGalleryModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            console.log('📥 Form HTML loaded');

                            loadingModal.hide();
                            cleanupModals();

                            $('#homeGalleryModalContainer').html(response.html);

                            const modalEl = document.getElementById('homeGalleryModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // IMPORTANT
                            setupHomeGalleryValidation(homeGalleryId, modal);

                        }).catch(err => {
                            console.error('❌ Failed to load form', err);
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupHomeGalleryValidation(homeGalleryId, modal) {
                        const $form = $('#homeGalleryForm');

                        console.log('🧪 setupHomeGalleryValidation called');
                        console.log('Form exists:', $form.length);

                        if (!$form.length) {
                            console.warn('❌ #homeGalleryForm not found');
                            return;
                        }

                        if ($form.data('validator')) {
                            console.warn('⚠️ Validator already exists');
                            return;
                        }

                        console.log('✅ Initializing jQuery Validation');

                        $form.validate({
                            rules: {
                                title: {
                                    required: true
                                },
                                link: {
                                    url: true
                                },
                                videourl: {
                                    url: true
                                },
                                displayorder: {
                                    number: true,
                                    min: 0
                                }
                            },
                            messages: {
                                title: 'Title(EN) is required',
                                link: 'Please enter a valid URL',
                                videourl: 'Please enter a valid video URL',
                                displayorder: {
                                    number: 'Display Order must be a number',
                                    min: 'Display Order cannot be negative'
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
                                console.log('🚀 Validation passed → submitHomeGalleryForm()');
                                submitHomeGalleryForm(form, homeGalleryId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitHomeGalleryForm(form, homeGalleryId, modal) {
                        console.log('📤 submitHomeGalleryForm called');

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
                                console.log('✅ AJAX success:', res);
                                showToastInModal(modal, res.message || 'Photo saved successfully', 'success');

                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);

                                reloadHomeGalleryTable();
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                let errorMessage = 'Failed to save photo.';

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

                                const $form = $('#homeGalleryForm');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            });
                    }

                    /* -----------------------------------
                     CONFIRM DELETE HOME GALLERY
                    ----------------------------------- */
                    function confirmDeleteHomeGallery(homeGalleryId, homeGalleryTitle) {
                        $('#deleteHomeGalleryModal').remove();
                        $('.modal-backdrop').remove();

                        const modalHtml = `
                            <div class="modal fade" id="deleteHomeGalleryModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirm Delete</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Are you sure you want to delete "<strong>${homeGalleryTitle}</strong>"?</p>
                                            <p class="text-danger mb-0">This action cannot be undone.</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="button" class="btn btn-danger" id="confirmDeleteHomeGalleryBtn">Delete</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;

                        $('body').append(modalHtml);

                        const modalEl = document.getElementById('deleteHomeGalleryModal');
                        const modal = new bootstrap.Modal(modalEl);

                        modalEl.addEventListener('hidden.bs.modal', function() {
                            modalEl.remove();
                            cleanupModals();
                        }, { once: true });

                        const deleteBtn = document.getElementById('confirmDeleteHomeGalleryBtn');
                        deleteBtn.onclick = function() {
                            deleteHomeGallery(homeGalleryId, modal, deleteBtn);
                        };

                        modal.show();
                    }

                    /* -----------------------------------
                     DELETE HOME GALLERY
                    ----------------------------------- */
                    function deleteHomeGallery(homeGalleryId, modal, deleteBtn) {
                        const originalText = deleteBtn.innerHTML;
                        deleteBtn.disabled = true;
                        deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...';

                        const url = '{{ route('admin.home-gallery.destroy', ':id') }}'.replace(':id', homeGalleryId);

                        AdminAjax.request(url, 'DELETE')
                            .then(response => {
                                console.log('✅ Photo deleted successfully');
                                showToastInModal(null, response.message || 'Photo deleted successfully', 'success');
                                modal.hide();

                                reloadHomeGalleryTable();
                            })
                            .catch(error => {
                                console.error('❌ Error deleting photo:', error);
                                showToastInModal(null, error.message || 'Failed to delete photo.', 'error');
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
                        const published = urlParams.get('published');

                        if (page || perPage || search || published) {
                            const params = {};
                            if (page) params.page = page;
                            if (perPage) params.per_page = perPage;
                            if (search) params.search = search;
                            if (published) params.published = published;

                            if (perPage && $('#perPageSelect').length) {
                                $('#perPageSelect').val(perPage);
                            }

                            if (search && $('[data-search]').length) {
                                $('[data-search]').val(search);
                            }

                            if (published && $('[data-filter][name="published"]').length) {
                                $('[data-filter][name="published"]').val(published);
                            }

                            console.log('📄 Loading table from URL params:', params);

                            AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
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
                     RELOAD HOME GALLERY TABLE
                    ----------------------------------- */
                    function reloadHomeGalleryTable() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentPage = urlParams.get('page') || 1;
                        const currentPerPage = urlParams.get('per_page') || $('#perPageSelect').val() || 25;
                        const currentSearch = urlParams.get('search') || $('[data-search]').val() || '';
                        const currentPublishedFilter = $('[data-filter][name="published"]').val() || '';

                        const params = {
                            page: currentPage,
                            per_page: currentPerPage
                        };

                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (currentPublishedFilter) {
                            params.published = currentPublishedFilter;
                        }

                        console.log('🔄 Reloading table with params:', params);

                        AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
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

                            console.log('📄 Pagination clicked:', url);

                            const urlObj = new URL(url, window.location.origin);
                            const page = urlObj.searchParams.get('page') || 1;
                            const perPage = urlObj.searchParams.get('per_page') || $('#perPageSelect').val() || 25;
                            const search = urlObj.searchParams.get('search') || $('[data-search]').val() || '';
                            const published = urlObj.searchParams.get('published') || $('[data-filter][name="published"]').val() || '';

                            const params = {
                                page: page,
                                per_page: perPage
                            };

                            if (search) {
                                params.search = search;
                            }
                            if (published) {
                                params.published = published;
                            }

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
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
                        const currentPublishedFilter = $('[data-filter][name="published"]').val() || '';

                        const params = {
                            page: 1,
                            per_page: perPage
                        };

                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (currentPublishedFilter) {
                            params.published = currentPublishedFilter;
                        }

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
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
                            const currentPublishedFilter = $('[data-filter][name="published"]').val() || '';

                            const params = {
                                page: 1,
                                per_page: currentPerPage
                            };

                            if (searchValue) {
                                params.search = searchValue;
                            }
                            if (currentPublishedFilter) {
                                params.published = currentPublishedFilter;
                            }

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
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
                     FILTER HANDLER
                    ----------------------------------- */
                    $(document).on('change', '[data-filter]', function(e) {
                        e.preventDefault();
                        const filterName = $(this).attr('name');
                        const filterValue = $(this).val();
                        const currentPerPage = $('#perPageSelect').val() || 25;
                        const currentSearch = $('[data-search]').val() || '';

                        const params = {
                            page: 1,
                            per_page: currentPerPage
                        };

                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (filterValue) {
                            params[filterName] = filterValue;
                        }

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.home-gallery') }}', $('.table-container')[0], {
                            params: params,
                            onSuccess: function(response) {
                                if (response.pagination) {
                                    $('.pagination-container').html(response.pagination);
                                }
                            }
                        });
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
                        $('#homeGalleryModal').remove();
                    }

                    function loaderHtml() {
                        return `
            <div class="modal fade" id="homeGalleryModal">
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
            initHomeGalleryScript();
        })();
    </script>
@endsection

