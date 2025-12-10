@extends('layouts.vertical', ['title' => 'Admin Category'])

@section('css')
@endsection

@section('content')

@include('layouts.partials/page-title', ['title' => 'Category'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-dashed justify-content-between align-items-center">
                <h4 class="card-title mb-0">Category Information</h4>
            </div>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categoryName" placeholder="Enter category name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="categorySlug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="categorySlug" placeholder="category-slug">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="parentCategory" class="form-label">Parent Category</label>
                            <select class="form-select" id="parentCategory">
                                <option value="">None (Main Category)</option>
                                <option value="electronics">Electronics</option>
                                <option value="fashion">Fashion</option>
                                <option value="home">Home & Living</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="categoryStatus" class="form-label">Status</label>
                            <select class="form-select" id="categoryStatus">
                                <option value="published">Published</option>
                                <option value="pending">Pending</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" rows="5" placeholder="Enter category description"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="categoryImage" class="form-label">Category Image</label>
                        <input type="file" class="form-control" id="categoryImage" accept="image/*">
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Category</button>
                        <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@endsection

