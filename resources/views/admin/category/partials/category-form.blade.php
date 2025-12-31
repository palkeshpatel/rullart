<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">
                    {{ $category ? 'Edit Category' : 'Add Category' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="categoryForm" method="POST"
                action="{{ $category ? route('admin.category.update', $category->categoryid) : route('admin.category.store') }}"
                novalidate enctype="multipart/form-data">
                @csrf
                @if ($category)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="category" class="form-control" placeholder="Category Name"
                                    value="{{ old('category', $category ? $category->category : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category (AR) <span class="text-danger">*</span></label>
                                <input type="text" name="categoryAR" class="form-control" placeholder="Category Name"
                                    value="{{ old('categoryAR', $category ? $category->categoryAR : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category Code <span class="text-danger">*</span></label>
                                <input type="text" name="categorycode" class="form-control"
                                    value="{{ old('categorycode', $category ? $category->categorycode : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent Category <span class="text-danger">*</span></label>
                                <select name="parentid" class="form-select" required>
                                    <option value="">--PARENT--</option>
                                    <option value="0" {{ old('parentid', $category ? ($category->parentid ?? 0) : '') == 0 ? 'selected' : '' }}>No Parent (Main Category)</option>
                                    @foreach ($parentCategories ?? [] as $parent)
                                        <option value="{{ $parent->categoryid }}"
                                            {{ old('parentid', $category && $category->parentid == $parent->categoryid ? $category->parentid : '') == $parent->categoryid ? 'selected' : '' }}>
                                            {{ $parent->category }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [EN]</label>
                                <input type="text" name="metatitle" class="form-control" placeholder="Meta Title"
                                    value="{{ old('metatitle', $category ? $category->metatitle : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [AR]</label>
                                <input type="text" name="metatitleAR" class="form-control" placeholder="Meta Title"
                                    value="{{ old('metatitleAR', $category ? $category->metatitleAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [EN]</label>
                                <input type="text" name="metakeyword" class="form-control" placeholder="Meta Keyword"
                                    value="{{ old('metakeyword', $category ? $category->metakeyword : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [AR]</label>
                                <input type="text" name="metakeywordAR" class="form-control" placeholder="Meta Keyword"
                                    value="{{ old('metakeywordAR', $category ? $category->metakeywordAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [EN]</label>
                                <textarea name="metadescr" class="form-control" rows="3" placeholder="Meta Description">{{ old('metadescr', $category ? $category->metadescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [AR]</label>
                                <textarea name="metadescrAR" class="form-control" rows="3" placeholder="Meta Description">{{ old('metadescrAR', $category ? $category->metadescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispublished" value="1"
                                        id="ispublished" {{ old('ispublished', $category ? $category->ispublished : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispublished">
                                        Is Active?
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="displayorder" class="form-control"
                                    value="{{ old('displayorder', $category ? ($category->displayorder ?? 0) : 0) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Desktop Photo</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                @if($category && $category->photo)
                                    <small class="text-muted">Current: {{ $category->photo }}</small>
                                @endif
                                <small class="text-muted d-block">Recommended size: 1440px X 338px</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Mobile Photo</label>
                                <input type="file" name="photo_mobile" class="form-control" accept="image/*">
                                @if($category && $category->photo_mobile)
                                    <small class="text-muted">Current: {{ $category->photo_mobile }}</small>
                                @endif
                                <small class="text-muted d-block">Recommended size: 990px X 467px</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $category ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
