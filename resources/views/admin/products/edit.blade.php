@extends('layouts.vertical', ['title' => 'Edit Product'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Edit Product'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.update', $product->productid) }}"
                        enctype="multipart/form-data" id="productForm" novalidate>
                        @csrf
                        @method('PUT')

                        @include('admin.products.partials.product-form-fields', [
                            'product' => $product,
                            'categories' => $categories,
                            'colors' => $colors,
                            'sizes' => $sizes,
                            'occasions' => $occasions,
                            'productSizes' => $productSizes,
                            'productFilters' => $productFilters,
                        ])

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> Update
                                </button>
                                <a href="{{ route('admin.products') }}" class="btn btn-danger">
                                    <i class="ti ti-x me-1"></i> Cancel
                                </a>
                            </div>
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
        // Wait for jQuery to be available
        (function() {
            function initProductScript() {
                if (typeof jQuery === 'undefined' || typeof jQuery.fn.validate === 'undefined') {
                    setTimeout(initProductScript, 50);
                    return;
                }

                const $ = jQuery;

                $(document).ready(function() {
                    // Setup validation
                    setupProductValidation();

                    // Setup dependent subcategory dropdown
                    setupSubcategoryDropdown();

                    // Load subcategories if category is already selected (on edit)
                    const $categorySelect = $('#categorySelect');
                    if ($categorySelect.length && $categorySelect.val()) {
                        $categorySelect.trigger('change');
                    }
                });
            }

            function setupProductValidation() {
                const $form = $('#productForm');
                if (!$form.length || $form.data('validator')) {
                    return;
                }

                $form.validate({
                    rules: {
                        productcode: {
                            required: true
                        },
                        fkcategoryid: {
                            required: true
                        },
                        title: {
                            required: true
                        },
                        titleAR: {
                            required: true
                        },
                        price: {
                            required: true,
                            number: true,
                            min: 0
                        },
                        sellingprice: {
                            required: true,
                            number: true,
                            min: 0
                        }
                    },
                    messages: {
                        productcode: 'Product Code is required.',
                        fkcategoryid: 'Category is required.',
                        title: 'Title [EN] is required.',
                        titleAR: 'Title [AR] is required.',
                        price: 'Product Price [KWD] is required and must be a valid number.',
                        sellingprice: 'Selling Price [KWD] is required and must be a valid number.'
                    },
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    highlight: function(el) {
                        $(el).addClass('is-invalid');
                    },
                    unhighlight: function(el) {
                        $(el).removeClass('is-invalid').addClass('is-valid');
                    },
                    errorPlacement: function(error, element) {
                        error.insertAfter(element);
                    },
                    submitHandler: function(form) {
                        // Update Quill editor content before submitting
                        if (window.longdescrQuill) {
                            const longdescrContent = window.longdescrQuill.root.innerHTML;
                            document.getElementById('longdescr').value = longdescrContent;
                        }

                        if (window.longdescrARQuill) {
                            const longdescrARContent = window.longdescrARQuill.root.innerHTML;
                            document.getElementById('longdescrAR').value = longdescrARContent;
                        }

                        // Submit via AJAX to handle redirect properly
                        const formData = new FormData(form);
                        const submitBtn = form.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

                        // Ensure CSRF token is included
                        const csrfToken = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val();
                        if (csrfToken) {
                            formData.append('_token', csrfToken);
                            formData.append('_method', 'PUT');
                        }

                        $.ajax({
                            url: form.action,
                            method: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken || ''
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Show success message
                                    if (typeof toastr !== 'undefined') {
                                        toastr.success(response.message || 'Product updated successfully');
                                    } else if (typeof showToast === 'function') {
                                        showToast(response.message || 'Product updated successfully', 'success');
                                    }
                                    // Redirect after short delay
                                    setTimeout(function() {
                                        window.location.href = response.redirect || '{{ route("admin.products") }}';
                                    }, 1000);
                                } else {
                                    if (typeof toastr !== 'undefined') {
                                        toastr.error(response.message || 'Failed to update product');
                                    } else if (typeof showToast === 'function') {
                                        showToast(response.message || 'Failed to update product', 'error');
                                    }
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = originalText;
                                }
                            },
                            error: function(xhr) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = originalText;
                                
                                if (xhr.status === 422) {
                                    // Validation errors
                                    const errors = xhr.responseJSON?.errors || {};
                                    $.each(errors, function(field, messages) {
                                        const input = form.querySelector('[name="' + field + '"]');
                                        if (input) {
                                            $(input).addClass('is-invalid');
                                            const errorDiv = input.nextElementSibling;
                                            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                                $(errorDiv).text(Array.isArray(messages) ? messages[0] : messages);
                                            }
                                        }
                                    });
                                    if (typeof toastr !== 'undefined') {
                                        toastr.error('Please fix the validation errors');
                                    }
                                } else if (xhr.status === 419) {
                                    // CSRF token mismatch
                                    const message = 'Session expired. Please refresh the page and try again.';
                                    if (typeof toastr !== 'undefined') {
                                        toastr.error(message);
                                    } else {
                                        alert('Error: ' + message);
                                    }
                                    // Optionally reload the page after a delay
                                    setTimeout(function() {
                                        window.location.reload();
                                    }, 2000);
                                } else {
                                    const message = xhr.responseJSON?.message || 'An error occurred while updating the product';
                                    if (typeof toastr !== 'undefined') {
                                        toastr.error(message);
                                    } else {
                                        alert('Error: ' + message);
                                    }
                                }
                            }
                        });

                        return false; // Prevent default form submission
                    }
                });
            }

            // Image preview function
            function previewProductImage(input, previewId) {
                const containerId = previewId + 'Container';
                const container = document.getElementById(containerId);
                const preview = document.getElementById(previewId);

                if (input.files && input.files[0]) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                        preview.style.width = '150px';
                        preview.style.height = '150px';
                        preview.style.objectFit = 'cover';
                        preview.style.borderRadius = '4px';
                        if (container) {
                            container.style.display = 'block';
                        }
                    }

                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Make preview function globally available
            window.previewProductImage = previewProductImage;

            // Setup dependent subcategory dropdown
            function setupSubcategoryDropdown() {
                const $categorySelect = $('#categorySelect');
                const $subCategorySelect = $('#subCategorySelect');

                if (!$categorySelect.length || !$subCategorySelect.length) {
                    return;
                }

                // Handle category change
                $categorySelect.on('change', function() {
                    const categoryId = $(this).val();
                    const selectedSubCategoryId = $subCategorySelect.val();

                    // Clear subcategory dropdown
                    $subCategorySelect.empty();
                    $subCategorySelect.append('<option value="">-- Select Sub Category --</option>');

                    if (!categoryId) {
                        return;
                    }

                    // Show loading state
                    $subCategorySelect.prop('disabled', true);

                    // Fetch subcategories
                    $.ajax({
                        url: '{{ route('admin.products.subcategories') }}',
                        method: 'GET',
                        data: {
                            category_id: categoryId
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.length > 0) {
                                $.each(response.data, function(index, subcategory) {
                                    const isSelected = (subcategory.categoryid ==
                                        selectedSubCategoryId);
                                    $subCategorySelect.append(
                                        '<option value="' + subcategory.categoryid +
                                        '"' + (isSelected ? ' selected' : '') + '>' +
                                        subcategory.category + '</option>'
                                    );
                                });
                            }
                            $subCategorySelect.prop('disabled', false);
                        },
                        error: function(xhr) {
                            console.error('Error fetching subcategories:', xhr);
                            $subCategorySelect.prop('disabled', false);
                            if (typeof toastr !== 'undefined') {
                                toastr.error('Error loading subcategories');
                            }
                        }
                    });
                });
            }

            initProductScript();
        })();
    </script>
@endsection
