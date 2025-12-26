<table class="table table-bordered table-striped table-hover" id="sizesTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalueid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalue">
                    Size Name(EN) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="filtervalueAR">
                    Size Name(AR) <i class="fa fa-sort"></i>
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
        @forelse($sizes as $size)
            <tr>
                <td>{{ $size->filtervalueid }}</td>
                <td>{{ $size->filtervalue }}</td>
                <td>{{ $size->filtervalueAR }}</td>
                <td>{{ $size->displayorder }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-size-btn" 
                           title="View" data-size-id="{{ $size->filtervalueid }}">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-size-btn" 
                           title="Edit" data-size-id="{{ $size->filtervalueid }}">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-size-btn" 
                           title="Delete" data-size-id="{{ $size->filtervalueid }}" 
                           data-size-name="{{ $size->filtervalue }}">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No sizes found</td>
            </tr>
        @endforelse
    </tbody>
</table>

