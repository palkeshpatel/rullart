@extends('layouts.vertical', ['title' => 'Mobile Devices'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Customers Mobile Devices List'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Customers Mobile Devices List</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ url('/admin/mobiledevice/export') }}" 
                            class="btn btn-success btn-sm" title="Export to Excel">
                            <i class="ti ti-file-excel me-1"></i> Export
                        </a>
                        <button type="button" class="btn btn-sm btn-primary" id="sendToAllBtn">
                            <i class="ti ti-send"></i> Send To All Customer
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search devices...">
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
                        <table id="devicesTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Device ID</th>
                                    <th>Is Active?</th>
                                    <th>Last Login</th>
                                    <th>Register Date</th>
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

    <!-- Notification to Customer Modal -->
    <div class="modal fade" id="notificationModal" tabindex="-1" aria-labelledby="notificationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="notificationModalLabel">Notification to Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="notificationForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="deviceId" class="form-label">Device id</label>
                            <input type="text" class="form-control" id="deviceId" name="device_id" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="redirectType" class="form-label">Redirect Type</label>
                            <select class="form-select" id="redirectType" name="redirect_type" required>
                                <option value="category">category</option>
                                <option value="product">product</option>
                                <option value="other">other</option>
                            </select>
                        </div>
                        <div class="mb-3" id="categoryField" style="display: none;">
                            <label for="categoryId" class="form-label">Category</label>
                            <select class="form-select" id="categoryId" name="category_id">
                                <option value="">--Select--</option>
                                @if(isset($categories) && is_iterable($categories))
                                    @foreach($categories as $category)
                                        <option value="{{ $category->categoryid }}">{{ $category->category }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mb-3" id="productCodeField" style="display: none;">
                            <label for="productCode" class="form-label">Product Code</label>
                            <input type="text" class="form-control" id="productCode" name="product_code" placeholder="Product Code">
                        </div>
                        <div class="mb-3">
                            <label for="notificationTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="notificationTitle" name="title" placeholder="Title">
                        </div>
                        <div class="mb-3">
                            <label for="notificationMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="notificationMessage" name="message" rows="4" placeholder="Message"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Send Message</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
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

            function initDevicesDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initDevicesDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const deviceBaseUrl = '{{ url("/admin/mobiledevice") }}';

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('deviceDataTableLoader');
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
                            $('#deviceDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#devicesTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip',
                            ajax: {
                                url: deviceBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
                                },
                                dataSrc: function(json) {
                                    hideLoader();
                                    isFirstDraw = false;
                                    if (json.error) {
                                        alert('Error: ' + json.error);
                                    }
                                    return json.data;
                                },
                                error: function(xhr, error, thrown) {
                                    hideLoader();
                                    alert('Error loading data. Status: ' + xhr.status);
                                }
                            },
                            columns: [{
                                    data: 'name',
                                    name: 'name',
                                    render: function(data) {
                                        return '<div class="d-flex align-items-center"><i class="ti ti-circle-filled text-danger me-2" style="font-size: 8px;"></i>' + data + '</div>';
                                    }
                                },
                                {
                                    data: 'mobile',
                                    name: 'mobile'
                                },
                                {
                                    data: 'device_id',
                                    name: 'device_id',
                                    render: function(data) {
                                        return '<div class="text-truncate" style="max-width: 200px;" title="' + data + '">' + data + '</div>';
                                    }
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive',
                                    render: function(data) {
                                        const badgeClass = data === 'Yes' ? 'badge-soft-success' : 'badge-soft-danger';
                                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                                    }
                                },
                                {
                                    data: 'lastlogin',
                                    name: 'lastlogin'
                                },
                                {
                                    data: 'registerdate',
                                    name: 'registerdate'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html += '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle send-notification-btn" data-device-id="' + row.action + '" title="Send Notification">';
                                        html += '<i class="ti ti-send fs-lg"></i></a>';
                                        html += '</div>';
                                        return html;
                                    }
                                }
                            ],
                            pageLength: 25,
                            lengthMenu: [[25, 50, 100], [25, 50, 100]],
                            order: [[0, 'desc']],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No mobile devices found",
                                zeroRecords: "No matching mobile devices found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 6]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3, 4, 5]
                                }
                            ]
                        });

                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // Notification Modal
                        const notificationModal = new bootstrap.Modal(document.getElementById('notificationModal'));
                        let isSendToAll = false;

                        // Setup Validation
                        function setupNotificationValidation() {
                            const $form = $('#notificationForm');
                            if (!$form.length) {
                                return;
                            }

                            // Remove existing validator if any
                            if ($form.data('validator')) {
                                $form.data('validator', null);
                            }

                            // Add custom method for conditional category validation
                            if ($.validator && $.validator.methods) {
                                $.validator.addMethod("requiredIfCategory", function(value, element) {
                                    const redirectType = $('#redirectType').val();
                                    if (redirectType === 'category') {
                                        return value && value !== '' && value !== null;
                                    }
                                    return true;
                                }, "Please select category");
                                
                                $.validator.addMethod("requiredIfProduct", function(value, element) {
                                    const redirectType = $('#redirectType').val();
                                    if (redirectType === 'product') {
                                        return value && value !== '' && value !== null;
                                    }
                                    return true;
                                }, "Product code is required");
                            }

                            $form.validate({
                                rules: {
                                    redirect_type: {
                                        required: true
                                    },
                                    category_id: {
                                        requiredIfCategory: true
                                    },
                                    product_code: {
                                        requiredIfProduct: true
                                    },
                                    title: {
                                        required: true
                                    },
                                    message: {
                                        required: true
                                    }
                                },
                                messages: {
                                    redirect_type: 'Redirect type is required.',
                                    category_id: 'Please select category',
                                    product_code: 'Product code is required',
                                    title: 'Title is required',
                                    message: 'Message is required'
                                },
                                errorElement: 'div',
                                errorClass: 'invalid-feedback',
                                highlight: function(el) {
                                    $(el).addClass('is-invalid').removeClass('is-valid');
                                },
                                unhighlight: function(el) {
                                    $(el).removeClass('is-invalid').addClass('is-valid');
                                },
                                errorPlacement: function(error, element) {
                                    error.insertAfter(element);
                                },
                                submitHandler: function(form) {
                                    submitNotificationForm(form);
                                }
                            });
                        }

                        // Handle redirect type change
                        $('#redirectType').on('change', function() {
                            const redirectType = $(this).val();
                            const $categoryField = $('#categoryField');
                            const $categoryId = $('#categoryId');
                            const $productCodeField = $('#productCodeField');
                            const $productCode = $('#productCode');
                            
                            // Hide all conditional fields first
                            $categoryField.hide();
                            $productCodeField.hide();
                            
                            // Clear values and remove validation errors
                            $categoryId.val('').removeClass('is-invalid is-valid');
                            $productCode.val('').removeClass('is-invalid is-valid');
                            $categoryId.next('.invalid-feedback').remove();
                            $productCode.next('.invalid-feedback').remove();
                            
                            // Show appropriate field based on redirect type
                            if (redirectType === 'category') {
                                $categoryField.show();
                            } else if (redirectType === 'product') {
                                $productCodeField.show();
                            }
                            // For 'other', no additional fields are shown
                        });

                        // Open modal for "Send To All"
                        $('#sendToAllBtn').on('click', function() {
                            isSendToAll = true;
                            
                            $('#deviceId').val('All').prop('readonly', true);
                            $('#redirectType').val('category');
                            $('#categoryId').val('');
                            $('#productCode').val('');
                            $('#notificationTitle').val('');
                            $('#notificationMessage').val('');
                            $('#categoryField').show();
                            $('#productCodeField').hide();
                            
                            // Reset validation state
                            $('#notificationForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                            $('#notificationForm').find('.invalid-feedback').remove();
                            
                            notificationModal.show();
                            
                            // Setup validation after modal is shown
                            setTimeout(function() {
                                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.validate !== 'undefined') {
                                    setupNotificationValidation();
                                } else {
                                    // Load jQuery Validate if not available
                                    loadjQueryValidate(function() {
                                        setupNotificationValidation();
                                    });
                                }
                            }, 100);
                        });

                        // Open modal for individual device
                        $(document).on('click', '.send-notification-btn', function(e) {
                            e.preventDefault();
                            isSendToAll = false;
                            
                            // Get device_id from table row
                            const row = table.row($(this).closest('tr'));
                            const rowData = row.data();
                            const deviceIdValue = rowData.device_id;
                            
                            // Set device ID in readonly field
                            $('#deviceId').val(deviceIdValue !== 'N/A' ? deviceIdValue : 'All').prop('readonly', true);
                            $('#redirectType').val('category');
                            $('#categoryId').val('');
                            $('#productCode').val('');
                            $('#notificationTitle').val('');
                            $('#notificationMessage').val('');
                            $('#categoryField').show();
                            $('#productCodeField').hide();
                            
                            // Reset validation state
                            $('#notificationForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                            $('#notificationForm').find('.invalid-feedback').remove();
                            
                            notificationModal.show();
                            
                            // Setup validation after modal is shown
                            setTimeout(function() {
                                if (typeof jQuery !== 'undefined' && typeof jQuery.fn.validate !== 'undefined') {
                                    setupNotificationValidation();
                                } else {
                                    // Load jQuery Validate if not available
                                    loadjQueryValidate(function() {
                                        setupNotificationValidation();
                                    });
                                }
                            }, 100);
                        });

                        // Submit Form
                        function submitNotificationForm(form) {
                            let deviceIdsToSend = [];
                            
                            if (isSendToAll) {
                                // Send to all - use 'All' to indicate all devices
                                deviceIdsToSend = ['All'];
                            } else {
                                // Send to single device
                                const deviceId = $('#deviceId').val();
                                if (deviceId && deviceId !== 'All') {
                                    deviceIdsToSend = [deviceId];
                                } else {
                                    deviceIdsToSend = ['All'];
                                }
                            }
                            
                            const formData = {
                                device_ids: deviceIdsToSend,
                                device_id: $('#deviceId').val(), // Keep for backward compatibility
                                redirect_type: $('#redirectType').val(),
                                category_id: $('#categoryId').val() || null,
                                product_code: $('#productCode').val() || null,
                                title: $('#notificationTitle').val(),
                                message: $('#notificationMessage').val()
                            };

                            const submitBtn = $(form).find('button[type="submit"]');
                            const originalText = submitBtn.html();
                            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Sending...');

                            AdminAjax.request(deviceBaseUrl + '/send-notification', 'POST', formData)
                                .then(res => {
                                    if (res.success) {
                                        showToast(res.message || 'Notification sent successfully', 'success');
                                        notificationModal.hide();
                                        $('#notificationForm')[0].reset();
                                        isSendToAll = false;
                                    } else {
                                        showToast(res.message || 'Failed to send notification.', 'error');
                                    }
                                    submitBtn.prop('disabled', false).html(originalText);
                                })
                                .catch(err => {
                                    showToast(err.message || 'Failed to send notification.', 'error');
                                    submitBtn.prop('disabled', false).html(originalText);
                                });
                        }

                        // Load jQuery Validate if not available
                        function loadjQueryValidate(callback) {
                            if (typeof jQuery !== 'undefined' && typeof jQuery.fn.validate !== 'undefined') {
                                if (callback) callback();
                                return;
                            }

                            const script = document.createElement('script');
                            script.src = 'https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js';
                            script.onload = function() {
                                if (callback) callback();
                            };
                            script.onerror = function() {
                                console.error('Failed to load jQuery Validate');
                            };
                            document.head.appendChild(script);
                        }

                        // Reset form when modal is closed
                        $('#notificationModal').on('hidden.bs.modal', function() {
                            $('#notificationForm')[0].reset();
                            $('#categoryField').hide();
                            $('#productCodeField').hide();
                            $('#notificationForm').find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                            $('#notificationForm').find('.invalid-feedback').remove();
                            if ($('#notificationForm').data('validator')) {
                                $('#notificationForm').data('validator', null);
                            }
                            isSendToAll = false;
                        });

                        function cleanupLoader() {
                            $('#deviceDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="deviceDataTableLoader" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0">
                                            <div class="modal-body text-center p-5">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-3 mb-0">Loading data...</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }

                        function showToast(message, type = 'success') {
                            let toastContainer = $('#global-toast-container');
                            if (!toastContainer.length) {
                                toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                                $('body').append(toastContainer);
                            }

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
                    });
                });
            }

            initDevicesDataTable();
        })();
    </script>
@endsection
