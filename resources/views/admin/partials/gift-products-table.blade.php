<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="giftProductsTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Product Code</th>
                <th><i class="fa fa-sort"></i> Title</th>
                <th><i class="fa fa-sort"></i> Selling Price</th>
                <th><i class="fa fa-sort"></i> Category</th>
                <th>Photo</th>
                <th><i class="fa fa-sort"></i> Quantity</th>
                <th>Active</th>
                <th>Updated Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{ $product->productcode }}</td>
                <td>{{ $product->title }}</td>
                <td>{{ number_format($product->sellingprice ?? 0, 3) }}</td>
                <td>{{ $product->category->category ?? 'N/A' }}</td>
                <td>
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->title }}" style="width: 50px; height: 50px; object-fit: cover;">
                    @else
                        <span class="text-muted">No Image</span>
                    @endif
                </td>
                <td>{{ $product->quantity ?? 0 }}</td>
                <td>{{ $product->ispublished ? 'Yes' : 'No' }}</td>
                <td>{{ $product->updated_at ? \Carbon\Carbon::parse($product->updated_at)->format('d/M/Y') : 'N/A' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No gift products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

