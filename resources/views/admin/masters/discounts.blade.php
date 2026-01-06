@extends('layouts.vertical', ['title' => 'Discount Offer'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Discount Offer'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form id="discountForm" method="POST" action="{{ route('admin.discounts.store') }}" novalidate>
                        @csrf
                        @if($discount)
                            @method('PUT')
                            <input type="hidden" name="discount_id" value="{{ $discount->id }}">
                        @endif
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Discount Percentage</label>
                                    <input type="number" step="0.01" name="rate" id="rate" class="form-control" 
                                        value="{{ old('rate', $discount ? $discount->rate : '') }}" required>
                                    <div class="invalid-feedback"></div>
                                    @error('rate')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" name="enddate" id="enddate" class="form-control" 
                                        value="{{ old('enddate', $discount && $discount->enddate ? \Carbon\Carbon::parse($discount->enddate)->format('Y-m-d') : '') }}">
                                    <div class="invalid-feedback"></div>
                                    @error('enddate')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="isactive" value="1" id="isactive" 
                                            {{ old('isactive', $discount ? $discount->isactive : 0) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="isactive">Is Active?</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> Submit
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            const $form = $('#discountForm');
            
            // jQuery Validation
            $form.validate({
                rules: {
                    rate: {
                        required: true,
                        number: true,
                        min: 0,
                        max: 100
                    }
                },
                messages: {
                    rate: {
                        required: 'Discount Percentage is required.',
                        number: 'Discount Percentage must be a valid number.',
                        min: 'Discount Percentage must be at least 0.',
                        max: 'Discount Percentage cannot exceed 100.'
                    }
                },
                errorElement: 'div',
                errorClass: 'invalid-feedback',
                highlight: function(element) {
                    $(element).addClass('is-invalid').removeClass('is-valid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid').addClass('is-valid');
                },
                errorPlacement: function(error, element) {
                    error.insertAfter(element);
                },
                submitHandler: function(form) {
                    submitDiscountForm(form);
                }
            });
            
            function submitDiscountForm(form) {
                const formData = new FormData(form);
                const url = form.action;
                const method = form.querySelector('[name="_method"]')?.value || 'POST';
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
                
                // Update URL if editing
                @if($discount)
                    const updateUrl = '{{ route("admin.discounts.update", $discount->id) }}';
                @else
                    const updateUrl = url;
                @endif
                
                $.ajax({
                    url: @if($discount) updateUrl @else url @endif,
                    method: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showToast(response.message || 'Discount saved successfully', 'success');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        
                        // Reload page after 1.5 seconds to show updated data
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to save discount.';
                        
                        if (xhr.status === 419) {
                            errorMessage = 'Session expired. Please refresh the page and try again.';
                        } else if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                const firstError = Object.values(xhr.responseJSON.errors)[0];
                                errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                            }
                        }
                        
                        showToast(errorMessage, 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            }
            
            function showToast(message, type = 'error') {
                let toastContainer = $('#global-toast-container');
                if (!toastContainer.length) {
                    toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                    $('body').append(toastContainer);
                }
                
                toastContainer.find('.toast').each(function() {
                    const bsToast = bootstrap.Toast.getInstance(this);
                    if (bsToast) bsToast.hide();
                });
                
                const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                const toastId = 'toast-' + Date.now();
                const toast = $(`
                    <div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                ${message}
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                        </div>
                    </div>
                `);
                
                toastContainer.append(toast);
                const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 5000 });
                bsToast.show();
                
                toast.on('hidden.bs.toast', function() {
                    $(this).remove();
                    if (toastContainer.find('.toast').length === 0) {
                        toastContainer.remove();
                    }
                });
            }
        });
    </script>
@endsection
