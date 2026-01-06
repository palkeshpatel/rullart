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
                                        placeholder="Search messages...">
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
                        <table id="messagesTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Message(EN)</th>
                                    <th>Message(AR)</th>
                                    <th>Display Order</th>
                                    <th>Active</th>
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
    <div id="messageModalContainer"></div>
    <div id="messageViewModalContainer"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-alert-triangle text-warning me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this message?</p>
                    <p class="text-danger mb-0"><small>This action cannot be undone.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteMessageBtn">Delete</button>
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

            function initMessagesDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initMessagesDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const messageBaseUrl = '{{ url("/admin/messages") }}';
                    let deleteMessageId = null;

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('messageDataTableLoader');
                                if (modalEl) {
                                    loadingModal = new bootstrap.Modal(modalEl, {
                                        backdrop: 'static',
                                        keyboard: false
                                    });
                                }
                            }
                            if (loadingModal) {
                                loadingModal.show();
                            }
                        }
                        
                        function hideLoader() {
                            if (loadingModal) {
                                loadingModal.hide();
                                cleanupLoader();
                            }
                        }
                        
                        function cleanupLoader() {
                            $('#messageDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#messagesTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip',
                            ajax: {
                                url: messageBaseUrl,
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
                                    data: 'messageid',
                                    name: 'messageid'
                                },
                                {
                                    data: 'message',
                                    name: 'message'
                                },
                                {
                                    data: 'messageAR',
                                    name: 'messageAR'
                                },
                                {
                                    data: 'displayorder',
                                    name: 'displayorder'
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-message-btn" data-message-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-message-btn" data-message-id="' +
                                            row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-message-btn" data-message-id="' +
                                            row.action + '" title="Delete">';
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
                                [0, 'asc']
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No messages found",
                                zeroRecords: "No matching messages found"
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

                        $(document).on('click', '.view-message-btn', function(e) {
                            e.preventDefault();
                            const messageId = $(this).data('message-id');
                            openMessageViewModal(messageId);
                        });

                        $(document).on('click', '.edit-message-btn', function(e) {
                            e.preventDefault();
                            const messageId = $(this).data('message-id');
                            openMessageFormModal(messageId);
                        });

                        $(document).on('click', '.add-message-btn', function(e) {
                            e.preventDefault();
                            openMessageFormModal();
                        });

                        $(document).on('click', '.delete-message-btn', function(e) {
                            e.preventDefault();
                            deleteMessageId = $(this).data('message-id');
                            const deleteModal = new bootstrap.Modal(document.getElementById('deleteMessageModal'));
                            deleteModal.show();
                        });

                        $('#confirmDeleteMessageBtn').on('click', function() {
                            if (deleteMessageId) {
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;
                                
                                AdminAjax.request(messageBaseUrl + '/' + deleteMessageId, 'DELETE')
                                    .then(res => {
                                        bootstrap.Modal.getInstance(document.getElementById('deleteMessageModal')).hide();
                                        showToast('Message deleted successfully', 'success');
                                        
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
                                        showToast(err.message || 'Failed to delete message.', 'error');
                                    });
                            }
                        });

                        function openMessageViewModal(messageId) {
                            cleanupModals();
                            const url = messageBaseUrl + '/' + messageId;
                            $('#messageViewModalContainer').html(loaderHtml());
                            
                            const loadingModalEl = document.getElementById('messageModal');
                            let loadingModal = null;
                            if (loadingModalEl) {
                                loadingModal = new bootstrap.Modal(loadingModalEl, {
                                    backdrop: 'static',
                                    keyboard: false
                                });
                                loadingModal.show();
                            }

                            AdminAjax.get(url).then(response => {
                                if (loadingModal) {
                                    loadingModal.hide();
                                }
                                cleanupModals();
                                if (response && response.html) {
                                    $('#messageViewModalContainer').html(response.html);
                                    const modalEl = document.getElementById('messageViewModal');
                                    if (modalEl) {
                                        const modal = new bootstrap.Modal(modalEl);
                                        modal.show();

                                        modalEl.addEventListener('hidden.bs.modal', function() {
                                            cleanupModals();
                                        }, { once: true });
                                    } else {
                                        console.error('Message view modal element not found');
                                        showToast('Failed to load message details', 'error');
                                    }
                                } else {
                                    console.error('Invalid response:', response);
                                    showToast('Failed to load message details', 'error');
                                }
                            }).catch(err => {
                                if (loadingModal) {
                                    loadingModal.hide();
                                }
                                cleanupModals();
                                console.error('Error loading message view:', err);
                                showToast('Failed to load message details: ' + (err.message || 'Unknown error'), 'error');
                            });
                        }

                        function openMessageFormModal(messageId = null) {
                            cleanupModals();
                            const url = messageId ? messageBaseUrl + '/' + messageId + '/edit' : messageBaseUrl + '/create';
                            $('#messageModalContainer').html(loaderHtml());
                            
                            // Wait a bit for DOM to update before creating modal
                            setTimeout(function() {
                                const loadingModalEl = document.getElementById('messageModal');
                                let loadingModal = null;
                                if (loadingModalEl) {
                                    loadingModal = new bootstrap.Modal(loadingModalEl, {
                                        backdrop: 'static',
                                        keyboard: false
                                    });
                                    loadingModal.show();
                                }

                                AdminAjax.get(url).then(response => {
                                    if (loadingModal) {
                                        loadingModal.hide();
                                    }
                                    cleanupModals();
                                    if (response && response.html) {
                                        $('#messageModalContainer').html(response.html);
                                        // Wait for DOM to update
                                        setTimeout(function() {
                                            const modalEl = document.getElementById('messageModal');
                                            if (modalEl) {
                                                const modal = new bootstrap.Modal(modalEl);
                                                modal.show();
                                                setupMessageValidation(messageId, modal);
                                            } else {
                                                console.error('Message modal element not found');
                                                showToast('Failed to load message form', 'error');
                                            }
                                        }, 100);
                                    } else {
                                        console.error('Invalid response:', response);
                                        showToast('Failed to load message form', 'error');
                                    }
                                }).catch(err => {
                                    if (loadingModal) {
                                        loadingModal.hide();
                                    }
                                    cleanupModals();
                                    console.error('Error loading message form:', err);
                                    showToast('Failed to load message form: ' + (err.message || 'Unknown error'), 'error');
                                });
                            }, 100);
                        }

                        function setupMessageValidation(messageId, modal) {
                            const $form = $('#messageForm');
                            if (!$form.length || $form.data('validator')) {
                                return;
                            }

                            $form.validate({
                                rules: {
                                    message: { 
                                        required: true 
                                    },
                                    messageAR: { 
                                        required: true 
                                    }
                                },
                                messages: {
                                    message: 'Message is required.',
                                    messageAR: 'Message {AR} is required.'
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
                                    submitMessageForm(form, messageId, modal);
                                }
                            });
                        }

                        function submitMessageForm(form, messageId, modal) {
                            const formData = new FormData(form);
                            const url = form.action;
                            const method = form.querySelector('[name="_method"]')?.value || 'POST';
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                            AdminAjax.request(url, method, formData)
                                .then(res => {
                                    showToast(res.message || 'Message saved successfully', 'success');
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                                })
                                .catch(err => {
                                    let errorMessage = 'Failed to save message.';
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
                            $('#messageModal').remove();
                            $('#messageViewModal').remove();
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
                });
            }

            initMessagesDataTable();
        })();
    </script>
@endsection
