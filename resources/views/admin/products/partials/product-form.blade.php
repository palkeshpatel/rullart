<div class="modal fade" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel">
                    {{ $product ? 'Edit Product' : 'Add Product' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productForm" method="POST"
                action="{{ $product ? route('admin.products.update', $product->productid) : route('admin.products.store') }}"
                novalidate enctype="multipart/form-data">
                @csrf
                @if ($product)
                    @method('PUT')
                @endif
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Basic Information -->
                    <h6 class="mb-3 border-bottom pb-2">Basic Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select name="fkcategoryid" class="form-select" required>
                                    <option value="">-- Select Category --</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ old('fkcategoryid', $product && $product->fkcategoryid == $cat->categoryid ? $product->fkcategoryid : '') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" name="productcode" class="form-control"
                                    value="{{ old('productcode', $product ? $product->productcode : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Title (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $product ? $product->title : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Title (AR) <span class="text-danger">*</span></label>
                                <input type="text" name="titleAR" class="form-control"
                                    value="{{ old('titleAR', $product ? $product->titleAR : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Descriptions -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Descriptions</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Short Description (EN) <span class="text-danger">*</span></label>
                                <textarea name="shortdescr" class="form-control" rows="3" required>{{ old('shortdescr', $product ? $product->shortdescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Short Description (AR) <span class="text-danger">*</span></label>
                                <textarea name="shortdescrAR" class="form-control" rows="3" required>{{ old('shortdescrAR', $product ? $product->shortdescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Long Description (EN)</label>
                                <textarea name="longdescr" class="form-control" rows="5">{{ old('longdescr', $product ? $product->longdescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Long Description (AR)</label>
                                <textarea name="longdescrAR" class="form-control" rows="5">{{ old('longdescrAR', $product ? $product->longdescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Pricing</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="price" class="form-control"
                                    value="{{ old('price', $product ? $product->price : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Discount</label>
                                <input type="number" step="0.01" name="discount" class="form-control"
                                    value="{{ old('discount', $product ? $product->discount : 0) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Selling Price <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="sellingprice" class="form-control"
                                    value="{{ old('sellingprice', $product ? $product->sellingprice : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">SEO Meta Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [EN]</label>
                                <input type="text" name="metatitle" class="form-control"
                                    value="{{ old('metatitle', $product ? $product->metatitle : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [AR]</label>
                                <input type="text" name="metatitleAR" class="form-control"
                                    value="{{ old('metatitleAR', $product ? $product->metatitleAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [EN]</label>
                                <input type="text" name="metakeyword" class="form-control"
                                    value="{{ old('metakeyword', $product ? $product->metakeyword : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [AR]</label>
                                <input type="text" name="metakeywordAR" class="form-control"
                                    value="{{ old('metakeywordAR', $product ? $product->metakeywordAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [EN]</label>
                                <textarea name="metadescr" class="form-control" rows="3">{{ old('metadescr', $product ? $product->metadescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [AR]</label>
                                <textarea name="metadescrAR" class="form-control" rows="3">{{ old('metadescrAR', $product ? $product->metadescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Photos -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Product Photos</h6>
                    <div class="row">
                        @for($i = 1; $i <= 5; $i++)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo {{ $i }}</label>
                                <input type="file" name="photo{{ $i }}" class="form-control" accept="image/*">
                                @if($product && $product->{"photo{$i}"})
                                    <small class="text-muted">Current: {{ $product->{"photo{$i}"} }}</small>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        @endfor
                    </div>

                    <!-- Video -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Video</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Video URL</label>
                                <input type="text" name="video" class="form-control"
                                    value="{{ old('video', $product ? $product->video : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Video Poster URL</label>
                                <input type="text" name="videoposter" class="form-control"
                                    value="{{ old('videoposter', $product ? $product->videoposter : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Status Flags -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2">Status & Flags</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispublished" value="1"
                                        id="ispublished" {{ old('ispublished', $product ? $product->ispublished : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispublished">Published</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isnew" value="1"
                                        id="isnew" {{ old('isnew', $product ? $product->isnew : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isnew">New Product</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispopular" value="1"
                                        id="ispopular" {{ old('ispopular', $product ? $product->ispopular : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispopular">Popular</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isgift" value="1"
                                        id="isgift" {{ old('isgift', $product ? $product->isgift : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isgift">Is Gift</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="internation_ship" value="1"
                                        id="internation_ship" {{ old('internation_ship', $product ? $product->internation_ship : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="internation_ship">International Shipping</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $product ? 'Update' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

