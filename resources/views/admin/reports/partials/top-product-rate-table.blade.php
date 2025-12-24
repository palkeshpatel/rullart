<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="topProductRateTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Product Code</th>
                <th><i class="fa fa-sort"></i> Product Name</th>
                <th><i class="fa fa-sort"></i> Average Rating</th>
                <th><i class="fa fa-sort"></i> Total Ratings</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>{{ $report->productcode ?? 'N/A' }}</td>
                <td>{{ $report->title ?? 'N/A' }}</td>
                <td>{{ number_format($report->avg_rate ?? 0, 2) }}</td>
                <td>{{ $report->total_ratings ?? 0 }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No product rating data found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

