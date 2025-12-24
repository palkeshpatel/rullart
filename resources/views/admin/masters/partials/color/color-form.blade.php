<div class="modal fade" id="colorModal" tabindex="-1" aria-labelledby="colorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorModalLabel">
                    {{ $color ? 'Edit Color' : 'Add Color' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="colorForm" method="POST" action="{{ $color ? route('admin.colors.update', $color->filtervalueid) : route('admin.colors.store') }}">
                @csrf
                @if($color)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Color Name (EN) <span class="text-danger">*</span></label>
                        <input type="text" name="filtervalue" class="form-control" value="{{ old('filtervalue', $color ? $color->filtervalue : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Name (AR)</label>
                        <input type="text" name="filtervalueAR" class="form-control" value="{{ old('filtervalueAR', $color ? $color->filtervalueAR : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color Code</label>
                        <input type="text" name="filtervaluecode" class="form-control" value="{{ old('filtervaluecode', $color ? $color->filtervaluecode : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Display Order</label>
                        <input type="number" name="displayorder" class="form-control" value="{{ old('displayorder', $color ? $color->displayorder : 0) }}" min="0">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $color ? $color->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $color ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

