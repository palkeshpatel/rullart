<table class="table table-bordered table-striped table-hover" id="homeGalleryTable">
    <thead>
        <tr>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="homegalleryid">
                    ID <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="title">
                    Title <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Photo</th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="ispublished">
                    Is Published <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="displayorder">
                    Display Order <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>
                <a href="javascript:void(0);" class="text-dark" data-sort="updateddate">
                    Updated Date <i class="fa fa-sort"></i>
                </a>
            </th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($homeGalleries as $homeGallery)
            <tr>
                <td>{{ $homeGallery->homegalleryid }}</td>
                <td>{{ $homeGallery->title }}</td>
                <td>
                    @if($homeGallery->photo)
                        <img src="{{ asset('storage/upload/homegallery/' . $homeGallery->photo) }}" 
                             alt="{{ $homeGallery->title }}" 
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                    @else
                        <span class="text-muted">No Photo</span>
                    @endif
                </td>
                <td>{{ $homeGallery->ispublished ? 'Yes' : 'No' }}</td>
                <td>{{ $homeGallery->displayorder ?? 0 }}</td>
                <td>{{ $homeGallery->updateddate ? \Carbon\Carbon::parse($homeGallery->updateddate)->format('d/M/Y') : 'N/A' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-home-gallery-btn" 
                           title="View" data-home-gallery-id="{{ $homeGallery->homegalleryid }}">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle edit-home-gallery-btn" 
                           title="Edit" data-home-gallery-id="{{ $homeGallery->homegalleryid }}">
                            <i class="ti ti-edit fs-lg"></i>
                        </a>
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-home-gallery-btn" 
                           title="Delete" data-home-gallery-id="{{ $homeGallery->homegalleryid }}" 
                           data-home-gallery-title="{{ $homeGallery->title }}">
                            <i class="ti ti-trash fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">No photos found</td>
            </tr>
        @endforelse
    </tbody>
</table>

