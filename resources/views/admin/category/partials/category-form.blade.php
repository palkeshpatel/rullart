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
                                    value="{{ old('categorycode', $category ? $category->categorycode : '') }}"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Parent Category <span class="text-danger">*</span></label>
                                <select name="parentid" class="form-select" required>
                                    <option value="">--PARENT--</option>
                                    <option value="0"
                                        {{ old('parentid', $category ? $category->parentid ?? 0 : '') == 0 ? 'selected' : '' }}>
                                        No Parent (Main Category)</option>
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
                                <input type="text" name="metakeywordAR" class="form-control"
                                    placeholder="Meta Keyword"
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
                                    <input class="form-check-input" type="checkbox" name="ispublished"
                                        value="1" id="ispublished"
                                        {{ old('ispublished', $category ? $category->ispublished : 1) ? 'checked' : '' }}>
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
                                    value="{{ old('displayorder', $category ? $category->displayorder ?? 0 : 0) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Desktop Photo</label>
                                <input type="file" name="photo" id="photoInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoPreview')">

                                @if ($category && $category->photo)
                                    <div class="mt-3 position-relative d-inline-block" id="photoPreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/category/' . $category->photo) }}"
                                                alt="Desktop Photo" id="photoPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-category-id="{{ $category->categoryid }}" data-column="photo"
                                                data-image-name="{{ $category->photo }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $category->photo }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photoPreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block"
                                            style="width: 100px; height: 100px;">
                                            <img id="photoPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif

                                <small class="text-muted d-block mt-2">Recommended size: 1440px X 338px</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Mobile Photo</label>
                                <input type="file" name="photo_mobile" id="photoMobileInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoMobilePreview')">

                                @if ($category && $category->photo_mobile)
                                    <div class="mt-3 position-relative d-inline-block"
                                        id="photoMobilePreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/category/' . $category->photo_mobile) }}"
                                                alt="Mobile Photo" id="photoMobilePreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-category-id="{{ $category->categoryid }}"
                                                data-column="photo_mobile"
                                                data-image-name="{{ $category->photo_mobile }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current:
                                            {{ $category->photo_mobile }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photoMobilePreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block"
                                            style="width: 100px; height: 100px;">
                                            <img id="photoMobilePreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif

                                <small class="text-muted d-block mt-2">Recommended size: 990px X 467px</small>
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
        const container = input.id === 'photoInput' ? document.getElementById('photoPreviewContainer') : document
            .getElementById('photoMobilePreviewContainer');
        const preview = document.getElementById(previewId);

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
            const categoryId = button.data('category-id');
            const column = button.data('column');
            const imageName = button.data('image-name');
            const container = button.closest('#photoPreviewContainer, #photoMobilePreviewContainer');

            if (!categoryId || !column) {
                console.error('Missing category ID or column');
                return;
            }

            // Set data for confirmation modal
            const modal = $('#deleteImageModal');
            modal.data('category-id', categoryId);
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
            const categoryId = modal.data('category-id');
            const column = modal.data('column');
            const container = modal.data('container');
            const confirmBtn = $(this);

            if (!categoryId || !column) {
                console.error('Missing category ID or column');
                return;
            }

            // Show loading state
            const originalHtml = confirmBtn.html();
            confirmBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

            // Make AJAX request
            $.ajax({
                url: '{{ route('admin.category.remove-image', ':id') }}'.replace(':id',
                    categoryId),
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
                        if (column === 'photo') {
                            $('#photoInput').val('');
                            const previewContainer = $('#photoPreviewContainer');
                            if (previewContainer.length) previewContainer.hide();
                        } else {
                            $('#photoMobileInput').val('');
                            const previewContainer = $('#photoMobilePreviewContainer');
                            if (previewContainer.length) previewContainer.hide();
                        }

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
