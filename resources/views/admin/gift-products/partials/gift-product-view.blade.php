<div class="modal fade" id="giftProductViewModal" tabindex="-1" aria-labelledby="giftProductViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="giftProductViewModalLabel">Gift Product Details - {{ $product->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">ID:</th>
                                <td>{{ $product->productid }}</td>
                            </tr>
                            <tr>
                                <th>Product Code:</th>
                                <td>{{ $product->productcode }}</td>
                            </tr>
                            <tr>
                                <th>Title (EN):</th>
                                <td>{{ $product->title }}</td>
                            </tr>
                            <tr>
                                <th>Title (AR):</th>
                                <td>{{ $product->titleAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Category:</th>
                                <td>{{ $product->category->category ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Price:</th>
                                <td>{{ number_format($product->price, 3) }}</td>
                            </tr>
                            <tr>
                                <th>Discount:</th>
                                <td>{{ number_format($product->discount, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Selling Price:</th>
                                <td>{{ number_format($product->sellingprice, 3) }}</td>
                            </tr>
                            <tr>
                                <th>Short Description (EN):</th>
                                <td>{{ $product->shortdescr ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Short Description (AR):</th>
                                <td>{{ $product->shortdescrAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Published:</th>
                                <td>
                                    @if($product->ispublished)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>New:</th>
                                <td>
                                    @if($product->isnew)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Popular:</th>
                                <td>
                                    @if($product->ispopular)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Is Gift:</th>
                                <td>
                                    <span class="badge bg-success">Yes</span>
                                </td>
                            </tr>
                            <tr>
                                <th>International Shipping:</th>
                                <td>
                                    @if($product->internation_ship)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            @if($product->video)
                            <tr>
                                <th>Video URL:</th>
                                <td>{{ $product->video }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-gift-product-btn" data-product-id="{{ $product->productid }}">
                    <i class="ti ti-edit me-1"></i> Edit Gift Product
                </button>
            </div>
        </div>
    </div>
</div>

