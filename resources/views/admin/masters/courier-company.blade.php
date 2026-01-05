@extends('layouts.vertical', ['title' => 'Courier Company List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Courier Company List'])

    <div class="row">
        <div class="col-12">
            <!-- Courier Company Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Courier Company List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm add-courier-btn">
                        <i class="ti ti-plus me-1"></i> Add Courier Company
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Active:</label>
                                    <select class="form-select form-select-sm" id="activeFilter">
                                        <option value="">--All--</option>
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search courier companies...">
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
                        <table id="courierCompanyTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Tracking URL</th>
                                    <th>Active</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
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
    <div id="courierModalContainer"></div>
    <div id="courierViewModalContainer"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteCourierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteCourierName"></strong>?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteCourierBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
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

                if (!dataTablesLoaded) {
                    const dtScript = document.createElement('script');
                    dtScript.src = 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js';
                    dtScript.onload = function() {
                        const dtRespScript = document.createElement('script');
                        dtRespScript.src = 'https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js';
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

            function initCourierCompanyDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initCourierCompanyDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const courierBaseUrl = '{{ url("/admin/courier-company") }}';
                    let deleteCourierId = null;

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('courierDataTableLoader');
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
                            $('#courierDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#courierCompanyTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip',
                            ajax: {
                                url: courierBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                    d.active = $('#activeFilter').val();
                                    console.log('📤 DataTables request:', d);
                                },
                                dataSrc: function(json) {
                                    hideLoader();
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
                                    data: 'id',
                                    name: 'id'
                                },
                                {
                                    data: 'name',
                                    name: 'name'
                                },
                                {
                                    data: 'tracking_url',
                                    name: 'tracking_url',
                                    orderable: false
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive'
                                },
                                {
                                    data: 'created_at',
                                    name: 'created_at'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-courier-btn" data-courier-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-courier-btn" data-courier-id="' +
                                            row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-courier-btn" data-courier-id="' +
                                            row.action + '" data-courier-name="' + (row.name || 'this courier company') +
                                            '" title="Delete">';
                                        html += '<i class="ti ti-trash fs-lg"></i></a>';
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
                                emptyTable: "No courier companies found",
                                zeroRecords: "No matching courier companies found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 5]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3, 4]
                                }
                            ]
                        });

                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#activeFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        $(document).on('click', '.view-courier-btn', function(e) {
                            e.preventDefault();
                            const courierId = $(this).data('courier-id');
                            openCourierViewModal(courierId);
                        });

                        $(document).on('click', '.edit-courier-btn', function(e) {
                            e.preventDefault();
                            const courierId = $(this).data('courier-id');
                            openCourierFormModal(courierId);
                        });

                        $(document).on('click', '.add-courier-btn', function(e) {
                            e.preventDefault();
                            openCourierFormModal();
                        });

                        $(document).on('click', '.delete-courier-btn', function(e) {
                            e.preventDefault();
                            deleteCourierId = $(this).data('courier-id');
                            const courierName = $(this).data('courier-name') || 'this courier company';
                            $('#deleteCourierName').text(courierName);
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteCourierModal'));
                            deleteModal.show();
                        });

                        $('#confirmDeleteCourierBtn').on('click', function() {
                            if (deleteCourierId) {
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;
                                
                                AdminAjax.request(courierBaseUrl + '/' + deleteCourierId, 'DELETE')
                                    .then(res => {
                                        bootstrap.Modal.getInstance(document.getElementById('deleteCourierModal')).hide();
                                        showToast('Courier company deleted successfully', 'success');
                                        
                                        showLoader();
                                        table.ajax.reload(function() {
                                            hideLoader();
                                            const newTotalPages = table.page.info().pages;
                                            if (currentPage >= newTotalPages && newTotalPages > 0) {
                                                table.page(newTotalPages - 1).draw('page');
                                            } else {
                                                table.page(currentPage).draw('page');
                                            }
                                        }, false);
                                    })
                                    .catch(err => {
                                        showToast(err.message || 'Failed to delete courier company.', 'error');
                                    });
                            }
                        });

                        function openCourierViewModal(courierId) {
                            cleanupModals();
                            const url = courierBaseUrl + '/' + courierId;
                            $('#courierViewModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#courierModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#courierViewModalContainer').html(response.html);
                                const modalEl = document.getElementById('courierViewModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();

                                modalEl.addEventListener('hidden.bs.modal', function() {
                                    cleanupModals();
                                }, { once: true });
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                                AdminAjax.showError('Failed to load courier company details.');
                            });
                        }

                        function openCourierFormModal(courierId = null) {
                            cleanupModals();
                            const url = courierId ? courierBaseUrl + '/' + courierId + '/edit' : courierBaseUrl + '/create';
                            $('#courierModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#courierModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#courierModalContainer').html(response.html);
                                const modalEl = document.getElementById('courierModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                                setupCourierValidation(courierId, modal);
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                            });
                        }

                        function setupCourierValidation(courierId, modal) {
                            const $form = $('#courierForm');
                            if (!$form.length || $form.data('validator')) {
                                return;
                            }

                            $form.validate({
                                rules: {
                                    name: { required: true }
                                },
                                messages: {
                                    name: 'Courier Company Name is required.'
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
                                    submitCourierForm(form, courierId, modal);
                                }
                            });
                        }

                        function submitCourierForm(form, courierId, modal) {
                            const formData = new FormData(form);
                            const url = form.action;
                            const method = form.querySelector('[name="_method"]')?.value || 'POST';
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                            AdminAjax.request(url, method, formData)
                                .then(res => {
                                    showToast(res.message || 'Courier company saved successfully', 'success');
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                                })
                                .catch(err => {
                                    let errorMessage = 'Failed to save courier company.';
                                    if (err.message) {
                                        errorMessage = err.message;
                                    } else if (err.errors) {
                                        const firstError = Object.values(err.errors)[0];
                                        errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                                    }
                                    showToast(errorMessage, 'error');
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                });
                        }

                        function showToast(message, type = 'error') {
                            let toastContainer = $('#global-toast-container');
                            if (!toastContainer.length) {
                                toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
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
                            const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 5000 });
                            bsToast.show();

                            toast.on('hidden.bs.toast', function() {
                                $(this).remove();
                                if (toastContainer.find('.toast').length === 0) {
                                    toastContainer.remove();
                                }
                            });
                        }

                        function cleanupModals() {
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            $('#courierModal').remove();
                            $('#courierViewModal').remove();
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="courierModal">
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

            initCourierCompanyDataTable();
        })();
    </script>
@endsection
