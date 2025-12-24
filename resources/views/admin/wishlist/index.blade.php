@extends('layouts.vertical', ['title' => 'Wishlist'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Wishlist'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header justify-content-between align-items-center border-dashed">
                    <h4 class="card-title mb-0">Wishlist</h4>
                </div>
                <div class="card-body">
                    <!-- Search and Per Page Controls -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-2 justify-content-between align-items-center">
                                <div class="app-search app-search-sm" style="max-width: 300px;">
                                    <input type="text" name="search" class="form-control form-control-sm" data-search
                                        placeholder="Search wishlist..." value="{{ request('search') }}">
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
                        @include('admin.wishlist.partials.table', ['wishlists' => $wishlists])
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-container">
                        @include('admin.partials.pagination', ['items' => $wishlists])
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
                tableSelector: '#wishlistTable',
                searchSelector: '[data-search]',
                loadUrl: '{{ route('admin.wishlist') }}',
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

            document.getElementById('perPageSelect')?.addEventListener('change', function() {
                const formData = new FormData();
                formData.set('per_page', this.value);
                const params = Object.fromEntries(formData);
                AdminAjax.loadTable('{{ route('admin.wishlist') }}', document.querySelector('.table-container'), {
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

