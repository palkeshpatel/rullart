@extends('layouts.vertical', ['title' => 'Update Welcome Text'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Welcome Text'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Welcome Text</h4>
                </div>
                <div class="card-body">
                    <form id="homePageForm" method="POST" 
                        action="{{ route('admin.pages.home.update') }}" 
                        novalidate enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Page Title <span class="text-danger">*</span></label>
                                    <input type="text" name="pagetitle" class="form-control" 
                                        value="{{ old('pagetitle', $page->pagetitle ?? 'Welcome Text') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Page Title (AR)</label>
                                    <input type="text" name="pagetitleAR" class="form-control" 
                                        value="{{ old('pagetitleAR', $page->pagetitleAR ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Details</label>
                                    <textarea name="details" id="details" class="form-control" rows="10">{{ old('details', $page->details ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Details (AR)</label>
                                    <textarea name="detailsAR" id="detailsAR" class="form-control" rows="10">{{ old('detailsAR', $page->detailsAR ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="published" value="1" 
                                    id="published" {{ old('published', $page->published ?? 1) ? 'checked' : '' }}>
                                <label class="form-check-label" for="published">
                                    Published
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
        // Wait for jQuery to be available
        (function() {
            function initHomePageScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initHomePageScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    console.log('✅ Document ready for Home Page');

                    // Initialize CKEditor
                    if (typeof CKEDITOR !== 'undefined') {
                        CKEDITOR.replace('details', {
                            height: 300,
                            toolbar: [
                                { name: 'document', items: ['Source'] },
                                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
                                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                                { name: 'links', items: ['Link', 'Unlink'] },
                                { name: 'insert', items: ['Image', 'Table'] },
                                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                                { name: 'colors', items: ['TextColor', 'BGColor'] },
                                { name: 'tools', items: ['Maximize'] }
                            ]
                        });

                        CKEDITOR.replace('detailsAR', {
                            height: 300,
                            toolbar: [
                                { name: 'document', items: ['Source'] },
                                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
                                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Blockquote'] },
                                { name: 'links', items: ['Link', 'Unlink'] },
                                { name: 'insert', items: ['Image', 'Table'] },
                                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                                { name: 'colors', items: ['TextColor', 'BGColor'] },
                                { name: 'tools', items: ['Maximize'] }
                            ]
                        });
                    }

                    // Block native submit
                    $(document).off('submit', '#homePageForm');
                    $(document).on('submit', '#homePageForm', function(e) {
                        console.log('🚫 Native submit blocked');
                        e.preventDefault();
                        return false;
                    });

                    // Setup validation
                    const $form = $('#homePageForm');

                    $form.validate({
                        rules: {
                            pagetitle: {
                                required: true
                            }
                        },
                        messages: {
                            pagetitle: 'Page Title (EN) is required'
                        },
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
                            console.log('🚀 Validation passed → submitHomePageForm()');
                            submitHomePageForm(form);
                        }
                    });

                    /* -----------------------------------
                     SUBMIT FORM (AJAX)
                    ----------------------------------- */
                    function submitHomePageForm(form) {
                        console.log('📤 submitHomePageForm called');

                        // Update CKEditor content before submitting
                        for (var instance in CKEDITOR.instances) {
                            CKEDITOR.instances[instance].updateElement();
                        }

                        const formData = new FormData(form);
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
                                showToast(res.message || 'Page updated successfully', 'success');

                                submitBtn.disabled = false;
                                submitBtn.innerHTML = submitBtn.getAttribute('data-original-text') || originalText;
                            })
                            .catch(err => {
                                console.error('❌ AJAX error:', err);

                                let errorMessage = 'Failed to update page.';

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
            initHomePageScript();
        })();
    </script>
@endsection

