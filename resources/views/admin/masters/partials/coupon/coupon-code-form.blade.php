<div class="modal fade" id="couponCodeModal" tabindex="-1" aria-labelledby="couponCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponCodeModalLabel">
                    {{ $couponCode ? 'Edit Coupon Code' : 'Add Coupon Code' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="couponCodeForm" method="POST" action="{{ $couponCode ? route('admin.coupon-code.update', $couponCode->couponcodeid) : route('admin.coupon-code.store') }}">
                @csrf
                @if($couponCode)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                <input type="text" name="couponcode" class="form-control" value="{{ old('couponcode', $couponCode ? $couponCode->couponcode : '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="couponvalue" class="form-control" value="{{ old('couponvalue', $couponCode ? $couponCode->couponvalue : '') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="startdate" class="form-control" value="{{ old('startdate', $couponCode && $couponCode->startdate ? \Carbon\Carbon::parse($couponCode->startdate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="enddate" class="form-control" value="{{ old('enddate', $couponCode && $couponCode->enddate ? \Carbon\Carbon::parse($couponCode->enddate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Type ID</label>
                                <input type="number" name="fkcoupontypeid" class="form-control" value="{{ old('fkcoupontypeid', $couponCode ? $couponCode->fkcoupontypeid : 1) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Type</label>
                                <input type="text" name="coupontype" class="form-control" value="{{ old('coupontype', $couponCode ? $couponCode->coupontype : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category ID</label>
                                <input type="text" name="fkcategoryid" class="form-control" value="{{ old('fkcategoryid', $couponCode ? $couponCode->fkcategoryid : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $couponCode ? $couponCode->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">Active</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="ismultiuse" value="1" id="ismultiuse" {{ old('ismultiuse', $couponCode ? $couponCode->ismultiuse : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ismultiuse">Multi Use</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isgeneral" value="1" id="isgeneral" {{ old('isgeneral', $couponCode ? $couponCode->isgeneral : 0) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isgeneral">General</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $couponCode ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

