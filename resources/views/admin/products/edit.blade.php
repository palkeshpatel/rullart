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
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
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
                    // Initialize CKEditor for long descriptions
                    if (typeof CKEDITOR !== 'undefined') {
                        CKEDITOR.replace('longdescr', {
                            height: 300
                        });
                        CKEDITOR.replace('longdescrAR', {
                            height: 300
                        });
                    }

                    // Setup validation
                    setupProductValidation();
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
                        // Get CKEditor content before submit
                        if (typeof CKEDITOR !== 'undefined') {
                            for (var instance in CKEDITOR.instances) {
                                CKEDITOR.instances[instance].updateElement();
                            }
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

            initProductScript();
        })();
    </script>
@endsection
