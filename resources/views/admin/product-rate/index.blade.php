@extends('layouts.vertical', ['title' => 'Product Reviews'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Product Reviews'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section -->
            <form method="GET" action="{{ route('admin.product-rate') }}" data-table-filters id="ratingsFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="form-label mb-1">Rating</label>
                                <select name="rating" class="form-select form-select-sm" data-filter>
                                    <option value="">--All Ratings--</option>
                                    <option value="5" {{ request('rating') == '5' ? 'selected' : '' }}>5 Stars</option>
                                    <option value="4" {{ request('rating') == '4' ? 'selected' : '' }}>4 Stars</option>
                                    <option value="3" {{ request('rating') == '3' ? 'selected' : '' }}>3 Stars</option>
                                    <option value="2" {{ request('rating') == '2' ? 'selected' : '' }}>2 Stars</option>
                                    <option value="1" {{ request('rating') == '1' ? 'selected' : '' }}>1 Star</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label mb-1">Published</label>
                                <select name="published" class="form-select form-select-sm" data-filter>
                                    <option value="">--All--</option>
                                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Published</option>
                                    <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Unpublished</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Ratings Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Product Reviews</h4>
                    <a href="{{ route('admin.product-rate.export', ['rating' => request('rating'), 'published' => request('published'), 'search' => request('search')]) }}" 
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
                                        placeholder="Search reviews..." value="{{ request('search') }}">
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
                        @include('admin.product-rate.partials.table', ['ratings' => $ratings])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $ratings])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            AdminAjax.initDataTable({
                tableSelector: '#ratingsTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                loadUrl: '{{ route('admin.product-rate') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    if (response.pagination) {
                        document.querySelector('.pagination-container').innerHTML = response.pagination;
                    }
                }
            });

            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const form = document.getElementById('ratingsFilterForm');
                const formData = new FormData(form);
                formData.set('per_page', this.value);
                formData.delete('page');
                const params = Object.fromEntries(formData);
                AdminAjax.loadTable('{{ route('admin.product-rate') }}', document.querySelector('.table-container'), {
                    params: params,
                    onSuccess: function(response) {
                        if (response.pagination) {
                            document.querySelector('.pagination-container').innerHTML = response.pagination;
                        }
                    }
                });
            });
        });
    </script>
@endsection

