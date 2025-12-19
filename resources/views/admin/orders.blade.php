@extends('layouts.vertical', ['title' => 'Orders List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Orders List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.orders') }}" data-table-filters id="ordersFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Status</label>
                                <select name="status" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Status--</option>
                                    <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Process</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pending</option>
                                    <option value="7" {{ request('status') == '7' ? 'selected' : '' }}>Delivered
                                    </option>
                                    <option value="8" {{ request('status') == '8' ? 'selected' : '' }}>Cancelled
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Country</label>
                                <select name="country" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Country--</option>
                                    @foreach ($countries ?? [] as $country)
                                        <option value="{{ $country }}"
                                            {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Orders Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Orders List</h4>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-end align-items-center">
                                <label class="mb-0">Show
                                    <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                        id="perPageSelect">
                                        @php
                                            $currentPerPage = request('per_page', 25);
                                        @endphp
                                        <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                                    </select> entries
                                </label>
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">Search:</span>
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.partials.orders-table', ['orders' => $orders])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $orders])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AJAX data table
            AdminAjax.initDataTable({
                tableSelector: '#ordersTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.orders') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    initOrderRowToggles();
                    initOrderStatusHandlers();

                    // Update pagination if provided
                    if (response.pagination) {
                        const paginationContainer = document.querySelector('.pagination-container');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = response.pagination;
                        }
                    }

                    // Update per_page select to match current value from response
                    // Extract per_page from the pagination HTML or use current form value
                    const perPageSelect = document.getElementById('perPageSelect');
                    if (perPageSelect) {
                        // Try to get per_page from URL params in pagination links
                        const paginationLinks = document.querySelectorAll('.pagination a');
                        if (paginationLinks.length > 0) {
                            const firstLink = paginationLinks[0].href;
                            const urlParams = new URLSearchParams(firstLink.split('?')[1] || '');
                            const perPage = urlParams.get('per_page');
                            if (perPage && perPageSelect.value !== perPage) {
                                perPageSelect.value = perPage;
                            }
                        }
                    }
                }
            });

            // Per page change handler
            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const form = document.getElementById('ordersFilterForm');
                const formData = new FormData(form);
                formData.set('per_page', this.value);
                formData.delete('page'); // Reset to page 1 when changing per_page

                const params = new URLSearchParams();
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });

                AdminAjax.loadTable('{{ route('admin.orders') }}', document.querySelector(
                    '.table-container'), {
                    params: Object.fromEntries(params),
                    onSuccess: function(response) {
                        initOrderRowToggles();
                        initOrderStatusHandlers();

                        // Update pagination if provided
                        if (response.pagination) {
                            const paginationContainer = document.querySelector(
                                '.pagination-container');
                            if (paginationContainer) {
                                paginationContainer.innerHTML = response.pagination;
                            }
                        }
                    }
                });
            });

            // Initialize order row toggle functionality
            function initOrderRowToggles() {
                // Remove old event listeners by cloning nodes
                document.querySelectorAll('.toggle-order-details').forEach(toggle => {
                    const newToggle = toggle.cloneNode(true);
                    toggle.parentNode.replaceChild(newToggle, toggle);
                });

                // Add fresh event listeners
                document.querySelectorAll('.toggle-order-details').forEach(toggle => {
                    toggle.addEventListener('click', function(e) {
                        e.preventDefault();
                        const orderId = this.dataset.orderId;
                        const detailsRow = document.querySelector(
                            `tr.order-details-row[data-order-id="${orderId}"]`);
                        const icon = this.querySelector('.toggle-icon');

                        if (detailsRow) {
                            if (detailsRow.style.display === 'none') {
                                detailsRow.style.display = '';
                                icon.classList.remove('fa-plus-circle');
                                icon.classList.add('fa-minus-circle');
                                icon.classList.remove('text-success');
                                icon.classList.add('text-danger');
                            } else {
                                detailsRow.style.display = 'none';
                                icon.classList.remove('fa-minus-circle');
                                icon.classList.add('fa-plus-circle');
                                icon.classList.remove('text-danger');
                                icon.classList.add('text-success');
                            }
                        }
                    });
                });
            }

            // Initialize on page load
            initOrderRowToggles();

            // Order status change handler (use delegation for dynamic content)
            function initOrderStatusHandlers() {
                document.querySelectorAll('.order-status').forEach(select => {
                    // Remove old listeners
                    const newSelect = select.cloneNode(true);
                    select.parentNode.replaceChild(newSelect, select);
                });

                document.querySelectorAll('.order-status').forEach(select => {
                    select.addEventListener('change', function() {
                        const orderId = this.dataset.orderId;
                        const status = this.value;
                        const originalValue = this.value;

                        // Update order status via AJAX
                        AdminAjax.post('{{ route('admin.orders') }}/' + orderId + '/status', {
                                status: status
                            })
                            .then(response => {
                                AdminAjax.showSuccess('Order status updated successfully!');
                            })
                            .catch(error => {
                                AdminAjax.showError('Failed to update order status.');
                                this.value = originalValue; // Revert
                            });
                    });
                });
            }

            // Initialize order status handlers
            initOrderStatusHandlers();
        });
    </script>
@endsection
