<div class="modal fade" id="giftProduct4Modal" tabindex="-1" aria-labelledby="giftProduct4ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="giftProduct4ModalLabel">
                    {{ $product ? 'Edit Gift Product 4' : 'Add Gift Product 4' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="giftProduct4Form" method="POST"
                action="{{ $product ? route('admin.gift-products4.update', $product->productid) : route('admin.gift-products4.store') }}"
                novalidate enctype="multipart/form-data">
                @csrf
                @if ($product)
                    @method('PUT')
                @endif
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" name="productcode" class="form-control" placeholder="Product Code"
                                    value="{{ old('productcode', $product ? $product->productcode : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Title [EN] <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" placeholder="Title"
                                    value="{{ old('title', $product ? $product->title : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description (EN)</label>
                                <textarea name="shortdescr" class="form-control" rows="3" placeholder="Description">{{ old('shortdescr', $product ? $product->shortdescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Category 1</label>
                                <select name="productcategoryid" id="productCategory1" class="form-select">
                                    <option value="">-Select Product Category-</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ old('productcategoryid', $product && $product->productcategoryid == $cat->categoryid ? $product->productcategoryid : '') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sub Category 1</label>
                                <select name="subcategory1" id="subCategory1" class="form-select">
                                    <option value="">-Select Sub Category-</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Category 2</label>
                                <select name="productcategoryid2" id="productCategory2" class="form-select">
                                    <option value="">-Select Product Category-</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ old('productcategoryid2', $product && $product->productcategoryid2 == $cat->categoryid ? $product->productcategoryid2 : '') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sub Category 2</label>
                                <select name="subcategory2" id="subCategory2" class="form-select">
                                    <option value="">-Select Sub Category-</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Category 3</label>
                                <select name="productcategoryid3" id="productCategory3" class="form-select">
                                    <option value="">-Select Product Category-</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ old('productcategoryid3', $product && $product->productcategoryid3 == $cat->categoryid ? $product->productcategoryid3 : '') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sub Category 3</label>
                                <select name="subcategory3" id="subCategory3" class="form-select">
                                    <option value="">-Select Sub Category-</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Gift Product Category 4 <span class="text-danger">*</span></label>
                                <select name="productcategoryid4" id="productCategory4" class="form-select">
                                    <option value="">-Select Gift Product Category 4-</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->categoryid }}"
                                            {{ old('productcategoryid4', $product && $product->productcategoryid4 == $cat->categoryid ? $product->productcategoryid4 : '') == $cat->categoryid ? 'selected' : '' }}>
                                            {{ $cat->category }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Barcode</label>
                                <input type="text" name="barcode" class="form-control" placeholder="barcode"
                                    value="{{ old('barcode', '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="quantity" class="form-control"
                                    value="{{ old('quantity', '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Occasion</label>
                                <select name="occasion" class="form-select">
                                    <option value="">-Select Occasion-</option>
                                    @foreach ($occasions ?? [] as $occasion)
                                        <option value="{{ $occasion->occassionid }}"
                                            {{ old('occasion', isset($productFilters) && isset($productFilters['occassion']) && $productFilters['occassion']->count() > 0 && $productFilters['occassion']->first()->fkfiltervalueid == $occasion->occassionid ? $productFilters['occassion']->first()->fkfiltervalueid : '') == $occasion->occassionid ? 'selected' : '' }}>
                                            {{ $occasion->occassion }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Product Price [KWD] <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="price" class="form-control" placeholder="Price"
                                    value="{{ old('price', $product ? $product->price : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Discount (%)</label>
                                <input type="number" step="0.01" name="discount" class="form-control" placeholder="Discount"
                                    value="{{ old('discount', $product ? $product->discount : 0) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Selling Price [KWD] <span class="text-danger">*</span></label>
                                <input type="number" step="0.001" name="sellingprice" class="form-control" placeholder="selling price"
                                    value="{{ old('sellingprice', $product ? $product->sellingprice : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        
                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <select name="color" class="form-select">
                                    <option value="">-Select Color-</option>
                                    @foreach ($colors ?? [] as $color)
                                        <option value="{{ $color->filtervalueid }}"
                                            {{ old('color', isset($productFilters) && isset($productFilters['color']) && $productFilters['color']->count() > 0 && $productFilters['color']->first()->fkfiltervalueid == $color->filtervalueid ? $productFilters['color']->first()->fkfiltervalueid : '') == $color->filtervalueid ? 'selected' : '' }}>
                                            {{ $color->filtervalue }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Title [AR] <span class="text-danger">*</span></label>
                                <input type="text" name="titleAR" class="form-control" placeholder="Title (AR)"
                                    value="{{ old('titleAR', $product ? $product->titleAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description (AR)</label>
                                <textarea name="shortdescrAR" class="form-control" rows="3" placeholder="Long Description (AR)">{{ old('shortdescrAR', $product ? $product->shortdescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Recommended Image Size</label>
                                <div class="form-control-plaintext">800px X 930px, 900px X 1046px, 1000px X 1162px, 1100px X 1278px, 1200px X 1395px, 1300px X 1511px, 1400px X 1627px, 1500px X 1743px</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Photo 1 (Main Image)</label>
                                <input type="file" name="photo1" id="photo1Input" class="form-control"
                                    accept="image/*" onchange="previewImage4(this, 'photo1Preview')">
                                @if ($product && $product->photo1)
                                    <div class="mt-3 position-relative d-inline-block" id="photo1PreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/product/' . $product->photo1) }}"
                                                alt="Photo 1" id="photo1Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-product-id="{{ $product->productid }}" data-column="photo1"
                                                data-image-name="{{ $product->photo1 }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $product->photo1 }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photo1PreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block" style="width: 100px; height: 100px;">
                                            <img id="photo1Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Photo 2 (Gift Box)</label>
                                <input type="file" name="photo2" id="photo2Input" class="form-control"
                                    accept="image/*" onchange="previewImage4(this, 'photo2Preview')">
                                @if ($product && $product->photo2)
                                    <div class="mt-3 position-relative d-inline-block" id="photo2PreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/product/' . $product->photo2) }}"
                                                alt="Photo 2" id="photo2Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-product-id="{{ $product->productid }}" data-column="photo2"
                                                data-image-name="{{ $product->photo2 }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $product->photo2 }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photo2PreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block" style="width: 100px; height: 100px;">
                                            <img id="photo2Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Upload Photo 3 (Chocolate)</label>
                                <input type="file" name="photo3" id="photo3Input" class="form-control"
                                    accept="image/*" onchange="previewImage4(this, 'photo3Preview')">
                                @if ($product && $product->photo3)
                                    <div class="mt-3 position-relative d-inline-block" id="photo3PreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/product/' . $product->photo3) }}"
                                                alt="Photo 3" id="photo3Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-product-id="{{ $product->productid }}" data-column="photo3"
                                                data-image-name="{{ $product->photo3 }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $product->photo3 }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photo3PreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block" style="width: 100px; height: 100px;">
                                            <img id="photo3Preview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Meta Title</label>
                                <input type="text" name="metatitle" class="form-control" placeholder="Meta Title"
                                    value="{{ old('metatitle', $product ? $product->metatitle : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword</label>
                                <input type="text" name="metakeyword" class="form-control" placeholder="Meta Keyword"
                                    value="{{ old('metakeyword', $product ? $product->metakeyword : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <input type="text" name="metadescr" class="form-control" placeholder="Meta Description"
                                    value="{{ old('metadescr', $product ? $product->metadescr : '') }}">
                                <div class="invalid-feedback"></div>
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

<!-- Delete Image Confirmation Modal -->
<div class="modal fade" id="deleteImageModal4" tabindex="-1" aria-labelledby="deleteImageModal4Label"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteImageModal4Label">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this image?</p>
                <p class="text-muted small mb-0" id="deleteImageName4"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteImageBtn4">
                    <i class="ti ti-trash me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Load subcategories for product categories
    function loadSubcategories4(categoryId, subCategorySelectId) {
        const $subCategorySelect = $('#' + subCategorySelectId);
        $subCategorySelect.html('<option value="">-Select Sub Category-</option>');
        $subCategorySelect.prop('disabled', true);
        
        if (!categoryId) {
            $subCategorySelect.prop('disabled', false);
            return;
        }
        
        $.ajax({
            url: '{{ route('admin.products.subcategories') }}',
            method: 'GET',
            data: { category_id: categoryId },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    $.each(response.data, function(index, subcategory) {
                        $subCategorySelect.append(
                            '<option value="' + subcategory.categoryid + '">' + subcategory.category + '</option>'
                        );
                    });
                }
                $subCategorySelect.prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error fetching subcategories:', xhr);
                $subCategorySelect.prop('disabled', false);
            }
        });
    }
    
    // Initialize subcategory loading for all product categories
    $(document).ready(function() {
        // Product Category 1
        $('#productCategory1').on('change', function() {
            loadSubcategories4($(this).val(), 'subCategory1');
        });
        
        // Product Category 2
        $('#productCategory2').on('change', function() {
            loadSubcategories4($(this).val(), 'subCategory2');
        });
        
        // Product Category 3
        $('#productCategory3').on('change', function() {
            loadSubcategories4($(this).val(), 'subCategory3');
        });
        
        // Load subcategories on page load if product exists
        @if(isset($product) && $product)
            @if($product->productcategoryid)
                setTimeout(function() {
                    $('#productCategory1').val({{ $product->productcategoryid }}).trigger('change');
                    setTimeout(function() {
                        @if($product->productcategoryid2)
                            $('#subCategory1').val({{ $product->productcategoryid2 }});
                        @endif
                    }, 500);
                }, 100);
            @endif
            @if($product->productcategoryid3)
                setTimeout(function() {
                    $('#productCategory3').val({{ $product->productcategoryid3 }}).trigger('change');
                }, 300);
            @endif
        @endif
    });
    
    // Setup jQuery validation
    (function() {
        function initGiftProduct4Validation() {
            if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                setTimeout(initGiftProduct4Validation, 50);
                return;
            }

            const $ = jQuery;
            const $form = $('#giftProduct4Form');
            
            if (!$form.length || $form.data('validator')) {
                return;
            }

            $form.validate({
                rules: {
                    productcode: {
                        required: true
                    },
                    title: {
                        required: true
                    },
                    titleAR: {
                        required: true
                    },
                    price: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    sellingprice: {
                        required: true,
                        number: true,
                        min: 0
                    },
                    productcategoryid4: {
                        required: true
                    }
                },
                messages: {
                    productcode: 'Product Code is required.',
                    title: 'Title [EN] is required.',
                    titleAR: 'Title [AR] is required.',
                    price: 'Product Price [KWD] is required and must be a valid number.',
                    sellingprice: 'Selling Price [KWD] is required and must be a valid number.',
                    productcategoryid4: 'Gift Product Category 4 is required.'
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function(el) {
                    $(el).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(el) {
                    $(el).removeClass('is-invalid').addClass('is-valid');
                },
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function(form) {
                    // Submit via AJAX to handle redirect properly
                    const formData = new FormData(form);
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> {{ $product ? "Updating..." : "Saving..." }}';
                    
                    // Ensure CSRF token is included
                    const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                    if (csrfToken) {
                        formData.append('_token', csrfToken);
                        if ($('input[name="_method"]').length) {
                            formData.append('_method', 'PUT');
                        }
                    }
                    
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken || ''
                        },
                        success: function(response) {
                            if (response.success) {
                                if (typeof showToast === 'function') {
                                    showToast(response.message || 'Gift product 4 saved successfully', 'success');
                                } else if (typeof toastr !== 'undefined') {
                                    toastr.success(response.message || 'Gift product 4 saved successfully');
                                }
                                // Close modal and reload table
                                const modal = bootstrap.Modal.getInstance(document.getElementById('giftProduct4Modal'));
                                if (modal) modal.hide();
                                if (typeof table !== 'undefined' && table) {
                                    table.ajax.reload();
                                } else {
                                    setTimeout(function() {
                                        window.location.href = response.redirect || '{{ route("admin.gift-products4") }}';
                                    }, 1000);
                                }
                            } else {
                                if (typeof showToast === 'function') {
                                    showToast(response.message || 'Failed to save gift product 4', 'error');
                                } else if (typeof toastr !== 'undefined') {
                                    toastr.error(response.message || 'Failed to save gift product 4');
                                }
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            }
                        },
                        error: function(xhr) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                            
                            if (xhr.status === 422) {
                                // Validation errors
                                const errors = xhr.responseJSON?.errors || {};
                                $.each(errors, function(field, messages) {
                                    const input = form.querySelector('[name="' + field + '"]');
                                    if (input) {
                                        $(input).addClass('is-invalid').removeClass('is-valid');
                                        const errorDiv = input.nextElementSibling;
                                        if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                            $(errorDiv).text(Array.isArray(messages) ? messages[0] : messages);
                                        }
                                    }
                                });
                                if (typeof showToast === 'function') {
                                    showToast('Please fix the validation errors', 'error');
                                } else if (typeof toastr !== 'undefined') {
                                    toastr.error('Please fix the validation errors');
                                }
                            } else if (xhr.status === 419) {
                                // CSRF token mismatch
                                const message = 'Session expired. Please refresh the page and try again.';
                                if (typeof showToast === 'function') {
                                    showToast(message, 'error');
                                } else if (typeof toastr !== 'undefined') {
                                    toastr.error(message);
                                } else {
                                    alert('Error: ' + message);
                                }
                                setTimeout(function() {
                                    window.location.reload();
                                }, 2000);
                            } else {
                                const message = xhr.responseJSON?.message || 'An error occurred while saving the gift product 4';
                                if (typeof showToast === 'function') {
                                    showToast(message, 'error');
                                } else if (typeof toastr !== 'undefined') {
                                    toastr.error(message);
                                } else {
                                    alert('Error: ' + message);
                                }
                            }
                        }
                    });
                    
                    return false; // Prevent default form submission
                }
            });
        }

        // Initialize when modal is shown
        $(document).ready(function() {
            // Initialize validation when modal is shown
            $('#giftProduct4Modal').on('shown.bs.modal', function() {
                initGiftProduct4Validation();
            });
            
            // Also try to initialize immediately if modal is already open
            if ($('#giftProduct4Modal').hasClass('show')) {
                initGiftProduct4Validation();
            } else {
                initGiftProduct4Validation();
            }
        });
    })();
    
    // Image preview function
    function previewImage4(input, previewId) {
        const preview = document.getElementById(previewId);
        let containerId = previewId + 'Container';
        const container = document.getElementById(containerId);

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.style.width = '100px';
                preview.style.height = '100px';
                preview.style.objectFit = 'cover';
                preview.style.borderRadius = '4px';
                if (container) {
                    container.style.display = 'block';
                }
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    // Delete image handler - using global event delegation (works with dynamically loaded content)
    (function() {
        // Delete button click - attach to document for dynamic content
        $(document).on('click', '#giftProduct4Modal .remove-image-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const button = $(this);
            const productId = button.data('product-id');
            const column = button.data('column');
            const imageName = button.data('image-name');
            const container = button.closest('[id$="PreviewContainer"]');

            if (!productId || !column) {
                console.error('Missing product ID or column');
                return;
            }

            // Set data for confirmation modal
            const modal = $('#deleteImageModal4');
            modal.data('product-id', productId);
            modal.data('column', column);
            modal.data('container', container);
            $('#deleteImageName4').text('Image: ' + imageName);

            // Show confirmation modal
            const deleteModal = new bootstrap.Modal(modal[0]);
            deleteModal.show();
        });

        // Confirm delete button click - attach to document
        $(document).on('click', '#confirmDeleteImageBtn4', function() {
            const modal = $('#deleteImageModal4');
            const productId = modal.data('product-id');
            const column = modal.data('column');
            const container = modal.data('container');
            const confirmBtn = $(this);

            if (!productId || !column) {
                console.error('Missing product ID or column');
                return;
            }

            // Show loading state
            const originalHtml = confirmBtn.html();
            confirmBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

            // Make AJAX request
            $.ajax({
                url: '{{ route('admin.gift-products4.remove-image', ':id') }}'.replace(':id',
                    productId),
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    column: column
                },
                success: function(response) {
                    if (response.success) {
                        // Hide the image preview container
                        if (container && container.length) {
                            container.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }

                        // Clear the file input
                        const inputId = '#' + column + 'Input';
                        $(inputId).val('');
                        const previewContainer = $('#' + column + 'PreviewContainer');
                        if (previewContainer.length) previewContainer.hide();

                        // Show success message
                        if (typeof toastr !== 'undefined') {
                            toastr.success(response.message || 'Image removed successfully');
                        } else {
                            alert('Image removed successfully');
                        }

                        // Close modal
                        const modalInstance = bootstrap.Modal.getInstance(modal[0]);
                        if (modalInstance) {
                            modalInstance.hide();
                        }
                    } else {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(response.message || 'Error removing image');
                        } else {
                            alert('Error: ' + (response.message || 'Error removing image'));
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    const message = xhr.responseJSON?.message || 'Error removing image';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message);
                    } else {
                        alert('Error: ' + message);
                    }
                },
                complete: function() {
                    // Reset button state
                    confirmBtn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    })();
</script>
