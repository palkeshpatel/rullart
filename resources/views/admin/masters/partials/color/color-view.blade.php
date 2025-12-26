<div class="modal fade" id="colorViewModal" tabindex="-1" aria-labelledby="colorViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorViewModalLabel">Filter Details - {{ $color->filtervalue }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">ID:</th>
                                <td>{{ $color->filtervalueid }}</td>
                            </tr>
                            <tr>
                                <th>Color(EN):</th>
                                <td>{{ $color->filtervalue }}</td>
                            </tr>
                            <tr>
                                <th>Color (AR):</th>
                                <td>{{ $color->filtervalueAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($color->isactive)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-color-btn" data-color-id="{{ $color->filtervalueid }}">
                    <i class="ti ti-edit me-1"></i> Edit Filter
                </button>
            </div>
        </div>
    </div>
</div>

