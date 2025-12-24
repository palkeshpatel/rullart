@extends('layouts.vertical', ['title' => 'Top Selling Products'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Top Selling Products'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.top-product-month') }}" data-table-filters id="topProductFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="form-label mb-1">Month:</label>
                                <select name="month" class="form-select form-select-sm" data-filter>
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" 
                                            {{ request('month', date('m')) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label mb-1">Year:</label>
                                <select name="year" class="form-select form-select-sm" data-filter>
                                    @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                        <option value="{{ $year }}" {{ request('year', date('Y')) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="ti ti-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Top Products Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Top Selling Products</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.top-product-month.export', ['format' => 'excel', 'month' => request('month', date('m')), 'year' => request('year', date('Y'))]) }}" 
                            class="btn btn-success btn-sm" title="Export to Excel">
                            <i class="ti ti-file-excel me-1"></i> Export
                        </a>
                        <a href="{{ route('admin.top-product-month.export', ['format' => 'pdf', 'month' => request('month', date('m')), 'year' => request('year', date('Y'))]) }}" 
                            class="btn btn-success btn-sm pdf-export-btn" title="Export to PDF" download>
                            <i class="ti ti-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.top-product-month.print', ['month' => request('month', date('m')), 'year' => request('year', date('Y'))]) }}" 
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
                        @include('admin.reports.partials.top-product-month-table', ['reports' => $reports])
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
                tableSelector: '#topProductMonthTable',
                searchSelector: '[data-search]',
                filterSelector: '[data-filter]',
                paginationSelector: '.pagination a',
                loadUrl: '{{ route('admin.top-product-month') }}',
                containerSelector: '.table-container',
                onSuccess: function(response) {
                    if (response.pagination) {
                        document.querySelector('.pagination-container').innerHTML = response.pagination;
                    }
                }
            });

            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const form = document.getElementById('topProductFilterForm');
                const formData = new FormData(form);
                formData.set('per_page', this.value);
                formData.delete('page');

                const params = new URLSearchParams();
                formData.forEach((value, key) => {
                    if (value) params.set(key, value);
                });

                AdminAjax.loadTable('{{ route('admin.top-product-month') }}', document.querySelector(
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

