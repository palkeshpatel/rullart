@extends('layouts.vertical', ['title' => 'Category List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Category List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Category:</label>
                            <select id="parentCategoryFilter" class="form-select form-select-sm">
                                <option value="">--Parent--</option>
                                <option value="0">No Parent (Main Categories)</option>
                                @foreach ($parentCategories ?? [] as $parent)
                                    <option value="{{ $parent->categoryid }}">{{ $parent->category }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

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
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search category...">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                                            <option value="25">25</option>
                                            <option value="50">50</option>
                                            <option value="100">100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="categoriesTable" class="table table-bordered table-striped table-hover"
                            style="width:100%">
                            <thead>
                                <tr>
                                    <th>Category (EN)</th>
                                    <th>Category (AR)</th>
                                    <th>Is Active?</th>
                                    <th>Display Order</th>
                                    <th>Updated Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
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
            let dataTablesLoaded = false;

            function loadDataTables(callback) {
                if (dataTablesLoaded && typeof jQuery !== 'undefined' && typeof jQuery.fn.DataTable !== 'undefined') {
                    callback();
                    return;
                }

                if (typeof jQuery === 'undefined') {
                    setTimeout(function() {
                        loadDataTables(callback);
                    }, 50);
                    return;
                }

                // jQuery is ready, now load DataTables
                if (!dataTablesLoaded) {
                    const dtScript = document.createElement('script');
                    dtScript.src = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
                    dtScript.onload = function() {
                        const dtRespScript = document.createElement('script');
                        dtRespScript.src =
                            'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
                        dtRespScript.onload = function() {
                            dataTablesLoaded = true;
                            callback();
                        };
                        document.head.appendChild(dtRespScript);
                    };
                    document.head.appendChild(dtScript);
                } else {
                    setTimeout(function() {
                        loadDataTables(callback);
                    }, 50);
                }
            }

            function initCategoryDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initCategoryDataTable, 50);
                        return;
                    }

                    const $ = jQuery;

                    // Base URLs for actions (use URL instead of route helper)
                    const categoryBaseUrl = '{{ url('/admin/category') }}';

                    $(document).ready(function() {
                        // Loader modal for AJAX calls
                        let loadingModal = null;

                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('categoryDataTableLoader');
                                loadingModal = new bootstrap.Modal(modalEl, {
                                    backdrop: 'static',
                                    keyboard: false
                                });
                            }
                            loadingModal.show();
                        }

                        function hideLoader() {
                            if (loadingModal) {
                                loadingModal.hide();
                                cleanupLoader();
                            }
                        }

                        function cleanupLoader() {
                            $('#categoryDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        // Track if this is the first draw (initial load)
                        let isFirstDraw = true;

                        // Show loader on initial load
                        showLoader();

                        let table = $('#categoriesTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: categoryBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    // Show loader on pagination/sorting changes (but not on first request)
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    // Add custom filters
                                    d.parent_category = $('#parentCategoryFilter').val();
                                    console.log('📤 DataTables request:', d);
                                },
                                dataSrc: function(json) {
                                    hideLoader();
                                    // Mark that first draw is complete
                                    isFirstDraw = false;
                                    console.log('📥 DataTables response:', json);
                                    if (json.error) {
                                        console.error('❌ Server error:', json.error);
                                        alert('Error: ' + json.error);
                                    }
                                    return json.data;
                                },
                                error: function(xhr, error, thrown) {
                                    hideLoader();
                                    console.error('❌ DataTables AJAX Error:', error);
                                    alert('Error loading data. Status: ' + xhr.status);
                                }
                            },
                            columns: [{
                                    data: 'category',
                                    name: 'category'
                                },
                                {
                                    data: 'categoryAR',
                                    name: 'categoryAR'
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive'
                                },
                                {
                                    data: 'displayorder',
                                    name: 'displayorder'
                                },
                                {
                                    data: 'updateddate',
                                    name: 'updateddate'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-category-btn" data-category-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-category-btn" data-category-id="' +
                                            row.action + '" title="Edit">';
                                        html +=
                                            '<i class="ti ti-edit fs-lg"></i></a>';
                                        html += '</div>';
                                        return html;
                                    }
                                }
                            ],
                            pageLength: 25,
                            lengthMenu: [
                                [25, 50, 100],
                                [25, 50, 100]
                            ],
                            order: [
                                [0, 'desc']
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No categories found",
                                zeroRecords: "No matching categories found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1,
                                        5] // Category (EN), Category (AR), Action - always visible
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3] // Is Active?, Display Order
                                },
                                {
                                    responsivePriority: 3,
                                    targets: [4] // Updated Date
                                }
                            ]
                        });

                        // External Search & Filters
                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#parentCategoryFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // View Category Button
                        $(document).on('click', '.view-category-btn', function(e) {
                            e.preventDefault();
                            const categoryId = $(this).data('category-id');
                            openCategoryViewModal(categoryId);
                        });

                        // Edit Category Button
                        $(document).on('click', '.edit-category-btn', function(e) {
                            e.preventDefault();
                            const categoryId = $(this).data('category-id');
                            openCategoryFormModal(categoryId);
                        });

                        // Add Category Button
                        $(document).on('click', '.add-category-btn', function(e) {
                            e.preventDefault();
                            openCategoryFormModal();
                        });

                        // Open View Modal
                        function openCategoryViewModal(categoryId) {
                            cleanupModals();
                            const url = categoryBaseUrl + '/' + categoryId;
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

                        // Open Form Modal
                        function openCategoryFormModal(categoryId = null) {
                            cleanupModals();
                            const url = categoryId ? categoryBaseUrl + '/' + categoryId + '/edit' :
                                categoryBaseUrl + '/create';
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

                        // Validation Setup
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
                                    parentid: {
                                        required: true
                                    },
                                    categorycode: {
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

                        // Submit Form
                        function submitCategoryForm(form, categoryId, modal) {
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
                                    showToast(res.message || 'Category saved successfully',
                                        'success');
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                                })
                                .catch(err => {
                                    let errorMessage = 'Failed to save category.';
                                    if (err.message) {
                                        errorMessage = err.message;
                                    } else if (err.errors) {
                                        const firstError = Object.values(err.errors)[0];
                                        errorMessage = Array.isArray(firstError) ? firstError[0] :
                                            firstError;
                                    }
                                    showToast(errorMessage, 'error');
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                });
                        }

                        // Show Toast
                        function showToast(message, type = 'error') {
                            let toastContainer = $('#global-toast-container');
                            if (!toastContainer.length) {
                                toastContainer = $(
                                    '<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>'
                                    );
                                $('body').append(toastContainer);
                            }

                            toastContainer.find('.toast').each(function() {
                                const bsToast = bootstrap.Toast.getInstance(this);
                                if (bsToast) bsToast.hide();
                            });

                            const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                            const toastId = 'toast-' + Date.now();
                            const toast = $(`
                                <div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                            ${message}
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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

                        // Cleanup Modals
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
                                <div class="modal fade" id="categoryDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Categories...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                });
            }

            initCategoryDataTable();
        })();
    </script>
@endsection
