<div class="modal fade" id="couponCodeViewModal" tabindex="-1" aria-labelledby="couponCodeViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponCodeViewModalLabel">Coupon Code Details - {{ $couponCode->couponcode }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Coupon Code ID:</th>
                                <td>{{ $couponCode->couponcodeid }}</td>
                            </tr>
                            <tr>
                                <th>Coupon Code:</th>
                                <td>{{ $couponCode->couponcode }}</td>
                            </tr>
                            <tr>
                                <th>Coupon Value:</th>
                                <td>{{ $couponCode->couponvalue }}</td>
                            </tr>
                            <tr>
                                <th>Start Date:</th>
                                <td>{{ $couponCode->startdate ? \Carbon\Carbon::parse($couponCode->startdate)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>End Date:</th>
                                <td>{{ $couponCode->enddate ? \Carbon\Carbon::parse($couponCode->enddate)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Coupon Type ID:</th>
                                <td>{{ $couponCode->fkcoupontypeid ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Coupon Type:</th>
                                <td>{{ $couponCode->coupontype ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Category ID:</th>
                                <td>{{ $couponCode->fkcategoryid ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($couponCode->isactive)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Multi Use:</th>
                                <td>
                                    @if($couponCode->ismultiuse)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>General:</th>
                                <td>
                                    @if($couponCode->isgeneral)
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
                <button type="button" class="btn btn-primary edit-coupon-btn" data-coupon-id="{{ $couponCode->couponcodeid }}">
                    <i class="ti ti-edit me-1"></i> Edit Coupon Code
                </button>
            </div>
        </div>
    </div>
</div>

