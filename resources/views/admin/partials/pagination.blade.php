<div class="row mt-3">
    <div class="col-sm">
        <div>
            Showing {{ $items->firstItem() ?? 0 }} to {{ $items->lastItem() ?? 0 }} of {{ $items->total() }} entries
            @if(request('search'))
                (filtered from {{ $items->total() }} total entries)
            @endif
        </div>
    </div>
    <div class="col-sm-auto">
        {{ $items->appends(request()->query())->links() }}
    </div>
</div>

