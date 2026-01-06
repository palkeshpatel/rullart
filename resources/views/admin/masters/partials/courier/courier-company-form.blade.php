<div class="modal fade" id="courierModal" tabindex="-1" aria-labelledby="courierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courierModalLabel">
                    {{ $courierCompany ? 'Edit Courier Company' : 'Add Courier Company' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="courierForm" method="POST" action="{{ $courierCompany ? route('admin.courier-company.update', $courierCompany->id) : route('admin.courier-company.store') }}" novalidate>
                @csrf
                @if($courierCompany)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Courier Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $courierCompany ? $courierCompany->name : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tracking URL</label>
                        <input type="url" name="tracking_url" id="tracking_url" class="form-control" value="{{ old('tracking_url', $courierCompany ? $courierCompany->tracking_url : '') }}" placeholder="https://example.com/track/">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $courierCompany ? $courierCompany->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Is Active?
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

