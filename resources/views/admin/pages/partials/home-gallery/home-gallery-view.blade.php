<div class="modal fade" id="homeGalleryViewModal" tabindex="-1" aria-labelledby="homeGalleryViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="homeGalleryViewModalLabel">Photo Details - {{ $homeGallery->title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">Photo ID:</th>
                                <td>{{ $homeGallery->homegalleryid }}</td>
                            </tr>
                            <tr>
                                <th>Title(EN):</th>
                                <td>{{ $homeGallery->title }}</td>
                            </tr>
                            <tr>
                                <th>Title(AR):</th>
                                <td>{{ $homeGallery->titleAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Description(EN):</th>
                                <td>{{ $homeGallery->descr ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Description(AR):</th>
                                <td>{{ $homeGallery->descrAR ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Link:</th>
                                <td>
                                    @if($homeGallery->link)
                                        <a href="{{ $homeGallery->link }}" target="_blank">{{ $homeGallery->link }}</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Video URL:</th>
                                <td>
                                    @if($homeGallery->videourl)
                                        <a href="{{ $homeGallery->videourl }}" target="_blank">{{ $homeGallery->videourl }}</a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Photo (Desktop):</th>
                                <td>
                                    @if($homeGallery->photo)
                                        <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo) }}" 
                                             alt="{{ $homeGallery->title }}" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Photo (Mobile):</th>
                                <td>
                                    @if($homeGallery->photo_mobile)
                                        <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_mobile) }}" 
                                             alt="{{ $homeGallery->title }}" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Photo (AR - Desktop):</th>
                                <td>
                                    @if($homeGallery->photo_ar)
                                        <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_ar) }}" 
                                             alt="{{ $homeGallery->title }}" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Photo (AR - Mobile):</th>
                                <td>
                                    @if($homeGallery->photo_mobile_ar)
                                        <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo_mobile_ar) }}" 
                                             alt="{{ $homeGallery->title }}" 
                                             style="max-width: 200px; max-height: 200px; object-fit: cover;">
                                    @else
                                        N/A
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Display Order:</th>
                                <td>{{ $homeGallery->displayorder ?? 0 }}</td>
                            </tr>
                            <tr>
                                <th>Published:</th>
                                <td>
                                    @if($homeGallery->ispublished)
                                        <span class="badge badge-soft-success">Yes</span>
                                    @else
                                        <span class="badge badge-soft-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Updated Date:</th>
                                <td>{{ $homeGallery->updateddate ? \Carbon\Carbon::parse($homeGallery->updateddate)->format('d/M/Y') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary edit-home-gallery-btn" data-home-gallery-id="{{ $homeGallery->homegalleryid }}">
                    <i class="ti ti-edit me-1"></i> Edit Photo
                </button>
            </div>
        </div>
    </div>
</div>

