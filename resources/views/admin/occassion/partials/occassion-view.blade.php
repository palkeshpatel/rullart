<div class="modal fade" id="occassionViewModal" tabindex="-1" aria-labelledby="occassionViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="occassionViewModalLabel">Occasion Details - {{ $occassion->occassion }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">ID:</th>
                                <td>{{ $occassion->occassionid }}</td>
                            </tr>
                            <tr>
                                <th>Occasion Name (EN):</th>
                                <td>{{ $occassion->occassion }}</td>
                            </tr>
                            <tr>
                                <th>Occasion Name (AR):</th>
                                <td>{{ $occassion->occassionAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Occasion Code:</th>
                                <td>{{ $occassion->occassioncode }}</td>
                            </tr>
                            <tr>
                                <th>Meta Title (EN):</th>
                                <td>{{ $occassion->metatitle ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Title (AR):</th>
                                <td>{{ $occassion->metatitleAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Keyword (EN):</th>
                                <td>{{ $occassion->metakeyword ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Keyword (AR):</th>
                                <td>{{ $occassion->metakeywordAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Description (EN):</th>
                                <td>{{ $occassion->metadescr ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Meta Description (AR):</th>
                                <td>{{ $occassion->metadescrAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Photo:</th>
                                <td>{{ $occassion->photo ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mobile Photo:</th>
                                <td>{{ $occassion->photo_mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Published:</th>
                                <td>
                                    @if($occassion->ispublished)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Show Home:</th>
                                <td>
                                    @if($occassion->showhome)
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
                <button type="button" class="btn btn-primary edit-occassion-btn" data-occassion-id="{{ $occassion->occassionid }}">
                    <i class="ti ti-edit me-1"></i> Edit Occasion
                </button>
            </div>
        </div>
    </div>
</div>

