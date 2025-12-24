<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="monthwiseReportTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Month</th>
                <th><i class="fa fa-sort"></i> Order Count</th>
                <th><i class="fa fa-sort"></i> Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>
                    @if($report->order_month)
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $report->order_month)->format('M Y') }}
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $report->order_count ?? 0 }}</td>
                <td>{{ number_format($report->total_sales ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center">No sales data found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

