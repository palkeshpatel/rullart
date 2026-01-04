<table class="table table-bordered table-striped table-hover" id="areasTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="areaid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="areaname">
                    Area Name (EN) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="areanameAR">
                    Area Name (AR) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($areas as $area)
            <tr>
                <td>{{ $area->areaid }}</td>
                <td>{{ $area->areaname }}</td>
                <td>{{ $area->areanameAR }}</td>
                <td>{{ $area->isactive ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-area-btn" data-area-id="{{ $area->areaid }}" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        @unless(\App\Helpers\ViewHelper::isView('areamaster'))
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-area-btn" data-area-id="{{ $area->areaid }}" title="Edit">
                                <i class="ti ti-edit fs-lg"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-area-btn" data-area-id="{{ $area->areaid }}" data-area-name="{{ $area->areaname }}" title="Delete">
                                <i class="ti ti-trash fs-lg"></i>
                            </a>
                        @endunless
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">No areas found</td>
            </tr>
        @endforelse
    </tbody>
</table>

