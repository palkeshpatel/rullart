<div class="modal fade" id="discountViewModal" tabindex="-1" aria-labelledby="discountViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="discountViewModalLabel">Discount Details - {{ $discount->rate }}%</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Discount ID:</th>
                                <td>{{ $discount->id }}</td>
                            </tr>
                            <tr>
                                <th>Discount Rate:</th>
                                <td>{{ $discount->rate }}%</td>
                            </tr>
                            <tr>
                                <th>Start Date:</th>
                                <td>{{ $discount->startdate ? \Carbon\Carbon::parse($discount->startdate)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td>{{ $discount->enddate ? \Carbon\Carbon::parse($discount->enddate)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Days:</th>
                                <td>{{ $discount->days ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($discount->isactive)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-discount-btn" data-discount-id="{{ $discount->id }}">
                    <i class="ti ti-edit me-1"></i> Edit Discount
                </button>
            </div>
        </div>
    </div>
</div>

