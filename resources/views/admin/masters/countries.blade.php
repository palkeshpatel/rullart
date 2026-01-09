@extends('layouts.vertical', ['title' => 'Countries List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Countries List'])

    <div class="row">
        <div class="col-12">
            <!-- Countries Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Countries List</h4>
                    <div class="d-flex gap-2">
                        <a href="javascript:void(0);" class="btn btn-success btn-sm update-currency-rate-btn">
                            <i class="ti ti-refresh me-1"></i> Update Currency Rate
                        </a>
                        <a href="javascript:void(0);" class="btn btn-success btn-sm add-country-btn">
                            <i class="ti ti-plus me-1"></i> Add Country
                        </a>
                    </div>
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
                                        placeholder="Search countries...">
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
                        <table id="countriesTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Country Name(EN)</th>
                                    <th>Country Name(AR)</th>
                                    <th>Currency Code</th>
                                    <th>Currency Rate</th>
                                    <th>Shipping Charge [QAR]</th>
                                    <th>Free Over</th>
                                    <th>ISO</th>
                                    <th>Is Active?</th>
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
    <div id="countryModalContainer"></div>
    <div id="countryViewModalContainer"></div>
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

            function initCountriesDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initCountriesDataTable, 50);
                    return;
                }

                const $ = jQuery;
                    const countryBaseUrl = '{{ url("/admin/countries") }}';
                    let deleteCountryId = null;

                $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('countryDataTableLoader');
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
                            $('#countryDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#countriesTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip', // Hide default search (f) and length menu (l), show only table, info, pagination
                            ajax: {
                                url: countryBaseUrl,
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
                                    data: 'countryname',
                                    name: 'countryname'
                                },
                                {
                                    data: 'countrynameAR',
                                    name: 'countrynameAR'
                                },
                                {
                                    data: 'currencycode',
                                    name: 'currencycode'
                                },
                                {
                                    data: 'currencyrate',
                                    name: 'currencyrate'
                                },
                                {
                                    data: 'shipping_charge',
                                    name: 'shipping_charge'
                                },
                                {
                                    data: 'free_shipping_over',
                                    name: 'free_shipping_over'
                                },
                                {
                                    data: 'isocode',
                                    name: 'isocode'
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
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-country-btn" data-country-id="' +
                                            row.action + '" title="View">';
                                        html += '<i class="ti ti-eye fs-lg"></i></a>';
                                        html +=
                                            '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-country-btn" data-country-id="' +
                                            row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
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
                                [8, 'asc']  // Order by countryid (column index 8) ascending
                            ],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No countries found",
                                zeroRecords: "No matching countries found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 1, 8]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3, 4]
                                },
                                {
                                    responsivePriority: 3,
                                    targets: [5, 6, 7]
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

                        // 📄 Per Page Select Handler
                        $('#perPageSelect').on('change', function() {
                            showLoader();
                            table.page.len(parseInt($(this).val())).draw();
                        });

                        // View/Edit/Add/Delete handlers
                        $(document).on('click', '.view-country-btn', function(e) {
                        e.preventDefault();
                            const countryId = $(this).data('country-id');
                            openCountryViewModal(countryId);
                    });

                    $(document).on('click', '.edit-country-btn', function(e) {
                        e.preventDefault();
                        const countryId = $(this).data('country-id');
                        openCountryFormModal(countryId);
                    });

                        $(document).on('click', '.add-country-btn', function(e) {
                        e.preventDefault();
                            openCountryFormModal();
                    });

                        $(document).on('click', '.update-currency-rate-btn', function(e) {
                            e.preventDefault();
                            const btn = $(this);
                            const originalText = btn.html();
                            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');
                            
                            AdminAjax.request('{{ route("admin.countries.update-currency-rate") }}', 'POST')
                                .then(res => {
                                    showToast(res.message || 'Currency rates update initiated successfully', 'success');
                                    btn.prop('disabled', false).html(originalText);
                                    // Optionally reload the table to show updated rates
                                    setTimeout(() => {
                                        showLoader();
                                        table.ajax.reload();
                                    }, 1000);
                                })
                                .catch(err => {
                                    showToast(err.message || 'Failed to update currency rates.', 'error');
                                    btn.prop('disabled', false).html(originalText);
                                });
                        });


                        // Modal functions
                        function openCountryViewModal(countryId) {
                            cleanupModals();
                            const url = countryBaseUrl + '/' + countryId;
                        $('#countryViewModalContainer').html('<div class="text-center p-4"><div class="spinner-border"></div></div>');
                        // Use a temporary loader modal
                        const tempLoader = $('<div class="modal fade" id="tempCountryLoader" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center p-4"><div class="spinner-border"></div></div></div></div></div>');
                        $('body').append(tempLoader);
                        const loadingModal = new bootstrap.Modal(tempLoader[0], {
                            backdrop: 'static',
                            keyboard: false
                        });
                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            tempLoader.remove();
                            cleanupModals();
                            $('#countryViewModalContainer').html(response.html);
                            const modalEl = document.getElementById('countryViewModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();

                                modalEl.addEventListener('hidden.bs.modal', function() {
                                cleanupModals();
                                }, { once: true });
                            }).catch(err => {
                                loadingModal.hide();
                                tempLoader.remove();
                                cleanupModals();
                                AdminAjax.showError('Failed to load country details.');
                            });
                        }

                        function openCountryFormModal(countryId = null) {
                            cleanupModals();
                            const url = countryId ? countryBaseUrl + '/' + countryId + '/edit' : countryBaseUrl + '/create';
                        $('#countryModalContainer').html('<div class="text-center p-4"><div class="spinner-border"></div></div>');
                        // Use a temporary loader modal
                        const tempLoader = $('<div class="modal fade" id="tempCountryFormLoader" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-body text-center p-4"><div class="spinner-border"></div></div></div></div></div>');
                        $('body').append(tempLoader);
                        const loadingModal = new bootstrap.Modal(tempLoader[0], {
                            backdrop: 'static',
                            keyboard: false
                        });
                        loadingModal.show();

                        AdminAjax.get(url).then(response => {
                            loadingModal.hide();
                            tempLoader.remove();
                            cleanupModals();
                            $('#countryModalContainer').html(response.html);
                            const modalEl = document.getElementById('countryModal');
                            const modal = new bootstrap.Modal(modalEl);
                            modal.show();
                            setupCountryValidation(countryId, modal);
                        }).catch(err => {
                            loadingModal.hide();
                            tempLoader.remove();
                            cleanupModals();
                        });
                    }

                    function setupCountryValidation(countryId, modal) {
                        const $form = $('#countryForm');
                            if (!$form.length || $form.data('validator')) {
                            return;
                        }

                        $form.validate({
                            rules: {
                                    countryname: { required: true },
                                    countrynameAR: { required: true },
                                    currencycode: { required: true },
                                    currencyrate: { required: true, number: true }
                            },
                            messages: {
                                    countryname: 'Country Name (EN) is required.',
                                    countrynameAR: 'Country Name (AR) is required.',
                                    currencycode: 'Currency Code is required.',
                                    currencyrate: { 
                                        required: 'Currency Rate is required.',
                                        number: 'Currency Rate must be a number.'
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
                                submitCountryForm(form, countryId, modal);
                            }
                        });
                    }

                    function submitCountryForm(form, countryId, modal) {
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                    showToast(res.message || 'Country saved successfully', 'success');
                                setTimeout(() => {
                                    modal.hide();
                                }, 1500);
                                    showLoader();
                                    table.ajax.reload();
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to save country.';
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
                        $('#countryModal').remove();
                            $('#countryViewModal').remove();
                    }

                    function loaderHtml() {
                        return `
        <div class="modal fade" id="countryDataTableLoader" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center p-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Loading countries...</p>
                    </div>
                </div>
            </div>
        </div>`;
                    }
                    });
                });
            }

            // Start initialization
            initCountriesDataTable();
        })();
    </script>
@endsection
