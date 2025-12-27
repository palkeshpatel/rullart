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
                                <input type="text" name="title" class="form-control" 
                                    value="{{ old('title', $homeGallery ? $homeGallery->title : '') }}" required>
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
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                @if($homeGallery && $homeGallery->photo)
                                    <small class="text-muted">Current: {{ $homeGallery->photo }}</small>
                                    <br><img src="{{ asset('uploads/homegallery/' . $homeGallery->photo) }}" 
                                         alt="Current Photo" style="max-width: 100px; margin-top: 5px;">
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (Mobile)</label>
                                <input type="file" name="photo_mobile" class="form-control" accept="image/*">
                                @if($homeGallery && $homeGallery->photo_mobile)
                                    <small class="text-muted">Current: {{ $homeGallery->photo_mobile }}</small>
                                    <br><img src="{{ asset('uploads/homegallery/' . $homeGallery->photo_mobile) }}" 
                                         alt="Current Mobile Photo" style="max-width: 100px; margin-top: 5px;">
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (AR - Desktop)</label>
                                <input type="file" name="photo_ar" class="form-control" accept="image/*">
                                @if($homeGallery && $homeGallery->photo_ar)
                                    <small class="text-muted">Current: {{ $homeGallery->photo_ar }}</small>
                                    <br><img src="{{ asset('uploads/homegallery/' . $homeGallery->photo_ar) }}" 
                                         alt="Current AR Photo" style="max-width: 100px; margin-top: 5px;">
                                @endif
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Photo (AR - Mobile)</label>
                                <input type="file" name="photo_mobile_ar" class="form-control" accept="image/*">
                                @if($homeGallery && $homeGallery->photo_mobile_ar)
                                    <small class="text-muted">Current: {{ $homeGallery->photo_mobile_ar }}</small>
                                    <br><img src="{{ asset('uploads/homegallery/' . $homeGallery->photo_mobile_ar) }}" 
                                         alt="Current AR Mobile Photo" style="max-width: 100px; margin-top: 5px;">
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

