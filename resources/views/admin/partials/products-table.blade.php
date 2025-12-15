<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="productsTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Product Code</th>
                <th><i class="fa fa-sort"></i> Title</th>
                <th><i class="fa fa-sort"></i> Category</th>
                <th><i class="fa fa-sort"></i> Price</th>
                <th><i class="fa fa-sort"></i> Selling Price</th>
                <th>Published</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
            <tr>
                <td>{{ $product->productcode }}</td>
                <td>{{ $product->title }}</td>
                <td>{{ $product->category->category ?? 'N/A' }}</td>
                <td>{{ number_format($product->price, 2) }}</td>
                <td>{{ number_format($product->sellingprice, 2) }}</td>
                <td>{{ $product->ispublished ? 'Yes' : 'No' }}</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No products found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

