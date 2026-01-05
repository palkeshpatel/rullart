<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="ratingsTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Product</th>
                <th class="text-muted">Customer</th>
                <th class="text-muted">Rating</th>
                <th class="text-muted">Review</th>
                <th class="text-muted">Date</th>
                <th class="text-muted">Status</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ratings as $rating)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            @if($rating->product && $rating->product->photo)
                                <img src="{{ asset('storage/' . $rating->product->photo) }}" alt="{{ $rating->product->title ?? 'N/A' }}" class="me-2" style="width: 40px; height: 40px; object-fit: cover;">
                            @endif
                            <div class="fw-semibold">{{ $rating->product ? $rating->product->title : 'N/A' }}</div>
                        </div>
                    </td>
                    <td>
                        {{ $rating->customer ? ($rating->customer->firstname . ' ' . $rating->customer->lastname) : 'N/A' }}
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="ti ti-star{{ $i <= $rating->rate ? '-filled' : '' }} text-warning"></i>
                            @endfor
                            <span class="ms-1">({{ $rating->rate }})</span>
                        </div>
                    </td>
                    <td>
                        <div class="text-truncate" style="max-width: 200px;" title="{{ $rating->review }}">
                            {{ $rating->review ?? 'No review' }}
                        </div>
                    </td>
                    <td>
                        @if($rating->submiton)
                            {{ \Carbon\Carbon::parse($rating->submiton)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if($rating->ispublished)
                            <span class="badge badge-soft-success">Published</span>
                        @else
                            <span class="badge badge-soft-warning">Unpublished</span>
                        @endif
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-rating-btn" data-rating-id="{{ $rating->ratingid }}" title="View">
                                <i class="ti ti-eye fs-lg"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-rating-btn" data-rating-id="{{ $rating->ratingid }}" title="Delete">
                                <i class="ti ti-trash fs-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="text-muted">No product reviews found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

