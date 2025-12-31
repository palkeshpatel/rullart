<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">
                    {{ $customer ? 'Edit Customer' : 'Add Customer' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customerForm" method="POST"
                action="{{ $customer ? route('admin.customers.update', $customer->customerid) : route('admin.customers.store') }}"
                novalidate>
                @csrf
                @if ($customer)
                    @method('PUT')
                @endif
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control"
                            value="{{ old('firstname', $customer ? $customer->firstname : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="lastname" class="form-control"
                            value="{{ old('lastname', $customer ? $customer->lastname : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control"
                            value="{{ old('email', $customer ? $customer->email : '') }}" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span> 
                            @if($customer)
                                <small class="text-muted">(Leave blank to keep current password)</small>
                            @endif
                        </label>
                        <input type="password" name="password" class="form-control"
                            {{ $customer ? '' : 'required' }}>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mobile</label>
                        <input type="text" name="mobile" class="form-control"
                            value="{{ old('mobile', $customer ? ($customer->mobile ?? '') : '') }}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="isactive" value="1"
                                id="isactive" {{ old('isactive', $customer ? $customer->isactive : 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isactive">
                                Active
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-1"></i> {{ $customer ? 'Update' : 'Create' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

