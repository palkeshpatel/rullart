@extends('layouts.vertical', ['title' => 'Customers List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Customers List'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Customers List</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ url('/admin/customers/export') }}" class="btn btn-success btn-sm" title="Export to Excel" id="exportBtn">
                            <i class="ti ti-file-excel me-1"></i> Export
                        </a>
                        @unless(\App\Helpers\ViewHelper::isView('customers'))
                            <a href="javascript:void(0);" class="btn btn-primary btn-sm add-customer-btn">
                                <i class="ti ti-plus me-1"></i> Add Customer
                            </a>
                        @endunless
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search customer...">
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
                        <table id="customersTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Email</th>
                                    <th>Site</th>
                                    <th>Login Type</th>
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

    <!-- Modal Container -->
    <div id="customerModalContainer"></div>
    <div id="customerViewModalContainer"></div>
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

            function initCustomersDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initCustomersDataTable, 50);
                        return;
                    }

                    const $ = jQuery;

                    // Base URLs for actions (use URL instead of route helper)
                    const customerBaseUrl = '{{ url("/admin/customers") }}';

                    $(document).ready(function() {
                        // Loader modal for AJAX calls
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('customerDataTableLoader');
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
                            $('#customerDataTableLoader').remove();
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
                        
                        let table = $('#customersTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: customerBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    // Show loader on pagination/sorting changes (but not on first request)
                                    if (!isFirstDraw) {
                                        showLoader();
                                    }
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
                                    data: 'firstname',
                                    name: 'firstname'
                                },
                                {
                                    data: 'lastname',
                                    name: 'lastname'
                                },
                                {
                                    data: 'email',
                                    name: 'email'
                                },
                                {
                                    data: 'site',
                                    name: 'site'
                                },
                                {
                                    data: 'login_type',
                                    name: 'login_type'
                                },
                                {
                                    data: 'isactive',
                                    name: 'isactive'
                                },
                                {
                                    data: 'last_login',
                                    name: 'last_login'
                                },
                                {
                                    data: 'regdate',
                                    name: 'regdate'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-customer-btn" data-customer-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
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
                                [2, 'desc']
                            ], // Default sort by Reg. Date desc
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No customers found",
                                zeroRecords: "No matching customers found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 3] // Name, Email, Action - always visible
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2] // Reg. Date
                                }
                            ]
                        });

                        // External Search
                        $('#searchBox').on('keyup', function() {
                            showLoader();
                            table.search(this.value).draw();
                        });

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // Update export button with current search
                        $('#searchBox').on('keyup', function() {
                            const searchValue = $(this).val();
                            const exportUrl = '{{ url("/admin/customers/export") }}' + (searchValue ? '?search=' + encodeURIComponent(searchValue) : '');
                            $('#exportBtn').attr('href', exportUrl);
                        });

                        // View Customer Button
                        $(document).on('click', '.view-customer-btn', function(e) {
                            e.preventDefault();
                            const customerId = $(this).data('customer-id');
                            openCustomerViewModal(customerId);
                        });

                        // Add Customer Button
                        $(document).on('click', '.add-customer-btn', function(e) {
                            e.preventDefault();
                            openCustomerFormModal();
                        });

                        // Open View Modal
                        function openCustomerViewModal(customerId) {
                            cleanupModals();
                            const url = customerBaseUrl + '/' + customerId;
                            $('#customerViewModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#customerModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#customerViewModalContainer').html(response.html);
                                const modalEl = document.getElementById('customerViewModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();

                                modalEl.addEventListener('hidden.bs.modal', function() {
                                    cleanupModals();
                                }, { once: true });
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                                AdminAjax.showError('Failed to load customer details.');
                            });
                        }

                        // Open Form Modal
                        function openCustomerFormModal(customerId = null) {
                            cleanupModals();
                            const url = customerId ? customerBaseUrl + '/' + customerId + '/edit' : customerBaseUrl + '/create';
                            $('#customerModalContainer').html(loaderHtml());
                            const loadingModal = new bootstrap.Modal($('#customerModal')[0], {
                                backdrop: 'static',
                                keyboard: false
                            });
                            loadingModal.show();

                            AdminAjax.get(url).then(response => {
                                loadingModal.hide();
                                cleanupModals();
                                $('#customerModalContainer').html(response.html);
                                const modalEl = document.getElementById('customerModal');
                                const modal = new bootstrap.Modal(modalEl);
                                modal.show();
                                setupCustomerValidation(customerId, modal);
                            }).catch(err => {
                                loadingModal.hide();
                                cleanupModals();
                            });
                        }

                        // Validation Setup
                        function setupCustomerValidation(customerId, modal) {
                            const $form = $('#customerForm');
                            if (!$form.length || $form.data('validator')) {
                                return;
                            }

                            $form.validate({
                                rules: {
                                    firstname: { required: true },
                                    email: { required: true, email: true },
                                    password: { required: !customerId, minlength: 6 }
                                },
                                messages: {
                                    firstname: 'First name is required',
                                    email: {
                                        required: 'Email is required',
                                        email: 'Please enter a valid email address'
                                    },
                                    password: {
                                        required: 'Password is required',
                                        minlength: 'Password must be at least 6 characters'
                                    }
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
                                    submitCustomerForm(form, customerId, modal);
                                }
                            });
                        }

                        // Submit Form
                        function submitCustomerForm(form, customerId, modal) {
                            const formData = new FormData(form);
                            const url = form.action;
                            const method = form.querySelector('[name="_method"]')?.value || 'POST';
                            const submitBtn = form.querySelector('button[type="submit"]');
                            const originalText = submitBtn.innerHTML;
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                            AdminAjax.request(url, method, formData)
                                .then(res => {
                                    showToast(res.message || 'Customer saved successfully', 'success');
                                    setTimeout(() => {
                                        modal.hide();
                                    }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                                })
                                .catch(err => {
                                    let errorMessage = 'Failed to save customer.';
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
                            $('#customerModal').remove();
                            $('#customerViewModal').remove();
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="customerDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-body text-center p-4">
                                                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <h5 class="mt-3 mb-1">Loading Customers...</h5>
                                                <p class="text-muted mb-0">Please wait while we fetch the data.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal fade" id="customerModal">
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

            initCustomersDataTable();
        })();
    </script>
@endsection
