@extends('layouts.vertical', ['title' => 'Edit Category'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Edit Category'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Category - {{ $category->category }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.category.update', $category->categoryid) }}" id="categoryEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Name (EN) <span class="text-danger">*</span></label>
                                    <input type="text" name="category" class="form-control" value="{{ old('category', $category->category) }}" required>
                                    @error('category')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Name (AR)</label>
                                    <input type="text" name="categoryAR" class="form-control" value="{{ old('categoryAR', $category->categoryAR) }}">
                                    @error('categoryAR')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category Code <span class="text-danger">*</span></label>
                                    <input type="text" name="categorycode" class="form-control" value="{{ old('categorycode', $category->categorycode) }}" required>
                                    @error('categorycode')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="displayorder" class="form-control" value="{{ old('displayorder', $category->displayorder ?? 0) }}">
                                    @error('displayorder')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Published</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="ispublished" value="1" id="ispublished" {{ old('ispublished', $category->ispublished) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="ispublished">
                                            Published
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Show Menu</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="showmenu" value="1" id="showmenu" {{ old('showmenu', $category->showmenu) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="showmenu">
                                            Show in Menu
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update Category
                            </button>
                            <a href="{{ route('admin.category') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

