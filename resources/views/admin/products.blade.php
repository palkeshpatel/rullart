@extends('layouts.vertical', ['title' => 'Products'])

@section('content')

@include('layouts.partials/page-title', ['title' => 'Products'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Products List</h4>
                <div class="d-flex gap-2">
                    <a href="javascript:void(0);" class="btn btn-sm btn-success" title="Export to Excel">
                        <i class="fa fa-file-excel-o"></i>
                    </a>
                    <a href="javascript:void(0);" class="btn btn-sm btn-success" title="Print">
                        <i class="fa fa-print"></i>
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Filters Form -->
                <form method="GET" action="{{ route('admin.products') }}" data-table-filters id="productsFilterForm">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select form-select-sm" data-filter>
                                <option value="">--All Categories--</option>
                                @foreach(\App\Models\Category::orderBy('category')->get() as $cat)
                                    <option value="{{ $cat->categoryid }}" {{ request('category') == $cat->categoryid ? 'selected' : '' }}>{{ $cat->category }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Published</label>
                            <select name="published" class="form-select form-select-sm" data-filter>
                                <option value="">--All--</option>
                                <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex gap-2 justify-content-end align-items-end">
                                <label class="mb-0">Show 
                                    <select class="form-select form-select-sm d-inline-block" style="width: auto;" id="perPageSelect">
                                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page', 25) == 50 ? 'selected' : '' }}>50</option>
                                        <option value="100" {{ request('per_page', 25) == 100 ? 'selected' : '' }}>100</option>
                                    </select> entries
                                </label>
                                <div class="input-group" style="max-width: 200px;">
                                    <span class="input-group-text">Search:</span>
                                    <input type="text" name="search" class="form-control form-control-sm" data-search placeholder="Search..." value="{{ request('search') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Table Container -->
                <div class="table-container">
                    @include('admin.partials.products-table', ['products' => $products])
                </div>

                <!-- Pagination -->
                @include('admin.partials.pagination', ['items' => $products])
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    AdminAjax.initDataTable({
        tableSelector: '#productsTable',
        searchSelector: '[data-search]',
        filterSelector: '[data-filter]',
        paginationSelector: '.pagination a',
        loadUrl: '{{ route("admin.products") }}',
        containerSelector: '.table-container'
    });

    document.getElementById('perPageSelect')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        AdminAjax.loadTable(url.toString(), document.querySelector('.table-container'));
    });
});
</script>
@endsection

