<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="customerwiseReportTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Customer Name</th>
                <th><i class="fa fa-sort"></i> Email</th>
                <th><i class="fa fa-sort"></i> Order Count</th>
                <th><i class="fa fa-sort"></i> Total Sales</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reports as $report)
            <tr>
                <td>
                    @php
                        $customer = $report->customer ?? null;
                        $name = $customer ? trim(($customer->firstname ?? '') . ' ' . ($customer->lastname ?? '')) : 'N/A';
                    @endphp
                    {{ $name ?: 'N/A' }}
                </td>
                <td>{{ $customer->email ?? 'N/A' }}</td>
                <td>{{ $report->order_count ?? 0 }}</td>
                <td>{{ number_format($report->total_sales ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No sales data found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

