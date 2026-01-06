<div class="modal fade" id="homeGalleryModal" tabindex="-1" aria-labelledby="homeGalleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="homeGalleryModalLabel">
                    {{ $homeGallery ? 'Edit Photo' : 'Add Photo' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="homeGalleryForm" method="POST" 
                action="{{ $homeGallery ? route('admin.home-gallery.update', $homeGallery->homegalleryid) : route('admin.home-gallery.store') }}" 
                novalidate enctype="multipart/form-data">
                @csrf
                @if($homeGallery)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title(EN) <span class="text-danger">*</span></label>
                                <input type="text" name="title" id="title" class="form-control" 
                                    value="{{ old('title', $homeGallery ? $homeGallery->title : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Title(AR)</label>
                                <input type="text" name="titleAR" class="form-control" 
                                    value="{{ old('titleAR', $homeGallery ? $homeGallery->titleAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Description(EN)</label>
                                <textarea name="descr" class="form-control" rows="3">{{ old('descr', $homeGallery ? $homeGallery->descr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Description(AR)</label>
                                <textarea name="descrAR" class="form-control" rows="3">{{ old('descrAR', $homeGallery ? $homeGallery->descrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Link</label>
                                <input type="url" name="link" class="form-control" 
                                    value="{{ old('link', $homeGallery ? $homeGallery->link : '') }}" 
                                    placeholder="https://example.com">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Video URL</label>
                                <input type="url" name="videourl" class="form-control" 
                                    value="{{ old('videourl', $homeGallery ? $homeGallery->videourl : '') }}" 
                                    placeholder="https://youtube.com/watch?v=...">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (Desktop)</label>
                                <input type="file" name="photo" id="photoInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoPreview')">

                                @if ($homeGallery && $homeGallery->photo)
                                    <div class="mt-3 position-relative d-inline-block" id="photoPreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo) }}"
                                                alt="Desktop Photo" id="photoPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-homegallery-id="{{ $homeGallery->homegalleryid }}" data-column="photo"
                                                data-image-name="{{ $homeGallery->photo }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $homeGallery->photo }}</small>
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

                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (Mobile)</label>
                                <input type="file" name="photo_mobile" id="photoMobileInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoMobilePreview')">

                                @if ($homeGallery && $homeGallery->photo_mobile)
                                    <div class="mt-3 position-relative d-inline-block"
                                        id="photoMobilePreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_mobile) }}"
                                                alt="Mobile Photo" id="photoMobilePreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-homegallery-id="{{ $homeGallery->homegalleryid }}"
                                                data-column="photo_mobile"
                                                data-image-name="{{ $homeGallery->photo_mobile }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current:
                                            {{ $homeGallery->photo_mobile }}</small>
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

                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (AR - Desktop)</label>
                                <input type="file" name="photo_ar" id="photoArInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoArPreview')">

                                @if ($homeGallery && $homeGallery->photo_ar)
                                    <div class="mt-3 position-relative d-inline-block" id="photoArPreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_ar) }}"
                                                alt="AR Desktop Photo" id="photoArPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-homegallery-id="{{ $homeGallery->homegalleryid }}" data-column="photo_ar"
                                                data-image-name="{{ $homeGallery->photo_ar }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current: {{ $homeGallery->photo_ar }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photoArPreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block"
                                            style="width: 100px; height: 100px;">
                                            <img id="photoArPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif

                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (AR - Mobile)</label>
                                <input type="file" name="photo_mobile_ar" id="photoMobileArInput" class="form-control"
                                    accept="image/*" onchange="previewImage(this, 'photoMobileArPreview')">

                                @if ($homeGallery && $homeGallery->photo_mobile_ar)
                                    <div class="mt-3 position-relative d-inline-block"
                                        id="photoMobileArPreviewContainer">
                                        <div class="position-relative" style="width: 100px; height: 100px;">
                                            <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_mobile_ar) }}"
                                                alt="AR Mobile Photo" id="photoMobileArPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                                            <button type="button"
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 remove-image-btn"
                                                data-homegallery-id="{{ $homeGallery->homegalleryid }}"
                                                data-column="photo_mobile_ar"
                                                data-image-name="{{ $homeGallery->photo_mobile_ar }}" title="Remove Image"
                                                style="z-index: 10; padding: 2px 6px; font-size: 12px;">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">Current:
                                            {{ $homeGallery->photo_mobile_ar }}</small>
                                    </div>
                                @else
                                    <div class="mt-3" id="photoMobileArPreviewContainer" style="display: none;">
                                        <div class="position-relative d-inline-block"
                                            style="width: 100px; height: 100px;">
                                            <img id="photoMobileArPreview"
                                                style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; display: none;">
                                        </div>
                                    </div>
                                @endif

                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Display Order</label>
                                <input type="number" name="displayorder" class="form-control" 
                                    value="{{ old('displayorder', $homeGallery ? $homeGallery->displayorder : 0) }}" 
                                    min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input class="form-check-input" type="checkbox" name="ispublished" value="1" 
                                        id="ispublished" {{ old('ispublished', $homeGallery ? $homeGallery->ispublished : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispublished">
                                        Published
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $homeGallery ? 'Update' : 'Create' }}
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
            const homegalleryId = button.data('homegallery-id');
            const column = button.data('column');
            const imageName = button.data('image-name');
            const container = button.closest('[id$="PreviewContainer"]');

            if (!homegalleryId || !column) {
                console.error('Missing homegallery ID or column');
                return;
            }

            // Set data for confirmation modal
            const modal = $('#deleteImageModal');
            modal.data('homegallery-id', homegalleryId);
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
            const homegalleryId = modal.data('homegallery-id');
            const column = modal.data('column');
            const container = modal.data('container');
            const confirmBtn = $(this);

            if (!homegalleryId || !column) {
                console.error('Missing homegallery ID or column');
                return;
            }

            // Show loading state
            const originalHtml = confirmBtn.html();
            confirmBtn.prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-1"></span> Deleting...');

            // Make AJAX request
            $.ajax({
                url: '{{ route('admin.home-gallery.remove-image', ':id') }}'.replace(':id',
                    homegalleryId),
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

