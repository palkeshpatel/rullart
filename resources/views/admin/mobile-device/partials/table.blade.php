<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="devicesTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Device ID</th>
                <th class="text-muted">Device Name</th>
                <th class="text-muted">OS</th>
                <th class="text-muted">Version</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
                <tr>
                    <td>{{ $device->device_id ?? 'N/A' }}</td>
                    <td>{{ $device->device_name ?? 'N/A' }}</td>
                    <td>{{ $device->os ?? 'N/A' }}</td>
                    <td>{{ $device->version ?? 'N/A' }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete">
                            <i class="ti ti-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <div class="text-muted">No mobile devices found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

