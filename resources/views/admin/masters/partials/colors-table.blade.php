<table class="table table-bordered table-striped table-hover" id="colorsTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalueid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalue">
                    Color Name (EN) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalueAR">
                    Color Name (AR) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="displayorder">
                    Display Order <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($colors as $color)
            <tr>
                <td>{{ $color->filtervalueid }}</td>
                <td>{{ $color->filtervalue }}</td>
                <td>{{ $color->filtervalueAR }}</td>
                <td>{{ $color->displayorder }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-color-btn" data-color-id="{{ $color->filtervalueid }}" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-color-btn" data-color-id="{{ $color->filtervalueid }}" title="Edit">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-color-btn" data-color-id="{{ $color->filtervalueid }}" data-color-name="{{ $color->filtervalue }}" title="Delete">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No colors found</td>
            </tr>
        @endforelse
    </tbody>
</table>

