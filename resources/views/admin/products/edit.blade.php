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
                        shortdescr: {
                            required: true
                        },
                        shortdescrAR: {
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
                        productcode: 'Product code is required.',
                        fkcategoryid: 'Category is required.',
                        title: 'Product title (EN) is required.',
                        titleAR: 'Product title (AR) is required.',
                        shortdescr: 'Short description (EN) is required.',
                        shortdescrAR: 'Short description (AR) is required.',
                        price: 'Price is required and must be a valid number.',
                        sellingprice: 'Selling price is required and must be a valid number.'
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

                        form.submit();
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
