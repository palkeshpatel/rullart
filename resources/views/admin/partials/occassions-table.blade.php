<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="occassionsTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Occasion</th>
                <th><i class="fa fa-sort"></i> Occasion (AR)</th>
                <th><i class="fa fa-sort"></i> Occasion Code</th>
                <th>Published</th>
                <th>Display Order</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($occassions as $occassion)
            <tr>
                <td>{{ $occassion->occassion }}</td>
                <td>{{ $occassion->occassionAR ?? 'N/A' }}</td>
                <td>{{ $occassion->occassioncode }}</td>
                <td>{{ $occassion->ispublished ? 'Yes' : 'No' }}</td>
                <td>{{ $occassion->displayorder ?? 0 }}</td>
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
                <td colspan="6" class="text-center">No occasions found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

