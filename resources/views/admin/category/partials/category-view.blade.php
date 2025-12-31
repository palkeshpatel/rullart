<div class="modal fade" id="categoryViewModal" tabindex="-1" aria-labelledby="categoryViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryViewModalLabel">Category Details - {{ $category->category }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">ID:</th>
                                <td>{{ $category->categoryid }}</td>
                            </tr>
                            <tr>
                                <th>Category Name (EN):</th>
                                <td>{{ $category->category }}</td>
                            </tr>
                            <tr>
                                <th>Category Name (AR):</th>
                                <td>{{ $category->categoryAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Category Code:</th>
                                <td>{{ $category->categorycode }}</td>
                            </tr>
                            <tr>
                                <th>Parent Category:</th>
                                <td>
                                    @if($category->parentid && $category->parentid > 0)
                                        @php
                                            $parent = \App\Models\Category::find($category->parentid);
                                        @endphp
                                        {{ $parent ? $parent->category : 'N/A' }}
                                    @else
                                        No Parent (Main Category)
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td>{{ $category->displayorder ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Published:</th>
                                <td>
                                    @if($category->ispublished)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Show in Menu:</th>
                                <td>
                                    @if($category->showmenu)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-category-btn" data-category-id="{{ $category->categoryid }}">
                    <i class="ti ti-edit me-1"></i> Edit Category
                </button>
            </div>
        </div>
    </div>
</div>

