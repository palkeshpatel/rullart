@extends('layouts.vertical', ['title' => 'Shopping Cart Not Complete Payment'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Shopping Cart not complete payment'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.orders-not-process') }}" data-table-filters id="cartsFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
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

            <!-- Carts Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Shopping Cart Not Complete Payment</h4>
                    <a href="{{ route('admin.orders-not-process.export', ['country' => request('country'), 'search' => request('search')]) }}" 
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
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search cart..." value="{{ request('search') }}">
                                    <i data-lucide="search" class="app-search-icon text-muted"></i>
                                </div>
                                <div class="d-flex align-items-center">
                                    <label class="mb-0 me-2">Show
                                        <select class="form-select form-select-sm d-inline-block" style="width: auto;"
                                            id="perPageSelect">
                                            @php
                                                $currentPerPage = request('per_page', 25);
                                            @endphp
                                            <option value="25" {{ $currentPerPage == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100
                                            </option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.orders-not-process.partials.table', ['carts' => $carts])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $carts])
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
                tableSelector: '#cartsTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.orders-not-process') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    // Update pagination if provided
                    if (response.pagination) {
                        const paginationContainer = document.querySelector('.pagination-container');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = response.pagination;
                        }
                    }
                }
            });

            // Per page change handler
            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const form = document.getElementById('cartsFilterForm');
                const formData = new FormData(form);
                formData.set('per_page', this.value);
                formData.delete('page'); // Reset to page 1 when changing per_page

                const params = new URLSearchParams();
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });

                AdminAjax.loadTable('{{ route('admin.orders-not-process') }}', document.querySelector(
                    '.table-container'), {
                    params: Object.fromEntries(params),
                    onSuccess: function(response) {
                        // Update pagination if provided
                        if (response.pagination) {
                            const paginationContainer = document.querySelector(
                                '.pagination-container');
                            if (paginationContainer) {
                                paginationContainer.innerHTML = response.pagination;
                            }
                        }
                        // Re-initialize view and delete buttons after table reload
                        initCartActions();
                    }
                });
            });

            // Initialize view and delete buttons
            function initCartActions() {
                // View cart button
                document.querySelectorAll('.view-cart-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const cartId = this.getAttribute('data-cart-id');
                        loadCartView(cartId);
                    });
                });

                // Delete cart button
                document.querySelectorAll('.delete-cart-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const cartId = this.getAttribute('data-cart-id');
                        deleteCart(cartId);
                    });
                });
            }

            // Load cart view in modal
            function loadCartView(cartId) {
                fetch(`{{ route('admin.orders-not-process.show', ':id') }}`.replace(':id', cartId), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create modal if it doesn't exist
                        let modalContainer = document.getElementById('cartViewModalContainer');
                        if (!modalContainer) {
                            modalContainer = document.createElement('div');
                            modalContainer.id = 'cartViewModalContainer';
                            document.body.appendChild(modalContainer);
                        }
                        modalContainer.innerHTML = data.html;
                        
                        // Show modal
                        const modal = new bootstrap.Modal(document.getElementById('cartViewModal'));
                        modal.show();
                    }
                })
                .catch(error => {
                    console.error('Error loading cart view:', error);
                    alert('Error loading cart details');
                });
            }

            // Show confirmation modal
            function showConfirmModal(message, onConfirm) {
                // Create or get modal container
                let modalContainer = document.getElementById('confirmModalContainer');
                if (!modalContainer) {
                    modalContainer = document.createElement('div');
                    modalContainer.id = 'confirmModalContainer';
                    document.body.appendChild(modalContainer);
                }

                // Create modal HTML
                modalContainer.innerHTML = `
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header border-0 pb-0">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center px-4 pb-4">
                                    <div class="mb-3">
                                        <i class="ti ti-alert-triangle text-danger" style="font-size: 48px;"></i>
                                    </div>
                                    <h5 class="modal-title mb-3" id="confirmDeleteModalLabel">Confirm Delete</h5>
                                    <p class="text-muted mb-0">${message}</p>
                                </div>
                                <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                modal.show();

                // Handle confirm button click
                document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
                    modal.hide();
                    if (onConfirm) {
                        onConfirm();
                    }
                });

                // Clean up when modal is hidden
                document.getElementById('confirmDeleteModal').addEventListener('hidden.bs.modal', function() {
                    modalContainer.innerHTML = '';
                });
            }

            // Delete cart
            function deleteCart(cartId) {
                showConfirmModal('Are you sure you want to delete this cart? This action cannot be undone.', function() {
                    fetch(`{{ route('admin.orders-not-process.destroy', ':id') }}`.replace(':id', cartId), {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Reload table
                            AdminAjax.loadTable('{{ route('admin.orders-not-process') }}', document.querySelector('.table-container'), {
                                onSuccess: function(response) {
                                    if (response.pagination) {
                                        const paginationContainer = document.querySelector('.pagination-container');
                                        if (paginationContainer) {
                                            paginationContainer.innerHTML = response.pagination;
                                        }
                                    }
                                    initCartActions();
                                }
                            });
                        } else {
                            alert(data.message || 'Error deleting cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting cart:', error);
                        alert('Error deleting cart');
                    });
                });
            }

            // Initialize on page load
            initCartActions();
        });
    </script>
@endsection

