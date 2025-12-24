<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="topProductMonthTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Product Code</th>
                <th><i class="fa fa-sort"></i> Product Name</th>
                <th><i class="fa fa-sort"></i> Total Quantity</th>
                <th><i class="fa fa-sort"></i> Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>{{ $report->productcode ?? 'N/A' }}</td>
                <td>{{ $report->title ?? 'N/A' }}</td>
                <td>{{ $report->total_qty ?? 0 }}</td>
                <td>{{ number_format($report->total_sales ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No product data found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

