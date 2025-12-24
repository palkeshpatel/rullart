<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="categoriesTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Category</th>
                <th><i class="fa fa-sort"></i> Category (AR)</th>
                <th><i class="fa fa-sort"></i> Category Code</th>
                <th>Published</th>
                <th>Show Menu</th>
                <th>Display Order</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
            <tr>
                <td>{{ $category->category }}</td>
                <td>{{ $category->categoryAR ?? 'N/A' }}</td>
                <td>{{ $category->categorycode }}</td>
                <td>{{ $category->ispublished ? 'Yes' : 'No' }}</td>
                <td>{{ $category->showmenu ? 'Yes' : 'No' }}</td>
                <td>{{ $category->displayorder }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-category-btn" data-category-id="{{ $category->categoryid }}" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="{{ route('admin.category.edit', $category->categoryid) }}" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">No categories found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

