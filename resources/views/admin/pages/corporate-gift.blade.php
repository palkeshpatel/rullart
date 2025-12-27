@extends('layouts.vertical', ['title' => 'Update Corporate Gifts'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Corporate Gifts'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Corporate Gifts</h4>
                </div>
                <div class="card-body">
                    <form id="pageForm" method="POST" 
                        action="{{ route('admin.pages.corporate-gift.update') }}" 
                        novalidate enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Page Title <span class="text-danger">*</span></label>
                                    <input type="text" name="pagetitle" class="form-control" 
                                        value="{{ old('pagetitle', $page->pagetitle ?? 'Corporate Gifts') }}" required>
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
                                    <div id="details-editor" style="height: 300px;">
                                        {!! old('details', $page->details ?? '') !!}
                                    </div>
                                    <textarea name="details" id="details" class="form-control d-none" style="display: none;">{{ old('details', $page->details ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Details (AR)</label>
                                    <div id="detailsAR-editor" style="height: 300px;">
                                        {!! old('detailsAR', $page->detailsAR ?? '') !!}
                                    </div>
                                    <textarea name="detailsAR" id="detailsAR" class="form-control d-none" style="display: none;">{{ old('detailsAR', $page->detailsAR ?? '') }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Meta Title</label>
                                    <input type="text" name="metatitle" class="form-control" 
                                        value="{{ old('metatitle', $page->metatitle ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Meta Keyword</label>
                                    <input type="text" name="metakeyword" class="form-control" 
                                        value="{{ old('metakeyword', $page->metakeyword ?? '') }}">
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Meta Description</label>
                                    <input type="text" name="metadescription" class="form-control" 
                                        value="{{ old('metadescription', $page->metadescription ?? '') }}">
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
    @vite(['node_modules/quill/dist/quill.core.css', 'node_modules/quill/dist/quill.snow.css', 'resources/js/pages/home-page-editor.js'])
    <script>
        (function() {
            function initPageScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initPageScript, 50);
                    return;
                }
                const $ = jQuery;
                $(document).ready(function() {
                    console.log('✅ Document ready for Corporate Gifts Page');
                    $(document).off('submit', '#pageForm');
                    $(document).on('submit', '#pageForm', function(e) {
                        e.preventDefault();
                        return false;
                    });
                    const $form = $('#pageForm');
                    $form.validate({
                        rules: { pagetitle: { required: true } },
                        messages: { pagetitle: 'Page Title (EN) is required' },
                        errorElement: 'div',
                        errorClass: 'invalid-feedback',
                        highlight(el) { $(el).addClass('is-invalid'); },
                        unhighlight(el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
                        errorPlacement(error, element) { error.insertAfter(element); },
                        submitHandler(form) { submitPageForm(form); }
                    });
                    function submitPageForm(form) {
                        if (window.detailsQuill) {
                            document.getElementById('details').value = window.detailsQuill.root.innerHTML;
                        }
                        if (window.detailsARQuill) {
                            document.getElementById('detailsAR').value = window.detailsARQuill.root.innerHTML;
                        }
                        const formData = new FormData(form);
                        const url = form.action;
                        const method = form.querySelector('[name="_method"]')?.value || 'POST';
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
                        AdminAjax.request(url, method, formData)
                            .then(res => {
                                showToast(res.message || 'Page updated successfully', 'success');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                            })
                            .catch(err => {
                                let errorMessage = 'Failed to update page.';
                                if (err.message) errorMessage = err.message;
                                else if (err.errors) {
                                    const firstError = Object.values(err.errors)[0];
                                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                                }
                                showToast(errorMessage, 'error');
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
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
                        const toast = $(`<div class="toast ${toastBg} text-white border-0" role="alert"><div class="d-flex"><div class="toast-body"><i class="ti ti-${type === 'error' ? 'alert-circle' : 'check-circle'} me-2"></i>${message}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
                        toastContainer.append(toast);
                        const bsToast = new bootstrap.Toast(toast[0], { autohide: true, delay: 5000 });
                        bsToast.show();
                        toast.on('hidden.bs.toast', function() {
                            $(this).remove();
                            if (toastContainer.find('.toast').length === 0) toastContainer.remove();
                        });
                    }
                });
            }
            initPageScript();
        })();
    </script>
@endsection

