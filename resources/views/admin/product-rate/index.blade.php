@extends('layouts.vertical', ['title' => 'Product Reviews'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Product Reviews'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section -->
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1">Rating</label>
                            <select id="ratingFilter" class="form-select form-select-sm">
                                <option value="">--All Ratings--</option>
                                <option value="5">5 Stars</option>
                                <option value="4">4 Stars</option>
                                <option value="3">3 Stars</option>
                                <option value="2">2 Stars</option>
                                <option value="1">1 Star</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label mb-1">Published</label>
                            <select id="publishedFilter" class="form-select form-select-sm">
                                <option value="">--All--</option>
                                <option value="1">Published</option>
                                <option value="0">Unpublished</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ratings Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Product Reviews</h4>
                    <a href="{{ url('/admin/productrate/export') }}" 
                        class="btn btn-success btn-sm" title="Export to Excel">
                        <i class="ti ti-file-excel me-1"></i> Export
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" id="searchBox" class="form-control form-control-sm"
                                        placeholder="Search reviews...">
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
                        <table id="ratingsTable" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    <th>Date</th>
                                    <th>Status</th>
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

            <!-- Edit Rating Modal -->
            <div class="modal fade" id="editRatingModal" tabindex="-1" aria-labelledby="editRatingModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editRatingModalLabel">Edit Review</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="editRatingForm">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="editRating" class="form-label">Rating</label>
                                    <input type="number" class="form-control" id="editRating" name="rate" min="1" max="5" required>
                                </div>
                                <div class="mb-3">
                                    <label for="editReview" class="form-label">Review</label>
                                    <textarea class="form-control" id="editReview" name="review" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="editIsPublished" name="ispublished" value="1">
                                        <label class="form-check-label" for="editIsPublished">
                                            Is Published?
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
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

            function initRatingsDataTable() {
                loadDataTables(function() {
                    if (typeof jQuery === 'undefined' || typeof jQuery.fn.DataTable === 'undefined') {
                        setTimeout(initRatingsDataTable, 50);
                        return;
                    }

                    const $ = jQuery;
                    const ratingBaseUrl = '{{ url("/admin/productrate") }}';

                    $(document).ready(function() {
                        let loadingModal = null;
                        
                        function showLoader() {
                            if (!loadingModal) {
                                $('body').append(loaderHtml());
                                const modalEl = document.getElementById('ratingDataTableLoader');
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
                            $('#ratingDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        let isFirstDraw = true;
                        showLoader();
                        
                        let table = $('#ratingsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip',
                            ajax: {
                                url: ratingBaseUrl,
                                type: 'GET',
                                data: function(d) {
                                    d.rating = $('#ratingFilter').val();
                                    d.published = $('#publishedFilter').val();
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
                                    data: 'product',
                                    name: 'product',
                                    render: function(data, type, row) {
                                        if (!data || !data.title) return 'N/A';
                                        let html = '<div class="d-flex align-items-center">';
                                        if (data.photo) {
                                            html += '<img src="{{ asset("storage") }}/' + data.photo + '" alt="' + data.title + '" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">';
                                        }
                                        html += '<div class="fw-semibold">' + data.title + '</div></div>';
                                        return html;
                                    }
                                },
                                {
                                    data: 'rate',
                                    name: 'rate',
                                    render: function(data) {
                                        let html = '<div class="d-flex align-items-center">';
                                        for (let i = 1; i <= 5; i++) {
                                            html += '<i class="ti ti-star' + (i <= data ? '-filled' : '') + ' text-warning"></i>';
                                        }
                                        html += '<span class="ms-1">(' + data + ')</span></div>';
                                        return html;
                                    }
                                },
                                {
                                    data: 'review',
                                    name: 'review',
                                    render: function(data) {
                                        return '<div class="text-truncate" style="max-width: 200px;" title="' + data + '">' + data + '</div>';
                                    }
                                },
                                {
                                    data: 'submiton',
                                    name: 'submiton'
                                },
                                {
                                    data: 'ispublished',
                                    name: 'ispublished',
                                    render: function(data) {
                                        const badgeClass = data === 'Published' ? 'badge-soft-success' : 'badge-soft-warning';
                                        return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                                    }
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false,
                                    render: function(data, type, row) {
                                        let html = '<div class="d-flex gap-1">';
                                        html += '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-rating-btn" data-rating-id="' + row.action + '" title="Edit">';
                                        html += '<i class="ti ti-edit fs-lg"></i></a>';
                                        html += '<a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-rating-btn" data-rating-id="' + row.action + '" title="Delete">';
                                        html += '<i class="ti ti-trash fs-lg"></i></a>';
                                        html += '</div>';
                                        return html;
                                    }
                                }
                            ],
                            pageLength: 25,
                            lengthMenu: [[25, 50, 100], [25, 50, 100]],
                            order: [[3, 'desc']],
                            language: {
                                processing: '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>',
                                emptyTable: "No product reviews found",
                                zeroRecords: "No matching product reviews found"
                            },
                            responsive: true,
                            columnDefs: [{
                                    responsivePriority: 1,
                                    targets: [0, 5]
                                },
                                {
                                    responsivePriority: 2,
                                    targets: [1, 2, 3, 4]
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

                        $('#ratingFilter, #publishedFilter').on('change', function() {
                            showLoader();
                            table.ajax.reload();
                        });

                        // Edit Rating Modal
                        const editModal = new bootstrap.Modal(document.getElementById('editRatingModal'));
                        let currentRatingId = null;

                        $(document).on('click', '.edit-rating-btn', function(e) {
                            e.preventDefault();
                            const ratingId = $(this).data('rating-id');
                            currentRatingId = ratingId;

                            // Fetch rating data
                            AdminAjax.request(ratingBaseUrl + '/' + ratingId + '/edit', 'GET')
                                .then(res => {
                                    if (res.success && res.data) {
                                        $('#editRating').val(res.data.rate);
                                        $('#editReview').val(res.data.review || '');
                                        $('#editIsPublished').prop('checked', res.data.ispublished == 1 || res.data.ispublished === true);
                                        editModal.show();
                                    } else {
                                        showToast('Failed to load rating data.', 'error');
                                    }
                                })
                                .catch(err => {
                                    showToast(err.message || 'Failed to load rating data.', 'error');
                                });
                        });

                        // Handle form submission
                        $('#editRatingForm').on('submit', function(e) {
                            e.preventDefault();
                            
                            if (!currentRatingId) {
                                showToast('Rating ID not found.', 'error');
                                return;
                            }

                            const formData = {
                                _method: 'PUT',
                                rate: $('#editRating').val(),
                                review: $('#editReview').val(),
                                ispublished: $('#editIsPublished').is(':checked') ? 1 : 0
                            };

                            AdminAjax.request(ratingBaseUrl + '/' + currentRatingId, 'PUT', formData)
                                .then(res => {
                                    if (res.success) {
                                        showToast('Rating updated successfully', 'success');
                                        editModal.hide();
                                        showLoader();
                                        table.ajax.reload(function() {
                                            hideLoader();
                                        }, false);
                                    } else {
                                        showToast(res.message || 'Failed to update rating.', 'error');
                                    }
                                })
                                .catch(err => {
                                    showToast(err.message || 'Failed to update rating.', 'error');
                                });
                        });

                        // Reset form when modal is closed
                        $('#editRatingModal').on('hidden.bs.modal', function() {
                            currentRatingId = null;
                            $('#editRatingForm')[0].reset();
                        });

                        $(document).on('click', '.delete-rating-btn', function(e) {
                            e.preventDefault();
                            if (confirm('Are you sure you want to delete this rating?')) {
                                const ratingId = $(this).data('rating-id');
                                const currentPage = table.page();
                                const totalPages = table.page.info().pages;
                                
                                AdminAjax.request(ratingBaseUrl + '/' + ratingId, 'DELETE')
                                    .then(res => {
                                        if (res.success) {
                                            showToast('Rating deleted successfully', 'success');
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
                                        } else {
                                            showToast(res.message || 'Failed to delete rating.', 'error');
                                        }
                                    })
                                    .catch(err => {
                                        showToast(err.message || 'Failed to delete rating.', 'error');
                                    });
                            }
                        });

                        function cleanupLoader() {
                            $('#ratingDataTableLoader').remove();
                            $('.modal-backdrop').remove();
                            $('body').removeClass('modal-open').css({
                                overflow: '',
                                paddingRight: ''
                            });
                            loadingModal = null;
                        }

                        function loaderHtml() {
                            return `
                                <div class="modal fade" id="ratingDataTableLoader" tabindex="-1" aria-hidden="true">
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

            initRatingsDataTable();
        })();
    </script>
@endsection
