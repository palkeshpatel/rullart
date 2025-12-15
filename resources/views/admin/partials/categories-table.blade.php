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
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
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

