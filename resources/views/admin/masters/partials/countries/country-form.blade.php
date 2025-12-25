<div class="modal fade" id="countryModal" tabindex="-1" aria-labelledby="countryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="countryModalLabel">
                    {{ $country ? 'Edit Country' : 'Add Country' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="countryForm" method="POST" action="{{ $country ? route('admin.countries.update', $country->countryid) : route('admin.countries.store') }}">
                @csrf
                @if($country)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Country Name (EN) <span class="text-danger">*</span></label>
                                <input type="text" name="countryname" class="form-control" value="{{ old('countryname', $country ? $country->countryname : '') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Country Name (AR) <span class="text-danger">*</span></label>
                                <input type="text" name="countrynameAR" class="form-control" value="{{ old('countrynameAR', $country ? $country->countrynameAR : '') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">ISO Code</label>
                                <input type="text" name="isocode" class="form-control" value="{{ old('isocode', $country ? $country->isocode : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency Code</label>
                                <input type="text" name="currencycode" class="form-control" value="{{ old('currencycode', $country ? $country->currencycode : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency Rate</label>
                                <input type="number" step="0.000001" name="currencyrate" class="form-control" value="{{ old('currencyrate', $country ? $country->currencyrate : 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Currency Symbol</label>
                                <input type="text" name="currencysymbol" class="form-control" value="{{ old('currencysymbol', $country ? $country->currencysymbol : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shipping Charge</label>
                                <input type="number" step="0.01" name="shipping_charge" class="form-control" value="{{ old('shipping_charge', $country ? $country->shipping_charge : 0) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Free Shipping Over</label>
                                <input type="number" step="0.001" name="free_shipping_over" class="form-control" value="{{ old('free_shipping_over', $country ? $country->free_shipping_over : 0) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shipping Days</label>
                                <input type="text" name="shipping_days" class="form-control" value="{{ old('shipping_days', $country ? $country->shipping_days : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shipping Days (AR)</label>
                                <input type="text" name="shipping_daysAR" class="form-control" value="{{ old('shipping_daysAR', $country ? $country->shipping_daysAR : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" {{ old('isactive', $country ? $country->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $country ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

