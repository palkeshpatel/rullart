@extends('layouts.vertical', ['title' => 'Settings'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Settings'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Settings</h4>
                </div>
                <div class="card-body">
                    <form id="settingsForm" method="POST" 
                        action="{{ route('admin.settings.update') }}" 
                        novalidate enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            @forelse($settings as $setting)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        {{ $setting->name }}
                                        @if($setting->isrequired)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>
                                    
                                    @if($setting->inputtype == 'textarea')
                                        <textarea name="{{ $setting->name }}" 
                                            class="form-control" 
                                            rows="3"
                                            {{ $setting->isrequired ? 'required' : '' }}>{{ old($setting->name, $setting->details) }}</textarea>
                                    @elseif($setting->inputtype == 'number')
                                        <input type="number" 
                                            name="{{ $setting->name }}" 
                                            class="form-control" 
                                            value="{{ old($setting->name, $setting->details) }}"
                                            {{ $setting->isrequired ? 'required' : '' }}>
                                    @elseif($setting->inputtype == 'email')
                                        <input type="email" 
                                            name="{{ $setting->name }}" 
                                            class="form-control" 
                                            value="{{ old($setting->name, $setting->details) }}"
                                            {{ $setting->isrequired ? 'required' : '' }}>
                                    @elseif($setting->inputtype == 'url')
                                        <input type="url" 
                                            name="{{ $setting->name }}" 
                                            class="form-control" 
                                            value="{{ old($setting->name, $setting->details) }}"
                                            {{ $setting->isrequired ? 'required' : '' }}>
                                    @elseif($setting->inputtype == 'checkbox')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" 
                                                type="checkbox" 
                                                name="{{ $setting->name }}" 
                                                value="Yes" 
                                                id="{{ $setting->name }}"
                                                {{ old($setting->name, $setting->details) == '1' || old($setting->name, $setting->details) == 'Yes' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $setting->name }}">
                                                Enabled
                                            </label>
                                        </div>
                                    @else
                                        <input type="text" 
                                            name="{{ $setting->name }}" 
                                            class="form-control" 
                                            value="{{ old($setting->name, $setting->details) }}"
                                            {{ $setting->isrequired ? 'required' : '' }}>
                                    @endif
                                    
                                    <div class="invalid-feedback"></div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        No settings found. Settings will be created automatically when you save.
                                    </div>
                                </div>
                            @endforelse
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Wait for jQuery to be available
        (function() {
            function initSettingsScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initSettingsScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    console.log('✅ Document ready for Settings');

                    // Block native submit
                    $(document).off('submit', '#settingsForm');
                    $(document).on('submit', '#settingsForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    // Setup validation
                    const $form = $('#settingsForm');

                    $form.validate({
                        errorElement: 'div',
                        errorClass: 'invalid-feedback',
                        highlight(el) {
                            $(el).addClass('is-invalid');
                        },
                        unhighlight(el) {
                            $(el).removeClass('is-invalid').addClass('is-valid');
                        },
                        errorPlacement(error, element) {
                            error.insertAfter(element);
                        },
                        submitHandler(form) {
                            console.log('🚀 Validation passed → submitSettingsForm()');
                            submitSettingsForm(form);
                        }
                    });

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitSettingsForm(form) {
                        console.log('📤 submitSettingsForm called');

                        const formData = new FormData(form);
                        
                        // Handle unchecked checkboxes - add "No" value for checkboxes that aren't checked
                        $(form).find('input[type="checkbox"]').each(function() {
                            if (!$(this).is(':checked')) {
                                formData.append($(this).attr('name'), 'No');
                            }
                        });
                        
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';

                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.setAttribute('data-original-text', originalText);
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                console.log('✅ AJAX success:', res);
                                showToast(res.message || 'Settings updated successfully', 'success');

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                let errorMessage = 'Failed to update settings.';

                                if (err.message) {
                                    errorMessage = err.message;
                                } else if (err.errors) {
                                    const firstError = Object.values(err.errors)[0];
                                    if (Array.isArray(firstError)) {
                                        errorMessage = firstError[0];
                                    } else {
                                        errorMessage = firstError;
                                    }
                                }

                                showToast(errorMessage, 'error');

                                $form.find('.is-invalid').removeClass('is-invalid');
                                $form.find('.is-valid').removeClass('is-valid');
                                $form.find('[id$="-error"]').remove();
                                $form.find('.invalid-feedback').html('').removeClass('d-block').hide();

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            });
                    }

                    /* -----------------------------------
                     SHOW TOAST (TOP RIGHT)
                    ----------------------------------- */
                    function showToast(message, type = 'error') {
                        let toastContainer = $('#global-toast-container');

                        if (!toastContainer.length) {
                            toastContainer = $('<div id="global-toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
                            $('body').append(toastContainer);
                        }

                        toastContainer.find('.toast').each(function() {
                            const bsToast = bootstrap.Toast.getInstance(this);
                            if (bsToast) {
                                bsToast.hide();
                            }
                        });

                        const toastBg = type === 'error' ? 'bg-danger' : 'bg-success';
                        const toastId = 'toast-' + Date.now();
                        const toast = $(`
                            <div id="${toastId}" class="toast ${toastBg} text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                <div class="d-flex">
                                    <div class="toast-body">
                                        <i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>
                                        ${message}
                                    </div>
                                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                </div>
                            </div>
                        `);

                        toastContainer.append(toast);

                        const bsToast = new bootstrap.Toast(toast[0], {
                            autohide: true,
                            delay: 5000
                        });
                        bsToast.show();

                        toast.on('hidden.bs.toast', function() {
                            $(this).remove();
                            if (toastContainer.find('.toast').length === 0) {
                                toastContainer.remove();
                            }
                        });
                    }
                });
            }

            // Start initialization
            initSettingsScript();
        })();
    </script>
@endsection

