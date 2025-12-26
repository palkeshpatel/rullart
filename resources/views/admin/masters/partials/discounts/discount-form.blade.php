<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountModalLabel">
                    {{ $discount ? 'Edit Discount' : 'Add Discount' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="discountForm" method="POST" action="{{ $discount ? route('admin.discounts.update', $discount->id) : route('admin.discounts.store') }}" novalidate>
                @csrf
                @if($discount)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Discount Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control" value="{{ old('rate', $discount ? $discount->rate : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="startdate" class="form-control" value="{{ old('startdate', $discount && $discount->startdate ? \Carbon\Carbon::parse($discount->startdate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="enddate" class="form-control" value="{{ old('enddate', $discount && $discount->enddate ? \Carbon\Carbon::parse($discount->enddate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Days</label>
                        <input type="number" min="0" name="days" class="form-control" value="{{ old('days', $discount ? $discount->days : 0) }}">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $discount ? $discount->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $discount ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

