@extends('layouts.vertical', ['title' => 'Admin Product'])

@section('css')
@endsection

@section('content')

@include('layouts.partials/page-title', ['title' => 'Product'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-dashed justify-content-between align-items-center">
                <h4 class="card-title mb-0">Product Information</h4>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="productName" placeholder="Enter product name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productSKU" class="form-label">SKU <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="productSKU" placeholder="Enter SKU">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productPrice" class="form-label">Price <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="productPrice" placeholder="0.00" step="0.01">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productStock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="productStock" placeholder="0">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="productCategory" class="form-label">Category</label>
                            <select class="form-select" id="productCategory">
                                <option value="">Select Category</option>
                                <option value="electronics">Electronics</option>
                                <option value="fashion">Fashion</option>
                                <option value="home">Home</option>
                                <option value="sports">Sports</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="productStatus" class="form-label">Status</label>
                            <select class="form-select" id="productStatus">
                                <option value="published">Published</option>
                                <option value="pending">Pending</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" rows="5" placeholder="Enter product description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="productImage" class="form-label">Product Image</label>
                        <input type="file" class="form-control" id="productImage" accept="image/*">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Product</button>
                        <a href="/admin/products" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@endsection

