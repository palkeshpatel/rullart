<div class="row mt-3">
    <div class="col-sm">
        <div>
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
        </div>
    </div>
    <div class="col-sm-auto">
        @php
            // Preserve all query parameters including per_page
            $queryParams = request()->query();
            // Ensure per_page is included if it exists in the request
            if (request()->has('per_page')) {
                $queryParams['per_page'] = request('per_page');
            }
        @endphp
        {{ $items->appends($queryParams)->links() }}
    </div>
</div>
