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
                        <button type="button" class="btn btn-sm btn-primary">
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
                                    <th>Select</th>
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
                                        return '<input type="checkbox" class="form-check-input" value="' + row.action + '">';
                                    }
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        return '<button type="button" class="btn btn-sm btn-primary" title="Send Notification"><i class="ti ti-send"></i> Send Notification</button>';
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
                                    targets: [0, 1, 7]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [2, 3, 4, 5, 6]
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
                    });
                });
            }

            initDevicesDataTable();
        })();
    </script>
@endsection
