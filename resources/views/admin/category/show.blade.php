@extends('layouts.vertical', ['title' => 'View Category'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'View Category'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Category Details</h4>
                    <a href="{{ route('admin.category.edit', $category->categoryid) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit Category
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Category Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Category ID:</strong></label>
                                        <p class="mb-0">{{ $category->categoryid }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Category Name (EN):</strong></label>
                                        <p class="mb-0">{{ $category->category }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Category Name (AR):</strong></label>
                                        <p class="mb-0">{{ $category->categoryAR ?? 'N/A' }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Category Code:</strong></label>
                                        <p class="mb-0">{{ $category->categorycode }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Display Order:</strong></label>
                                        <p class="mb-0">{{ $category->displayorder ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Status Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Published:</strong></label>
                                        <p class="mb-0">
                                            @if($category->ispublished)
                                                <span class="badge badge-soft-success">Yes</span>
                                            @else
                                                <span class="badge badge-soft-danger">No</span>
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Show Menu:</strong></label>
                                        <p class="mb-0">
                                            @if($category->showmenu)
                                                <span class="badge badge-soft-success">Yes</span>
                                            @else
                                                <span class="badge badge-soft-danger">No</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($category->parentid)
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Parent Category ID:</strong></label>
                                        <p class="mb-0">{{ $category->parentid }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

