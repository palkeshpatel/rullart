<div class="modal fade" id="areaViewModal" tabindex="-1" aria-labelledby="areaViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="areaViewModalLabel">Area Details - {{ $area->areaname }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Area ID:</th>
                                <td>{{ $area->areaid }}</td>
                            </tr>
                            <tr>
                                <th>Country:</th>
                                <td>{{ $area->country ? $area->country->countryname : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Area Name(EN):</th>
                                <td>{{ $area->areaname }}</td>
                            </tr>
                            <tr>
                                <th>Area Name(AR):</th>
                                <td>{{ $area->areanameAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($area->isactive)
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
                <button type="button" class="btn btn-primary edit-area-btn" data-area-id="{{ $area->areaid }}">
                    <i class="ti ti-edit me-1"></i> Edit Area
                </button>
            </div>
        </div>
    </div>
</div>

