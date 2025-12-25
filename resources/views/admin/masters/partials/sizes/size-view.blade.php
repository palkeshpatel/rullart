<div class="modal fade" id="sizeViewModal" tabindex="-1" aria-labelledby="sizeViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sizeViewModalLabel">Size Details - {{ $size->filtervalue }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Size ID:</th>
                                <td>{{ $size->filtervalueid }}</td>
                            </tr>
                            <tr>
                                <th>Size Name (EN):</th>
                                <td>{{ $size->filtervalue }}</td>
                            </tr>
                            <tr>
                                <th>Size Name (AR):</th>
                                <td>{{ $size->filtervalueAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Size Code:</th>
                                <td>{{ $size->filtervaluecode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td>{{ $size->displayorder ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($size->isactive)
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
                <button type="button" class="btn btn-primary edit-size-btn" data-size-id="{{ $size->filtervalueid }}">
                    <i class="ti ti-edit me-1"></i> Edit Size
                </button>
            </div>
        </div>
    </div>
</div>

