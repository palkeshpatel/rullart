@extends('layouts.vertical', ['title' => 'Coupon Codes List'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Coupon Codes List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section -->
            <form method="GET" action="{{ route('admin.coupon-code') }}" data-table-filters id="couponCodeFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Active:</label>
                                <select name="active" class="form-select form-select-sm" data-filter>
                                    <option value="">All</option>
                                    <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Coupon Codes Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Coupon Codes List</h4>
                    <a href="javascript:void(0);" class="btn btn-success btn-sm">
                        <i class="ti ti-plus me-1"></i> Add Coupon Code
                    </a>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search coupon codes..." value="{{ request('search') }}">
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
                                            <option value="50" {{ $currentPerPage == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ $currentPerPage == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table Container -->
                    <div class="table-container">
                        @include('admin.masters.partials.coupon-code-table', ['couponCodes' => $couponCodes])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $couponCodes])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            document.addEventListener('DOMContentLoaded', function() {
                AdminAjax.initDataTable({
                    tableSelector: '#couponCodeTable',
                    searchSelector: '[data-search]',
                    filterSelector: '[data-filter]',
                    paginationSelector: '.pagination a',
                    loadUrl: '{{ route('admin.coupon-code') }}',
                    containerSelector: '.table-container',
                    onSuccess: function(response) {
                        if (response.pagination) {
                            document.querySelector('.pagination-container').innerHTML = response.pagination;
                        }
                    }
                });

                // Handle per page change
                document.getElementById('perPageSelect')?.addEventListener('change', function() {
                    const form = document.getElementById('couponCodeFilterForm');
                    const formData = new FormData(form || document.createElement('form'));
                    formData.set('per_page', this.value);
                    formData.delete('page');
                    
                    const params = new URLSearchParams();
                    formData.forEach((value, key) => {
                        if (value) params.set(key, value);
                    });
                    
                    AdminAjax.loadTable('{{ route('admin.coupon-code') }}', document.querySelector('.table-container'), {
                        params: Object.fromEntries(params),
                        onSuccess: function(response) {
                            if (response.pagination) {
                                const paginationContainer = document.querySelector('.pagination-container');
                                if (paginationContainer) {
                                    paginationContainer.innerHTML = response.pagination;
                                }
                            }
                        }
                    });
                });
            });
        })();
    </script>
@endsection

