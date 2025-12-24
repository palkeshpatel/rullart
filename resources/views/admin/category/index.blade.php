@extends('layouts.vertical', ['title' => 'Category List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Category List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.category') }}" data-table-filters id="categoryFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category:</label>
                                <select name="parent_category" class="form-select form-select-sm" data-filter>
                                    <option value="">--Parent--</option>
                                    <option value="0" {{ request('parent_category') == '0' ? 'selected' : '' }}>No Parent (Main Categories)</option>
                                    @foreach ($parentCategories ?? [] as $parent)
                                        <option value="{{ $parent->categoryid }}"
                                            {{ request('parent_category') == $parent->categoryid ? 'selected' : '' }}>{{ $parent->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Category Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Category List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm">
                        <i class="ti ti-plus me-1"></i> Add category
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search category..." value="{{ request('search') }}">
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
                        @include('admin.partials.categories-table', ['categories' => $categories])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $categories])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category View Modal Container -->
    <div id="categoryViewModalContainer"></div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AJAX data table
            AdminAjax.initDataTable({
                tableSelector: '#categoriesTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.category') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    // Update pagination if provided
                    if (response.pagination) {
                        const paginationContainer = document.querySelector('.pagination-container');
                        if (paginationContainer) {
                            paginationContainer.innerHTML = response.pagination;
                        }
                    }
                    // Re-initialize view buttons after table reload
                    initCategoryViewModal();
                }
            });

            // Per page change handler
            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const form = document.getElementById('categoryFilterForm');
                const formData = new FormData(form || document.createElement('form'));
                formData.set('per_page', this.value);
                formData.delete('page'); // Reset to page 1 when changing per_page

                const params = new URLSearchParams();
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });

                AdminAjax.loadTable('{{ route('admin.category') }}', document.querySelector(
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
                        initCategoryViewModal();
                    }
                });
            });

            // Initialize view category modal
            function initCategoryViewModal() {
                // Use event delegation for dynamically loaded content
                document.addEventListener('click', function(e) {
                    const viewBtn = e.target.closest('.view-category-btn');
                    if (viewBtn) {
                        e.preventDefault();
                        const categoryId = viewBtn.dataset.categoryId;
                        openCategoryModal(categoryId);
                    }
                });
            }

            function openCategoryModal(categoryId) {
                const modalContainer = document.getElementById('categoryViewModalContainer');
                
                // Show loading state
                modalContainer.innerHTML = '<div class="modal fade" id="categoryViewModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-body"><div class="text-center p-4"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div></div></div></div></div>';
                
                // Show loading modal
                const loadingModal = new bootstrap.Modal(document.getElementById('categoryViewModal'));
                loadingModal.show();

                // Fetch category data via AJAX
                AdminAjax.get('{{ route("admin.category") }}/' + categoryId)
                    .then(response => {
                        if (response.html) {
                            // Close loading modal
                            loadingModal.hide();
                            
                            // Update modal container with actual content
                            modalContainer.innerHTML = response.html;
                            
                            // Show modal using Bootstrap
                            const modal = document.getElementById('categoryViewModal');
                            const bsModal = new bootstrap.Modal(modal);
                            bsModal.show();
                            
                            // Clean up when modal is hidden
                            modal.addEventListener('hidden.bs.modal', function() {
                                modalContainer.innerHTML = '';
                            }, { once: true });
                        }
                    })
                    .catch(error => {
                        loadingModal.hide();
                        console.error('Error loading category:', error);
                        AdminAjax.showError('Failed to load category details.');
                        modalContainer.innerHTML = '';
                    });
            }

            // Initialize on page load
            initCategoryViewModal();
        });
    </script>
@endsection
