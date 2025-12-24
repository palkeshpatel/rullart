<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="returnRequestsTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Order No</th>
                <th class="text-muted">Name</th>
                <th class="text-muted">Email</th>
                <th class="text-muted">Mobile</th>
                <th class="text-muted">Submit Date</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returnRequests as $request)
                <tr>
                    <td>
                        <a href="#" class="link-reset fw-semibold">#{{ $request->orderno ?? 'N/A' }}</a>
                    </td>
                    <td>{{ $request->firstname ?? '' }} {{ $request->lastname ?? '' }}</td>
                    <td>{{ $request->email ?? 'N/A' }}</td>
                    <td>{{ $request->mobile ?? 'N/A' }}</td>
                    <td>
                        @if($request->submiton)
                            {{ \Carbon\Carbon::parse($request->submiton)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" title="View">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <div class="text-muted">No return requests found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

