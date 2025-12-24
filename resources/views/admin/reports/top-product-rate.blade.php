@extends('layouts.vertical', ['title' => 'Top Rating Products'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Top Rating Products'])

    <div class="row">
        <div class="col-12">
            <!-- Top Rating Products Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Top Rating Products</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.top-product-rate.export', ['format' => 'excel']) }}" 
                            class="btn btn-success btn-sm" title="Export to Excel">
                            <i class="ti ti-file-excel me-1"></i> Export
                        </a>
                        <a href="{{ route('admin.top-product-rate.export', ['format' => 'pdf']) }}" 
                            class="btn btn-success btn-sm pdf-export-btn" title="Export to PDF" download>
                            <i class="ti ti-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.top-product-rate.print') }}" 
                            class="btn btn-success btn-sm" title="Print Full Report" target="_blank">
                            <i class="ti ti-printer me-1"></i> Print
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
                                                $currentPerPage = request('per_page', 50);
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
                        @include('admin.reports.partials.top-product-rate-table', ['reports' => $reports])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $reports])
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
                tableSelector: '#topProductRateTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.top-product-rate') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    if (response.pagination) {
                        document.querySelector('.pagination-container').innerHTML = response.pagination;
                    }
                }
            });

            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const params = new URLSearchParams();
                params.set('per_page', this.value);
                params.delete('page');

                AdminAjax.loadTable('{{ route('admin.top-product-rate') }}', document.querySelector(
                    '.table-container'), {
                    params: Object.fromEntries(params),
                    onSuccess: function(response) {
                        if (response.pagination) {
                            document.querySelector('.pagination-container').innerHTML = response.pagination;
                        }
                    }
                });
            });
        });
    </script>

    @include('admin.partials.pdf-loader')
@endsection

