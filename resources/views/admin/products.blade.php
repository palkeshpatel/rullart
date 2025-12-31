@extends('layouts.vertical', ['title' => 'Products List'])

@php
    // Redirect to new index view
    header('Location: ' . route('admin.products'));
    exit;

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Products List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.products') }}" data-table-filters id="productsFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Category</label>
                                <select name="category" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Categories--</option>
                                    @foreach($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}" {{ request('category') == $cat->categoryid ? 'selected' : '' }}>{{ $cat->category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Published</label>
                                <select name="published" class="form-select form-select-sm" data-filter>
                                    <option value="">--All--</option>
                                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Products Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Products List</h4>
                    <div class="d-flex gap-2">
                        <a href="javascript:void(0);" class="btn btn-success btn-sm" title="Export to Excel">
                            <i class="ti ti-file-excel me-1"></i> Export Products
                        </a>
                        <a href="javascript:void(0);" class="btn btn-success btn-sm">
                            <i class="ti ti-plus me-1"></i> Add Product
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search..." value="{{ request('search') }}">
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
                        @include('admin.partials.products-table', ['products' => $products])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $products])
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
                tableSelector: '#productsTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.products') }}',
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
                const form = document.getElementById('productsFilterForm');
                const formData = new FormData(form);
                formData.set('per_page', this.value);
                formData.delete('page'); // Reset to page 1 when changing per_page

                const params = new URLSearchParams();
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });

                AdminAjax.loadTable('{{ route('admin.products') }}', document.querySelector(
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
                    }
                });
            });
        });
    </script>
@endsection

