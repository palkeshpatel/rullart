<div class="modal fade" id="couponCodeModal" tabindex="-1" aria-labelledby="couponCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponCodeModalLabel">
                    {{ $couponCode ? 'Edit Coupon Code' : 'Add Coupon Code' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="couponCodeForm" method="POST" action="{{ $couponCode ? route('admin.coupon-code.update', $couponCode->couponcodeid) : route('admin.coupon-code.store') }}" novalidate>
                @csrf
                @if($couponCode)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                                <input type="text" name="couponcode" class="form-control" 
                                    value="{{ old('couponcode', $couponCode ? $couponCode->couponcode : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon Type</label>
                                <select name="fkcoupontypeid" id="couponTypeSelect" class="form-select">
                                    <option value="">--Select Coupon Type--</option>
                                    @foreach ($couponTypes ?? [] as $type)
                                        <option value="{{ $type->coupontypeid }}"
                                            {{ old('fkcoupontypeid', $couponCode && $couponCode->fkcoupontypeid == $type->coupontypeid ? $couponCode->fkcoupontypeid : '') == $type->coupontypeid ? 'selected' : '' }}>
                                            {{ $type->coupontype }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="coupontype" id="couponTypeText" value="{{ old('coupontype', $couponCode ? $couponCode->coupontype : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Coupon value (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="couponvalue" class="form-control" 
                                    value="{{ old('couponvalue', $couponCode ? $couponCode->couponvalue : '') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="startdate" class="form-control" 
                                    value="{{ old('startdate', $couponCode && $couponCode->startdate ? \Carbon\Carbon::parse($couponCode->startdate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="enddate" class="form-control" 
                                    value="{{ old('enddate', $couponCode && $couponCode->enddate ? \Carbon\Carbon::parse($couponCode->enddate)->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" 
                                        {{ old('isactive', $couponCode ? $couponCode->isactive : 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isactive">Is Active?</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="ismultiuse" value="1" id="ismultiuse" 
                                        {{ old('ismultiuse', $couponCode ? $couponCode->ismultiuse : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="ismultiuse">Allow Multiple Use?</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="isgeneral" value="1" id="isgeneral" 
                                        {{ old('isgeneral', $couponCode ? $couponCode->isgeneral : 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isgeneral">For all customer?</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category?</label>
                                <select name="fkcategoryid" id="categorySelect" class="form-select">
                                    <option value="">ALL</option>
                                    @foreach ($categories ?? [] as $category)
                                        <option value="{{ $category->categoryid }}"
                                            {{ old('fkcategoryid', $couponCode && $couponCode->fkcategoryid == $category->categoryid ? $couponCode->fkcategoryid : '') == $category->categoryid ? 'selected' : '' }}>
                                            {{ $category->category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
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

<script>
    // Update coupontype text field when coupon type dropdown changes
    $(document).ready(function() {
        $('#couponTypeSelect').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const couponTypeText = selectedOption.text();
            $('#couponTypeText').val(couponTypeText);
        });
        
        // Initialize on load if editing
        @if($couponCode && $couponCode->fkcoupontypeid)
            $('#couponTypeSelect').trigger('change');
        @endif
    });
</script>
