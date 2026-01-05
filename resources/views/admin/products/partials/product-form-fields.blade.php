{{-- Basic Information --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Basic Information</h5>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Product Code <span class="text-danger">*</span></label>
            <input type="text" name="productcode" class="form-control" value="{{ old('productcode', $product ? $product->productcode : '') }}" required>
            <div class="invalid-feedback"></div>
            @error('productcode')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Category <span class="text-danger">*</span></label>
            <select name="fkcategoryid" id="categorySelect" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->categoryid }}" {{ old('fkcategoryid', $product && $product->fkcategoryid == $cat->categoryid ? $product->fkcategoryid : '') == $cat->categoryid ? 'selected' : '' }}>
                        {{ $cat->category }}@if(isset($cat->subcategory_count) && $cat->subcategory_count > 0) ({{ $cat->subcategory_count }})@endif
                    </option>
                @endforeach
            </select>
            <div class="invalid-feedback"></div>
            @error('fkcategoryid')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Sub Category</label>
            <select name="productcategoryid" id="subCategorySelect" class="form-select">
                <option value="">-- Select Sub Category --</option>
                @if($product && $product->productcategoryid)
                    @php
                        $selectedSubCategory = \App\Models\Category::find($product->productcategoryid);
                    @endphp
                    @if($selectedSubCategory)
                        <option value="{{ $selectedSubCategory->categoryid }}" selected>{{ $selectedSubCategory->category }}</option>
                    @endif
                @endif
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Color</label>
            <select name="color" class="form-select">
                <option value="">-- Select Color --</option>
                @foreach($colors as $color)
                    <option value="{{ $color->filtervalueid }}" {{ old('color', (isset($productFilters) && isset($productFilters['color']) && $productFilters['color']->count() > 0 && $productFilters['color']->first()->fkfiltervalueid == $color->filtervalueid) ? $productFilters['color']->first()->fkfiltervalueid : '') == $color->filtervalueid ? 'selected' : '' }}>
                        {{ $color->filtervalue }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Occasion</label>
            <select name="occasions[]" class="form-select" multiple>
                @foreach($occasions as $occasion)
                    <option value="{{ $occasion->occassionid }}" {{ old('occasions', (isset($productFilters) && isset($productFilters['occassion']) && $productFilters['occassion']->pluck('fkfiltervalueid')->contains($occasion->occassionid)) ? [$occasion->occassionid] : []) ? 'selected' : '' }}>
                        {{ $occasion->occassion }}
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Recommend Product Codes</label>
            <input type="text" name="recommend_product_codes" class="form-control" value="{{ old('recommend_product_codes', '') }}" placeholder="Enter product codes separated by comma">
        </div>
    </div>
</div>

{{-- Titles and Descriptions --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Product Details</h5>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Title [EN] <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $product ? $product->title : '') }}" required>
            <div class="invalid-feedback"></div>
            @error('title')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Title [AR] <span class="text-danger">*</span></label>
            <input type="text" name="titleAR" class="form-control" value="{{ old('titleAR', $product ? $product->titleAR : '') }}" required>
            <div class="invalid-feedback"></div>
            @error('titleAR')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Short Description [EN] <span class="text-danger">*</span></label>
            <textarea name="shortdescr" class="form-control" rows="3" required>{{ old('shortdescr', $product ? $product->shortdescr : '') }}</textarea>
            <div class="invalid-feedback"></div>
            @error('shortdescr')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Short Description [AR] <span class="text-danger">*</span></label>
            <textarea name="shortdescrAR" class="form-control" rows="3" required>{{ old('shortdescrAR', $product ? $product->shortdescrAR : '') }}</textarea>
            <div class="invalid-feedback"></div>
            @error('shortdescrAR')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Details [EN]</label>
            <div id="longdescr-editor" style="height: 300px;"></div>
            <textarea name="longdescr" id="longdescr" class="form-control d-none" style="display: none;">{{ old('longdescr', $product ? $product->longdescr : '') }}</textarea>
            <div class="invalid-feedback"></div>
            @error('longdescr')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Details [AR]</label>
            <div id="longdescrAR-editor" style="height: 300px;"></div>
            <textarea name="longdescrAR" id="longdescrAR" class="form-control d-none" style="display: none;">{{ old('longdescrAR', $product ? $product->longdescrAR : '') }}</textarea>
            <div class="invalid-feedback"></div>
            @error('longdescrAR')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Size & Quantity Table --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Size & Quantity</h5>
        <div class="table-responsive">
            <table class="table table-bordered" id="sizesTable">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Quantity</th>
                        <th>Barcode</th>
                    </tr>
                </thead>
                <tbody>
                    @if($product && $productSizes->count() > 0)
                        @php
                            $firstSize = $productSizes->first();
                        @endphp
                        <tr>
                            <td>
                                <select name="sizes[][filtervalueid]" class="form-select form-select-sm">
                                    <option value="0">No Size</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->filtervalueid }}" {{ $firstSize->fkfiltervalueid == $size->filtervalueid ? 'selected' : '' }}>
                                            {{ $size->filtervalue }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="sizes[][qty]" class="form-control form-control-sm" min="0" value="{{ $firstSize->qty }}">
                            </td>
                            <td>
                                <input type="text" name="sizes[][barcode]" class="form-control form-control-sm" value="{{ $firstSize->barcode }}">
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td>
                                <select name="sizes[][filtervalueid]" class="form-select form-select-sm">
                                    <option value="0">No Size</option>
                                    @foreach($sizes as $size)
                                        <option value="{{ $size->filtervalueid }}">{{ $size->filtervalue }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="number" name="sizes[][qty]" class="form-control form-control-sm" min="0" value="0">
                            </td>
                            <td>
                                <input type="text" name="sizes[][barcode]" class="form-control form-control-sm">
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <small class="text-muted">Use "No Size" option if product does not have size</small>
    </div>
</div>

{{-- Pricing --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Pricing & Discount</h5>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Product Price [KWD] <span class="text-danger">*</span></label>
            <input type="number" step="0.001" name="price" class="form-control" value="{{ old('price', $product ? $product->price : '') }}" required>
            <div class="invalid-feedback"></div>
            @error('price')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Discount (%)</label>
            <input type="number" step="0.01" name="discount" class="form-control" value="{{ old('discount', $product ? $product->discount : 0) }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Discount Start Date</label>
            <input type="date" name="discount_start_date" class="form-control" value="{{ old('discount_start_date', '') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Discount End Date</label>
            <input type="date" name="discount_end_date" class="form-control" value="{{ old('discount_end_date', '') }}">
        </div>
    </div>
    <div class="col-md-3">
        <div class="mb-3">
            <label class="form-label">Selling Price [KWD] <span class="text-danger">*</span></label>
            <input type="number" step="0.001" name="sellingprice" class="form-control" value="{{ old('sellingprice', $product ? $product->sellingprice : '') }}" required>
            <div class="invalid-feedback"></div>
            @error('sellingprice')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- Status Flags --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Status & Flags</h5>
    </div>
    <div class="col-md-3">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="ispublished" value="1" id="ispublished" {{ old('ispublished', $product ? $product->ispublished : 1) ? 'checked' : '' }}>
            <label class="form-check-label" for="ispublished">Publish</label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="isnew" value="1" id="isnew" {{ old('isnew', $product ? $product->isnew : 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="isnew">New Arrival ?</label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="ispopular" value="1" id="ispopular" {{ old('ispopular', $product ? $product->ispopular : 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="ispopular">Is Popular?</label>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" name="internation_ship" value="1" id="internation_ship" {{ old('internation_ship', $product ? $product->internation_ship : 0) ? 'checked' : '' }}>
            <label class="form-check-label" for="internation_ship">International Ship?</label>
        </div>
    </div>
</div>

{{-- Photos --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Product Photos</h5>
        <p class="text-muted small">Recommended Image Size: 800px X 930px, 900px X 1046px, etc.</p>
    </div>
    @for($i = 1; $i <= 5; $i++)
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Photo {{ $i }} @if($i == 1) <span class="text-danger">*</span> @endif</label>
            <input type="file" name="photo{{ $i }}" id="photo{{ $i }}Input" class="form-control" accept="image/*" onchange="previewProductImage(this, 'photo{{ $i }}Preview')">
            <div class="invalid-feedback"></div>
            
            @if($product && $product->{"photo{$i}"})
                <div class="mt-3 position-relative d-inline-block" id="photo{{ $i }}PreviewContainer">
                    <div class="position-relative" style="width: 150px; height: 150px;">
                        <img src="{{ asset('storage/upload/product/' . $product->{"photo{$i}"}) }}" alt="Photo {{ $i }}" id="photo{{ $i }}Preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px;">
                    </div>
                    <small class="text-muted d-block mt-2">Current: {{ $product->{"photo{$i}"} }}</small>
                </div>
            @else
                <div class="mt-3" id="photo{{ $i }}PreviewContainer" style="display: none;">
                    <div class="position-relative d-inline-block" style="width: 150px; height: 150px;">
                        <img id="photo{{ $i }}Preview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px; display: none;">
                    </div>
                </div>
            @endif
        </div>
    </div>
    @endfor
</div>

{{-- Video --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">Video</h5>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Upload Video Poster</label>
            <input type="file" name="videoposter_file" id="videoposterInput" class="form-control" accept="image/*" onchange="previewProductImage(this, 'videoposterPreview')">
            <div class="invalid-feedback"></div>
            
            @if($product && $product->videoposter)
                <div class="mt-3 position-relative d-inline-block" id="videoposterPreviewContainer">
                    <div class="position-relative" style="width: 150px; height: 150px;">
                        <img src="{{ asset('storage/upload/product/' . $product->videoposter) }}" alt="Video Poster" id="videoposterPreview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px;">
                    </div>
                    <small class="text-muted d-block mt-2">Current: {{ $product->videoposter }}</small>
                </div>
            @else
                <div class="mt-3" id="videoposterPreviewContainer" style="display: none;">
                    <div class="position-relative d-inline-block" style="width: 150px; height: 150px;">
                        <img id="videoposterPreview" style="width: 150px; height: 150px; object-fit: cover; border-radius: 4px; display: none;">
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Upload MP4 Video [MAX 10 MB Size]</label>
            <input type="file" name="video_file" class="form-control" accept="video/mp4">
            <div class="invalid-feedback"></div>
            @if($product && $product->video)
                <small class="text-muted d-block mt-2">Current: {{ $product->video }}</small>
            @endif
        </div>
    </div>
</div>

{{-- SEO Meta Information --}}
<div class="row mb-4">
    <div class="col-12">
        <h5 class="mb-3 border-bottom pb-2">SEO Meta Information</h5>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Title</label>
            <input type="text" name="metatitle" class="form-control" value="{{ old('metatitle', $product ? $product->metatitle : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Title Arabic</label>
            <input type="text" name="metatitleAR" class="form-control" value="{{ old('metatitleAR', $product ? $product->metatitleAR : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Keyword</label>
            <input type="text" name="metakeyword" class="form-control" value="{{ old('metakeyword', $product ? $product->metakeyword : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Keyword Arabic</label>
            <input type="text" name="metakeywordAR" class="form-control" value="{{ old('metakeywordAR', $product ? $product->metakeywordAR : '') }}">
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Description</label>
            <textarea name="metadescr" class="form-control" rows="3">{{ old('metadescr', $product ? $product->metadescr : '') }}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Meta Description Arabic</label>
            <textarea name="metadescrAR" class="form-control" rows="3">{{ old('metadescrAR', $product ? $product->metadescrAR : '') }}</textarea>
        </div>
    </div>
</div>

