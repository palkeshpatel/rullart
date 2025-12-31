@extends('layouts.vertical', ['title' => 'Category List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Category List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.category') }}" data-table-filters id="categoryFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category:</label>
                                <select name="parent_category" class="form-select form-select-sm" data-filter>
                                    <option value="">--Parent--</option>
                                    <option value="0" {{ request('parent_category') == '0' ? 'selected' : '' }}>No Parent (Main Categories)</option>
                                    @foreach ($parentCategories ?? [] as $parent)
                                        <option value="{{ $parent->categoryid }}"
                                            {{ request('parent_category') == $parent->categoryid ? 'selected' : '' }}>{{ $parent->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Category Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Category List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-category-btn">
                        <i class="ti ti-plus me-1"></i> Add Category
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search category..." value="{{ request('search') }}">
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
                        @include('admin.partials.categories-table', ['categories' => $categories])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $categories])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Container -->
    <div id="categoryModalContainer"></div>
    <div id="categoryViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available (Vite loads scripts asynchronously)
        (function() {
            function initCategoryScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initCategoryScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    // Load table from URL parameters on page load
                    loadTableFromURL();

                    /* -----------------------------------
                     HARD BLOCK native submit (AJAX forms)
                    ----------------------------------- */
                    $(document).off('submit', '#categoryForm');
                    $(document).on('submit', '#categoryForm', function(e) {
                        e.preventDefault();
                        return false;
                    });

                    /* -----------------------------------
                     ADD CATEGORY BUTTON (OPEN MODAL ONLY)
                    ----------------------------------- */
                    $(document).on('click', '.add-category-btn', function(e) {
                        e.preventDefault();
                        openCategoryFormModal();
                    });

                    /* -----------------------------------
                     EDIT CATEGORY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.edit-category-btn', function(e) {
                        e.preventDefault();
                        const categoryId = $(this).data('category-id');
                        openCategoryFormModal(categoryId);
                    });

                    /* -----------------------------------
                     VIEW CATEGORY BUTTON
                    ----------------------------------- */
                    $(document).on('click', '.view-category-btn', function(e) {
                        e.preventDefault();
                        const categoryId = $(this).data('category-id');
                        openCategoryViewModal(categoryId);
                    });

                    /* -----------------------------------
                     OPEN VIEW MODAL
                    ----------------------------------- */
                    function openCategoryViewModal(categoryId) {
                        cleanupModals();

                        const url = '{{ route('admin.category.show', ':id') }}'.replace(':id', categoryId);

                        $('#categoryViewModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#categoryModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#categoryViewModalContainer').html(response.html);

                            const modalEl = document.getElementById('categoryViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            // Handle edit button click from view modal
                            $(modalEl).find('.edit-category-btn').on('click', function(e) {
                                e.preventDefault();
                                const editCategoryId = $(this).data('category-id');
                                modal.hide();
                                cleanupModals();
                                setTimeout(() => {
                                    openCategoryFormModal(editCategoryId);
                                }, 300);
                            });

                            modalEl.addEventListener('hidden.bs.modal', function() {
                                cleanupModals();
                            }, {
                                once: true
                            });

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
                            AdminAjax.showError('Failed to load category details.');
                        });
                    }

                    /* -----------------------------------
                     OPEN FORM MODAL
                    ----------------------------------- */
                    function openCategoryFormModal(categoryId = null) {
                        cleanupModals();

                        const url = categoryId ?
                            '{{ route('admin.category.edit', ':id') }}'.replace(':id', categoryId) :
                            '{{ route('admin.category.create') }}';

                        $('#categoryModalContainer').html(loaderHtml());

                        const loadingModal = new bootstrap.Modal($('#categoryModal')[0], {
                            backdrop: 'static',
                            keyboard: false
                        });

                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            cleanupModals();

                            $('#categoryModalContainer').html(response.html);

                            const modalEl = document.getElementById('categoryModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                            setupCategoryValidation(categoryId, modal);

                        }).catch(err => {
                            loadingModal.hide();
                            cleanupModals();
                        });
                    }

                    /* -----------------------------------
                     VALIDATION SETUP
                    ----------------------------------- */
                    function setupCategoryValidation(categoryId, modal) {
                        const $form = $('#categoryForm');
                        if (!$form.length || $form.data('validator')) {
                            return;
                        }

                        $form.validate({
                            rules: {
                                category: {
                                    required: true
                                },
                                categoryAR: {
                                    required: true
                                },
                                categorycode: {
                                    required: true
                                },
                                parentid: {
                                    required: true
                                }
                            },
                            messages: {
                                category: 'Category name (EN) is required.',
                                categoryAR: 'Category name (AR) is required.',
                                categorycode: 'Category code is required.',
                                parentid: 'Parent category is required.'
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
                                submitCategoryForm(form, categoryId, modal);
                            }
                        });
                    }

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitCategoryForm(form, categoryId, modal) {
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
                                showToastInModal(modal, res.message || 'Category saved successfully', 'success');
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);
                                reloadCategoryTable();
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to save category.';
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
                                const $form = $('#categoryForm');
                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            });
                    }

                    /* -----------------------------------
                     RELOAD CATEGORY TABLE (PRESERVE PAGE)
                    ----------------------------------- */
                    function reloadCategoryTable() {
                        const urlParams = new URLSearchParams(window.location.search);
                        const currentPage = urlParams.get('page') || 1;
                        const currentPerPage = urlParams.get('per_page') || $('#perPageSelect').val() || 25;
                        const currentSearch = urlParams.get('search') || $('[data-search]').val() || '';
                        const currentParentCategory = urlParams.get('parent_category') || $('[data-filter]').val() || '';

                        const params = {
                            page: currentPage,
                            per_page: currentPerPage
                        };
                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (currentParentCategory) {
                            params.parent_category = currentParentCategory;
                        }

                        AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                        const parentCategory = urlParams.get('parent_category');

                        // Only load via AJAX if URL has parameters (otherwise use server-rendered content)
                        if (page || perPage || search || parentCategory) {
                            const params = {};
                            if (page) params.page = page;
                            if (perPage) params.per_page = perPage;
                            if (search) params.search = search;
                            if (parentCategory) params.parent_category = parentCategory;

                            if (perPage && $('#perPageSelect').length) {
                                $('#perPageSelect').val(perPage);
                            }

                            if (search && $('[data-search]').length) {
                                $('[data-search]').val(search);
                            }

                            AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                            const parentCategory = urlObj.searchParams.get('parent_category') || $('[data-filter]').val() || '';

                            const params = {
                                page: page,
                                per_page: perPage
                            };
                            if (search) {
                                params.search = search;
                            }
                            if (parentCategory) {
                                params.parent_category = parentCategory;
                            }

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                        const currentParentCategory = $('[data-filter]').val() || '';

                        const params = {
                            page: 1,
                            per_page: perPage
                        };
                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (currentParentCategory) {
                            params.parent_category = currentParentCategory;
                        }

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                            const currentParentCategory = $('[data-filter]').val() || '';

                            const params = {
                                page: 1,
                                per_page: currentPerPage
                            };
                            if (searchValue) {
                                params.search = searchValue;
                            }
                            if (currentParentCategory) {
                                params.parent_category = currentParentCategory;
                            }

                            const newUrl = new URL(window.location.pathname, window.location.origin);
                            Object.keys(params).forEach(key => {
                                if (params[key]) {
                                    newUrl.searchParams.set(key, params[key]);
                                }
                            });
                            window.history.pushState({}, '', newUrl.toString());

                            AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                     FILTER HANDLER (PARENT CATEGORY)
                    ----------------------------------- */
                    $(document).on('change', '[data-filter]', function(e) {
                        const parentCategory = $(this).val();
                        const currentSearch = $('[data-search]').val() || '';
                        const currentPerPage = $('#perPageSelect').val() || 25;

                        const params = {
                            page: 1,
                            per_page: currentPerPage
                        };
                        if (currentSearch) {
                            params.search = currentSearch;
                        }
                        if (parentCategory) {
                            params.parent_category = parentCategory;
                        }

                        const newUrl = new URL(window.location.pathname, window.location.origin);
                        Object.keys(params).forEach(key => {
                            if (params[key]) {
                                newUrl.searchParams.set(key, params[key]);
                            }
                        });
                        window.history.pushState({}, '', newUrl.toString());

                        AdminAjax.loadTable('{{ route('admin.category') }}', $('.table-container')[0], {
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
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css({
                            overflow: '',
                            paddingRight: ''
                        });
                        $('#categoryModal').remove();
                        $('#categoryViewModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="categoryModal">
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
            initCategoryScript();
        })();
    </script>
@endsection
