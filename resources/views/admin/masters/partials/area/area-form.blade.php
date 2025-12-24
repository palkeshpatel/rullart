<div class="modal fade" id="areaModal" tabindex="-1" aria-labelledby="areaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="areaModalLabel">
                    {{ $area ? 'Edit Area' : 'Add Area' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="areaForm" method="POST" action="{{ $area ? route('admin.areas.update', $area->areaid) : route('admin.areas.store') }}">
                @csrf
                @if($area)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="fkcountryid" class="form-select" required>
                            <option value="">Select Country</option>
                            @foreach($countries ?? [] as $country)
                                <option value="{{ $country->countryid }}" {{ ($area && $area->fkcountryid == $country->countryid) ? 'selected' : '' }}>
                                    {{ $country->countryname }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area Name (EN) <span class="text-danger">*</span></label>
                        <input type="text" name="areaname" class="form-control" value="{{ old('areaname', $area ? $area->areaname : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Area Name (AR)</label>
                        <input type="text" name="areanameAR" class="form-control" value="{{ old('areanameAR', $area ? $area->areanameAR : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $area ? $area->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $area ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

