<table class="table table-bordered table-striped table-hover" id="discountsTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="id">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="rate">
                    Discount Rate (%) <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="startdate">
                    Start Date <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="enddate">
                    End Date <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="days">
                    Days <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($discounts as $discount)
            <tr>
                <td>{{ $discount->id }}</td>
                <td>{{ $discount->rate }}%</td>
                <td>{{ $discount->startdate ? \Carbon\Carbon::parse($discount->startdate)->format('d-M-Y') : 'N/A' }}</td>
                <td>{{ $discount->enddate ? \Carbon\Carbon::parse($discount->enddate)->format('d-M-Y') : 'N/A' }}</td>
                <td>{{ $discount->days ?? 'N/A' }}</td>
                <td>{{ $discount->isactive ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-discount-btn" 
                           title="View" data-discount-id="{{ $discount->id }}">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-discount-btn" 
                           title="Edit" data-discount-id="{{ $discount->id }}">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-discount-btn" 
                           title="Delete" data-discount-id="{{ $discount->id }}" 
                           data-discount-rate="{{ $discount->rate }}%">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No discounts found</td>
            </tr>
        @endforelse
    </tbody>
</table>

