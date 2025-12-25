<div class="modal fade" id="sizeModal" tabindex="-1" aria-labelledby="sizeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sizeModalLabel">
                    {{ $size ? 'Edit Size' : 'Add Size' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="sizeForm" method="POST" action="{{ $size ? route('admin.sizes.update', $size->filtervalueid) : route('admin.sizes.store') }}">
                @csrf
                @if($size)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Size Name (EN) <span class="text-danger">*</span></label>
                        <input type="text" name="filtervalue" class="form-control" value="{{ old('filtervalue', $size ? $size->filtervalue : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size Name (AR) <span class="text-danger">*</span></label>
                        <input type="text" name="filtervalueAR" class="form-control" value="{{ old('filtervalueAR', $size ? $size->filtervalueAR : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size Code</label>
                        <input type="text" name="filtervaluecode" class="form-control" value="{{ old('filtervaluecode', $size ? $size->filtervaluecode : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="displayorder" class="form-control" value="{{ old('displayorder', $size ? $size->displayorder : 0) }}" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $size ? $size->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $size ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

