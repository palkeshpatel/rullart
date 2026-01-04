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
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-product-btn" data-product-id="{{ $product->productid }}" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        @unless(\App\Helpers\ViewHelper::isView('productpriceview'))
                            <a href="{{ route('admin.products.edit', $product->productid) }}" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">
                                <i class="ti ti-edit fs-lg"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-product-btn" data-product-id="{{ $product->productid }}" data-product-name="{{ $product->title }}" title="Delete">
                                <i class="ti ti-trash fs-lg"></i>
                            </a>
                        @endunless
                    </div>
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

