@extends('layouts.vertical', ['title' => 'Add Product'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Add Product'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="productForm">
                        @csrf
                        
                        @include('admin.products.partials.product-form-fields', [
                            'product' => null,
                            'categories' => $categories,
                            'colors' => $colors,
                            'sizes' => $sizes,
                            'occasions' => $occasions,
                            'productSizes' => collect([]),
                            'productFilters' => null
                        ])
                        
                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-device-floppy me-1"></i> Save
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
            
            // Add size row
            $('#addSizeRow').on('click', function() {
                const sizeRow = `
                    <tr>
                        <td>
                            <select name="sizes[][filtervalueid]" class="form-select form-select-sm">
                                <option value="0">No Size</option>
                                @foreach($sizes as $size)
                                <option value="{{ $size->filtervalueid }}">{{ $size->filtervalue }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number" name="sizes[][qty]" class="form-control form-control-sm" min="0" value="0">
                        </td>
                        <td>
                            <input type="text" name="sizes[][barcode]" class="form-control form-control-sm">
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger remove-size-row">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#sizesTable tbody').append(sizeRow);
            });
            
            // Remove size row
            $(document).on('click', '.remove-size-row', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
@endsection

