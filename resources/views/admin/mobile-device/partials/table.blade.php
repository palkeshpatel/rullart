<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="devicesTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Name</th>
                <th class="text-muted">Mobile</th>
                <th class="text-muted">Device ID</th>
                <th class="text-muted">Is Active?</th>
                <th class="text-muted">Last Login</th>
                <th class="text-muted">Register Date</th>
                <th class="text-muted">Select</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($devices as $device)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="ti ti-circle-filled text-danger me-2" style="font-size: 8px;"></i>
                            {{ $device->customer ? ($device->customer->firstname . ' ' . $device->customer->lastname) : 'N/A' }}
                        </div>
                    </td>
                    <td>{{ $device->os ?? 'N/A' }}</td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $device->device_id ?? 'N/A' }}">
                            {{ $device->device_id ?? 'N/A' }}
                        </div>
                    </td>
                    <td>
                        @if(isset($device->isactive) && $device->isactive)
                            <span class="badge badge-soft-success">Yes</span>
                        @else
                            <span class="badge badge-soft-danger">No</span>
                        @endif
                    </td>
                    <td>
                        @if(isset($device->lastlogin) && $device->lastlogin)
                            {{ \Carbon\Carbon::parse($device->lastlogin)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if(isset($device->registerdate) && $device->registerdate)
                            {{ \Carbon\Carbon::parse($device->registerdate)->format('d/M/Y') }}
                        @elseif($device->created_at)
                            {{ \Carbon\Carbon::parse($device->created_at)->format('d/M/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input" value="{{ $device->id }}">
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle send-notification-btn" data-device-id="{{ $device->id }}" title="Send Notification">
                                <i class="ti ti-send fs-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">
                        <div class="text-muted">No mobile devices found.</div>
                        <small class="text-muted">The table might be empty in your local database.</small>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

