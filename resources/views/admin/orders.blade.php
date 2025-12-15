@extends('layouts.vertical', ['title' => 'Orders List'])

@section('content')

@include('layouts.partials/page-title', ['title' => 'Orders List'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Orders List</h4>
            </div>
            <div class="card-body">
                <!-- Filters Form -->
                <form method="GET" action="{{ route('admin.orders') }}" data-table-filters id="ordersFilterForm">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select form-select-sm" data-filter>
                                <option value="">--All Status--</option>
                                <option value="2" {{ request('status') == '2' ? 'selected' : '' }}>Process</option>
                                <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Pending</option>
                                <option value="7" {{ request('status') == '7' ? 'selected' : '' }}>Delivered</option>
                                <option value="8" {{ request('status') == '8' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <select name="country" class="form-select form-select-sm" data-filter>
                                <option value="">--All Country--</option>
                                @foreach($countries ?? [] as $country)
                                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="d-flex gap-2 justify-content-end align-items-end">
                                <a href="javascript:void(0);" class="btn btn-sm btn-success" title="Export to Excel">
                                    <i class="fa fa-file-excel-o"></i>
                                </a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-success" title="Print">
                                    <i class="fa fa-print"></i>
                                </a>
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
                    @include('admin.partials.orders-table', ['orders' => $orders])
                </div>

                <!-- Pagination -->
                @include('admin.partials.pagination', ['items' => $orders])
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
        tableSelector: '#ordersTable',
        searchSelector: '[data-search]',
        filterSelector: '[data-filter]',
        paginationSelector: '.pagination a',
        loadUrl: '{{ route("admin.orders") }}',
        containerSelector: '.table-container'
    });

    // Per page change handler
    document.getElementById('perPageSelect')?.addEventListener('change', function() {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', this.value);
        AdminAjax.loadTable(url.toString(), document.querySelector('.table-container'));
    });

    // Order status change handler
    document.querySelectorAll('.order-status').forEach(select => {
        select.addEventListener('change', function() {
            const orderId = this.dataset.orderId;
            const status = this.value;
            
            // Update order status via AJAX
            AdminAjax.post('{{ route("admin.orders") }}/' + orderId + '/status', {
                status: status
            })
            .then(response => {
                AdminAjax.showSuccess('Order status updated successfully!');
            })
            .catch(error => {
                AdminAjax.showError('Failed to update order status.');
                this.value = this.dataset.originalValue; // Revert
            });
        });
    });
});
</script>
@endsection
