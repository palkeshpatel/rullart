<div class="modal fade" id="colorViewModal" tabindex="-1" aria-labelledby="colorViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="colorViewModalLabel">Color Details - {{ $color->filtervalue }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Color ID:</th>
                                <td>{{ $color->filtervalueid }}</td>
                            </tr>
                            <tr>
                                <th>Color Name (EN):</th>
                                <td>{{ $color->filtervalue }}</td>
                            </tr>
                            <tr>
                                <th>Color Name (AR):</th>
                                <td>{{ $color->filtervalueAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Color Code:</th>
                                <td>{{ $color->filtervaluecode ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td>{{ $color->displayorder ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($color->isactive)
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
                <button type="button" class="btn btn-primary edit-color-btn" data-color-id="{{ $color->filtervalueid }}">
                    <i class="ti ti-edit me-1"></i> Edit Color
                </button>
            </div>
        </div>
    </div>
</div>

