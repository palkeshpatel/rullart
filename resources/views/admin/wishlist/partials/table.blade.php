<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="wishlistTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Product</th>
                <th class="text-muted">Customer</th>
                <th class="text-muted">Email</th>
                <th class="text-muted">Date Added</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($wishlists as $wishlist)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($wishlist->product && $wishlist->product->photo)
                                <img src="{{ asset('storage/' . $wishlist->product->photo) }}" alt="{{ $wishlist->product->title ?? 'N/A' }}" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @endif
                            <div>
                                <div class="fw-semibold">{{ $wishlist->product ? $wishlist->product->title : 'N/A' }}</div>
                                <small class="text-muted">{{ $wishlist->product ? $wishlist->product->productcode : 'N/A' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        {{ $wishlist->customer ? ($wishlist->customer->firstname . ' ' . $wishlist->customer->lastname) : 'N/A' }}
                    </td>
                    <td>{{ $wishlist->customer ? $wishlist->customer->email : 'N/A' }}</td>
                    <td>
                        @if($wishlist->createdon)
                            {{ \Carbon\Carbon::parse($wishlist->createdon)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">No wishlist items found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

