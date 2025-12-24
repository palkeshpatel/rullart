<table class="table table-bordered table-striped table-hover" id="courierCompanyTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="id">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="name">
                    Name <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="tracking_url">
                    Tracking URL <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Active</th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="created_at">
                    Created At <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($courierCompanies as $courierCompany)
            <tr>
                <td>{{ $courierCompany->id }}</td>
                <td>{{ $courierCompany->name }}</td>
                <td>
                    @if($courierCompany->tracking_url)
                        <a href="{{ $courierCompany->tracking_url }}" target="_blank" class="text-primary">
                            {{ Str::limit($courierCompany->tracking_url, 50) }}
                        </a>
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $courierCompany->isactive ? 'Yes' : 'No' }}</td>
                <td>{{ $courierCompany->created_at ? \Carbon\Carbon::parse($courierCompany->created_at)->format('d-M-Y H:i') : 'N/A' }}</td>
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
                <td colspan="6" class="text-center">No courier companies found</td>
            </tr>
        @endforelse
    </tbody>
</table>

