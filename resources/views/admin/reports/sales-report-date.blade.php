@extends('layouts.vertical', ['title' => 'Sales Report Datewise'])

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.css">
@endsection

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Sales Report Datewise List'])

    <div class="row">
        <div class="col-12">
            <!-- Filters Section - Top Bar -->
            <form method="GET" action="{{ route('admin.sales-report-date') }}" data-table-filters id="dateReportFilterForm">
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="form-label mb-1">Date range:</label>
                                <div class="input-group">
                                    <div class="position-relative" style="flex: 1;">
                                        <div id="dateRangePicker" class="form-control form-control-sm" 
                                            data-toggle="date-picker-range" 
                                            style="cursor: pointer; display: flex; align-items: center; justify-content: space-between;">
                                            <span id="dateRangeDisplay">
                                                @if(request('date_from') && request('date_to'))
                                                    {{ \Carbon\Carbon::parse(request('date_from'))->format('m/d/Y') }} - {{ \Carbon\Carbon::parse(request('date_to'))->format('m/d/Y') }}
                                                @else
                                                    <i class="ti ti-calendar me-2"></i>Date Range
                                                @endif
                                            </span>
                                            <i class="ti ti-chevron-down"></i>
                                        </div>
                                    </div>
                                    <input type="hidden" name="date_from" id="date_from" value="{{ request('date_from') }}">
                                    <input type="hidden" name="date_to" id="date_to" value="{{ request('date_to') }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-search me-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Sales Report Table Card -->
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Sales Report Datewise List</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.sales-report-date.export', ['format' => 'excel', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
                            class="btn btn-success btn-sm" title="Export to Excel">
                            <i class="ti ti-file-excel me-1"></i> Export
                        </a>
                        <a href="{{ route('admin.sales-report-date.export', ['format' => 'pdf', 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
                            class="btn btn-success btn-sm pdf-export-btn" title="Export to PDF">
                            <i class="ti ti-file-pdf me-1"></i> PDF
                        </a>
                        <a href="{{ route('admin.sales-report-date.print', ['date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" 
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
                        @include('admin.reports.partials.datewise-table', ['reports' => $reports])
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
        // Load scripts in order and initialize daterangepicker
        (function() {
            // Check if jQuery is already loaded
            if (typeof jQuery === 'undefined') {
                var jqueryScript = document.createElement('script');
                jqueryScript.src = 'https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js';
                jqueryScript.onload = loadMoment;
                document.head.appendChild(jqueryScript);
            } else {
                window.$ = window.jQuery = jQuery;
                loadMoment();
            }

            function loadMoment() {
                if (typeof moment === 'undefined') {
                    var momentScript = document.createElement('script');
                    momentScript.src = 'https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js';
                    momentScript.onload = loadDaterangepicker;
                    document.head.appendChild(momentScript);
                } else {
                    loadDaterangepicker();
                }
            }

            function loadDaterangepicker() {
                // Wait a bit to ensure moment is fully loaded
                setTimeout(function() {
                    if (typeof jQuery === 'undefined' || typeof moment === 'undefined') {
                        console.error('jQuery or moment not loaded');
                        return;
                    }
                    
                    if (typeof jQuery.fn.daterangepicker === 'undefined') {
                        var daterangepickerScript = document.createElement('script');
                        daterangepickerScript.src = 'https://cdn.jsdelivr.net/npm/daterangepicker@3.1.0/daterangepicker.min.js';
                        daterangepickerScript.onload = function() {
                            // Wait a bit more to ensure daterangepicker is attached
                            setTimeout(initializeDateRangePicker, 100);
                        };
                        daterangepickerScript.onerror = function() {
                            console.error('Failed to load daterangepicker');
                        };
                        document.head.appendChild(daterangepickerScript);
                    } else {
                        initializeDateRangePicker();
                    }
                }, 100);
            }

            function initializeDateRangePicker() {
                jQuery(document).ready(function($) {
                    // Initialize date range picker
                    const dateFrom = $('#date_from').val();
                    const dateTo = $('#date_to').val();
                    
                    let startDate = moment().subtract(29, 'days');
                    let endDate = moment();
                    
                    if (dateFrom && dateTo) {
                        startDate = moment(dateFrom);
                        endDate = moment(dateTo);
                    }

                    $('#dateRangePicker').daterangepicker({
                        startDate: startDate,
                        endDate: endDate,
                        ranges: {
                            'Today': [moment(), moment()],
                            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                            'This Month': [moment().startOf('month'), moment().endOf('month')],
                            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                        },
                        locale: {
                            format: 'MM/DD/YYYY'
                        },
                        cancelClass: 'btn-light',
                        applyButtonClasses: 'btn-success'
                    }, function(start, end) {
                        // Update hidden inputs
                        $('#date_from').val(start.format('YYYY-MM-DD'));
                        $('#date_to').val(end.format('YYYY-MM-DD'));
                        
                        // Update display
                        $('#dateRangeDisplay').html(start.format('MM/DD/YYYY') + ' - ' + end.format('MM/DD/YYYY'));
                    });

                    // Set initial display if dates exist
                    if (dateFrom && dateTo) {
                        $('#dateRangeDisplay').html(moment(dateFrom).format('MM/DD/YYYY') + ' - ' + moment(dateTo).format('MM/DD/YYYY'));
                    }

                    // Initialize AJAX data table
                    if (typeof AdminAjax !== 'undefined') {
                        AdminAjax.initDataTable({
                            tableSelector: '#datewiseReportTable',
                            searchSelector: '[data-search]',
                            filterSelector: '[data-filter]',
                            paginationSelector: '.pagination a',
                            loadUrl: '{{ route('admin.sales-report-date') }}',
                            containerSelector: '.table-container',
                            onSuccess: function(response) {
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
                            const form = document.getElementById('dateReportFilterForm');
                            const formData = new FormData(form);
                            formData.set('per_page', this.value);
                            formData.delete('page');

                            const params = new URLSearchParams();
                            formData.forEach((value, key) => {
                                if (value) params.set(key, value);
                            });

                            AdminAjax.loadTable('{{ route('admin.sales-report-date') }}', document.querySelector(
                                '.table-container'), {
                                params: Object.fromEntries(params),
                                onSuccess: function(response) {
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
                    }
                });
            }
        })();
    </script>

    @include('admin.partials.pdf-loader')
@endsection

