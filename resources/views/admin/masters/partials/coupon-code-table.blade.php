<table class="table table-bordered table-striped table-hover" id="couponCodeTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="couponcodeid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="couponcode">
                    Coupon Code <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="couponvalue">
                    Coupon Value <i class="fa fa-sort"></i>
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
            <th>Active</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($couponCodes as $couponCode)
            <tr>
                <td>{{ $couponCode->couponcodeid }}</td>
                <td>{{ $couponCode->couponcode }}</td>
                <td>{{ $couponCode->couponvalue }}</td>
                <td>{{ $couponCode->startdate ? \Carbon\Carbon::parse($couponCode->startdate)->format('d-M-Y') : 'N/A' }}</td>
                <td>{{ $couponCode->enddate ? \Carbon\Carbon::parse($couponCode->enddate)->format('d-M-Y') : 'N/A' }}</td>
                <td>{{ $couponCode->isactive ? 'Yes' : 'No' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No coupon codes found</td>
            </tr>
        @endforelse
    </tbody>
</table>

