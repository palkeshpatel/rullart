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
                    <h6 class="mb-3 border-bottom pb-2 bg-primary text-white p-2 rounded">Basic Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="fkcategoryid" class="form-select">
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
                                <label class="form-label">Product Code</label>
                                <input type="text" name="productcode" class="form-control"
                                    value="{{ old('productcode', $product ? $product->productcode : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title [EN] <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $product ? $product->title : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title [AR] <span class="text-danger">*</span></label>
                                <input type="text" name="titleAR" class="form-control"
                                    value="{{ old('titleAR', $product ? $product->titleAR : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Descriptions -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">Product Details</h6>
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
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">Pricing</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.001" name="price" class="form-control"
                                    value="{{ old('price', $product ? $product->price : '') }}">
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
                                <label class="form-label">Selling Price</label>
                                <input type="number" step="0.001" name="sellingprice" class="form-control"
                                    value="{{ old('sellingprice', $product ? $product->sellingprice : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Information -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">SEO Meta Information
                    </h6>
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
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">Product Photos</h6>
                    <div class="row">
                        @for ($i = 1; $i <= 5; $i++)
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Photo {{ $i }}</label>
                                    <input type="file" name="photo{{ $i }}"
                                        id="photo{{ $i }}Input" class="form-control" accept="image/*"
                                        onchange="previewImage(this, 'photo{{ $i }}Preview')">

                                    @if ($product && $product->{"photo{$i}"})
                                        <div class="mt-3 position-relative d-inline-block"
                                            id="photo{{ $i }}PreviewContainer">
                                            <div class="position-relative" style="width: 100px; height: 100px;">
                                                <img src="{{ asset('storage/upload/product/' . $product->{"photo{$i}"}) }}"
                                                    alt="Photo {{ $i }}"
                                                    id="photo{{ $i }}Preview"
                                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                                <button type="button"
                                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                    data-product-id="{{ $product->productid }}"
                                                    data-column="photo{{ $i }}"
                                                    data-image-name="{{ $product->{"photo{$i}"} }}"
                                                    title="Remove Image"
                                                    style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                            <small class="text-muted d-block mt-2">Current:
                                                {{ $product->{"photo{$i}"} }}</small>
                                        </div>
                                    @else
                                        <div class="mt-3" id="photo{{ $i }}PreviewContainer"
                                            style="display: none;">
                                            <div class="position-relative d-inline-block"
                                                style="width: 100px; height: 100px;">
                                                <img id="photo{{ $i }}Preview"
                                                    style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                            </div>
                                        </div>
                                    @endif

                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <!-- Video -->
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">Video</h6>
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
                    <h6 class="mb-3 mt-4 border-bottom pb-2 bg-primary text-white p-2 rounded">Status & Flags</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispublished"
                                        value="1" id="ispublished"
                                        {{ old('ispublished', $product ? $product->ispublished : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispublished">Published</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isnew" value="1"
                                        id="isnew"
                                        {{ old('isnew', $product ? $product->isnew : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isnew">New Product</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispopular" value="1"
                                        id="ispopular"
                                        {{ old('ispopular', $product ? $product->ispopular : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispopular">Popular</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isgift" value="1"
                                        id="isgift"
                                        {{ old('isgift', $product ? $product->isgift : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isgift">Is Gift</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="internation_ship"
                                        value="1" id="internation_ship"
                                        {{ old('internation_ship', $product ? $product->internation_ship : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="internation_ship">International
                                        Shipping</label>
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

<!-- Delete Image Confirmation Modal -->
<div class="modal fade" id="deleteImageModal" tabindex="-1" aria-labelledby="deleteImageModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteImageModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to remove this image?</p>
                <p class="text-muted small mb-0" id="deleteImageName"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No, Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteImageBtn">
                    <i class="ti ti-trash me-1"></i> Yes, Delete
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Image preview function
    function previewImage(input, previewId) {
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
        $(document).on('click', '.remove-image-btn', function(e) {
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
            const modal = $('#deleteImageModal');
            modal.data('product-id', productId);
            modal.data('column', column);
            modal.data('container', container);
            $('#deleteImageName').text('Image: ' + imageName);

            // Show confirmation modal
            const deleteModal = new bootstrap.Modal(modal[0]);
            deleteModal.show();
        });

        // Confirm delete button click - attach to document
        $(document).on('click', '#confirmDeleteImageBtn', function() {
            const modal = $('#deleteImageModal');
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
                url: '{{ route('admin.products.remove-image', ':id') }}'.replace(':id',
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
