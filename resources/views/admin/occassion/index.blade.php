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
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search occasion...">
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
                        <table id="occassionsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Occassion Name</th>
                                    <th>Photo</th>
                                    <th>Is Published</th>
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
    <div id="occassionModalContainer"></div>
    <div id="occassionViewModalContainer"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteOccassionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteOccassionName"></strong>?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteOccassionBtn">Delete</button>
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

            function initOccassionDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initOccassionDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const occassionBaseUrl = '{{ url("/admin/occassion") }}';
                    let deleteOccassionId = null;

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('occassionDataTableLoader');
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
                            $('#occassionDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#occassionsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: occassionBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
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
                                    data: 'occassion',
                                    name: 'occassion'
                                },
                                {
                                    data: 'photo',
                                    name: 'photo',
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    data: 'ispublished',
                                    name: 'ispublished'
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
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-occassion-btn" data-occassion-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-occassion-btn" data-occassion-id="' +
                                            row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-occassion-btn" data-occassion-id="' +
                                            row.action + '" data-occassion-name="' + (row.occassion || 'this occasion') +
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
                                emptyTable: "No occasions found",
                                zeroRecords: "No matching occasions found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 4]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [1, 2, 3]
                                }
                            ]
                        });

                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // View Occasion Button
                        $(document).on('click', '.view-occassion-btn', function(e) {
                            e.preventDefault();
                            const occassionId = $(this).data('occassion-id');
                            openOccassionViewModal(occassionId);
                        });

                        // Edit Occasion Button
                        $(document).on('click', '.edit-occassion-btn', function(e) {
                            e.preventDefault();
                            const occassionId = $(this).data('occassion-id');
                            openOccassionFormModal(occassionId);
                        });

                        // Add Occasion Button
                        $(document).on('click', '.add-occassion-btn', function(e) {
                            e.preventDefault();
                            openOccassionFormModal();
                        });

                        // Delete Occasion Button
                        $(document).on('click', '.delete-occassion-btn', function(e) {
                            e.preventDefault();
                            deleteOccassionId = $(this).data('occassion-id');
                            const occassionName = $(this).data('occassion-name') || 'this occasion';
                            $('#deleteOccassionName').text(occassionName);
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteOccassionModal'));
                            deleteModal.show();
                        });

                        // Confirm Delete
                        $('#confirmDeleteOccassionBtn').on('click', function() {
                            if (deleteOccassionId) {
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;
                                
                                AdminAjax.request(occassionBaseUrl + '/' + deleteOccassionId, 'DELETE')
                                    .then(res => {
                                        bootstrap.Modal.getInstance(document.getElementById('deleteOccassionModal')).hide();
                                        showToast('Occasion deleted successfully', 'success');
                                        
                                        // Reload table and maintain current page
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
                                        showToast(err.message || 'Failed to delete occasion.', 'error');
                                    });
                            }
                        });

                        // Open View Modal
                        function openOccassionViewModal(occassionId) {
                            cleanupModals();
                            const url = occassionBaseUrl + '/' + occassionId;
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

                                modalEl.addEventListener('hidden.bs.modal', function() {
                                    cleanupModals();
                                }, { once: true });
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                                AdminAjax.showError('Failed to load occasion details.');
                            });
                        }

                        // Open Form Modal
                        function openOccassionFormModal(occassionId = null) {
                            cleanupModals();
                            const url = occassionId ? occassionBaseUrl + '/' + occassionId + '/edit' : occassionBaseUrl + '/create';
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

                        // Validation Setup
                        function setupOccassionValidation(occassionId, modal) {
                            const $form = $('#occassionForm');
                            if (!$form.length || $form.data('validator')) {
                                return;
                            }

                            $form.validate({
                                rules: {
                                    occassion: { required: true },
                                    occassionAR: { required: true }
                                },
                                messages: {
                                    occassion: 'Occasion name (EN) is required.',
                                    occassionAR: 'Occasion name (AR) is required.'
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

                        // Submit Form
                        function submitOccassionForm(form, occassionId, modal) {
                            const formData = new FormData(form);
                            const url = form.action;
                            const method = form.querySelector('[name="_method"]')?.value || 'POST';
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                            AdminAjax.request(url, method, formData)
                                .then(res => {
                                    showToast(res.message || 'Occasion saved successfully', 'success');
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                                })
                                .catch(err => {
                                    let errorMessage = 'Failed to save occasion.';
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

                        // Show Toast
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

                        // Cleanup Modals
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
                                <div class="modal fade" id="occassionDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Occasions...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
                });
            }

            initOccassionDataTable();
        })();
    </script>
@endsection
