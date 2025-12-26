<div class="modal fade" id="courierCompanyViewModal" tabindex="-1" aria-labelledby="courierCompanyViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="courierCompanyViewModalLabel">Courier Company Details - {{ $courierCompany->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Courier Company ID:</th>
                                <td>{{ $courierCompany->id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $courierCompany->name }}</td>
                            </tr>
                            <tr>
                                <th>Tracking URL:</th>
                                <td>
                                    @if($courierCompany->tracking_url)
                                        <a href="{{ $courierCompany->tracking_url }}" target="_blank" class="text-primary">
                                            {{ $courierCompany->tracking_url }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($courierCompany->isactive)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Created At:</th>
                                <td>{{ $courierCompany->created_at ? \Carbon\Carbon::parse($courierCompany->created_at)->format('d-M-Y H:i') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-courier-btn" data-courier-id="{{ $courierCompany->id }}">
                    <i class="ti ti-edit me-1"></i> Edit Courier Company
                </button>
            </div>
        </div>
    </div>
</div>

