<div class="modal fade" id="occassionModal" tabindex="-1" aria-labelledby="occassionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="occassionModalLabel">
                    {{ $occassion ? 'Edit Occasion' : 'Add Occasion' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="occassionForm" method="POST"
                action="{{ $occassion ? route('admin.occassion.update', $occassion->occassionid) : route('admin.occassion.store') }}"
                novalidate enctype="multipart/form-data">
                @csrf
                @if ($occassion)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Occasion Name (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="occassion" class="form-control" placeholder="Occasion Name"
                                    value="{{ old('occassion', $occassion ? $occassion->occassion : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Occasion Name (AR) <span class="text-danger">*</span></label>
                                <input type="text" name="occassionAR" class="form-control" placeholder="Occasion Name (AR)"
                                    value="{{ old('occassionAR', $occassion ? $occassion->occassionAR : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Occasion Code <span class="text-danger">*</span></label>
                                <input type="text" name="occassioncode" class="form-control"
                                    value="{{ old('occassioncode', $occassion ? $occassion->occassioncode : '') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ispublished" value="1"
                                        id="ispublished" {{ old('ispublished', $occassion ? $occassion->ispublished : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ispublished">
                                        Publish
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [EN]</label>
                                <input type="text" name="metatitle" class="form-control" placeholder="Meta Title"
                                    value="{{ old('metatitle', $occassion ? $occassion->metatitle : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Title [AR]</label>
                                <input type="text" name="metatitleAR" class="form-control" placeholder="Meta Title"
                                    value="{{ old('metatitleAR', $occassion ? $occassion->metatitleAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [EN]</label>
                                <input type="text" name="metakeyword" class="form-control" placeholder="Meta Keyword"
                                    value="{{ old('metakeyword', $occassion ? $occassion->metakeyword : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Keyword [AR]</label>
                                <input type="text" name="metakeywordAR" class="form-control" placeholder="Meta Keyword"
                                    value="{{ old('metakeywordAR', $occassion ? $occassion->metakeywordAR : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [EN]</label>
                                <textarea name="metadescr" class="form-control" rows="3" placeholder="Meta Description">{{ old('metadescr', $occassion ? $occassion->metadescr : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Meta Description [AR]</label>
                                <textarea name="metadescrAR" class="form-control" rows="3" placeholder="Meta Description">{{ old('metadescrAR', $occassion ? $occassion->metadescrAR : '') }}</textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Photo</label>
                                <input type="file" name="photo" class="form-control" accept="image/*">
                                @if($occassion && $occassion->photo)
                                    <small class="text-muted">Current: {{ $occassion->photo }}</small>
                                @endif
                                <small class="text-muted d-block">Recommended size: 1440px X 338px</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Photo</label>
                                <input type="file" name="photo_mobile" class="form-control" accept="image/*">
                                @if($occassion && $occassion->photo_mobile)
                                    <small class="text-muted">Current: {{ $occassion->photo_mobile }}</small>
                                @endif
                                <small class="text-muted d-block">Recommended size: 1035px X 375px</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $occassion ? 'Update' : 'Save' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

