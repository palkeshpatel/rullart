<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="ordersTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Order#</th>
                <th><i class="fa fa-sort"></i> Name</th>
                <th><i class="fa fa-sort"></i> Email</th>
                <th><i class="fa fa-sort"></i> Total</th>
                <th>Order Status</th>
                <th><i class="fa fa-sort"></i> Area</th>
                <th><i class="fa fa-sort"></i> Order Date</th>
                <th><i class="fa fa-sort"></i> Shipping Method</th>
                <th><i class="fa fa-sort"></i> Payment Method</th>
                <th><i class="fa fa-sort"></i> Ref #</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>
                    <a href="#" class="text-primary">
                        <i class="fa fa-plus-circle text-success"></i> {{ $order->orderid }}
                    </a>
                </td>
                <td>{{ $order->firstname }} {{ $order->lastname }}</td>
                <td>{{ $order->customer->email ?? 'N/A' }}</td>
                <td>{{ number_format($order->total, 3) }}</td>
                <td>
                    <select class="form-select form-select-sm order-status" data-order-id="{{ $order->orderid }}">
                        <option value="2" {{ $order->fkorderstatus == 2 ? 'selected' : '' }}>Process</option>
                        <option value="1" {{ $order->fkorderstatus == 1 ? 'selected' : '' }}>Pending</option>
                        <option value="7" {{ $order->fkorderstatus == 7 ? 'selected' : '' }}>Delivered</option>
                        <option value="8" {{ $order->fkorderstatus == 8 ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </td>
                <td>{{ $order->country }}</td>
                <td>{{ $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d/M/Y H:i') : 'N/A' }}</td>
                <td>{{ $order->shipping_charge ? 'standard' : 'N/A' }}</td>
                <td>{{ $order->paymentmethod }}</td>
                <td>{{ $order->tranid ?? $order->paymentid ?? 'N/A' }}</td>
                <td>
                    <a href="#" class="btn btn-sm btn-info">View</a>
                    <a href="#" class="btn btn-sm btn-warning">Edit</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="text-center">No orders found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

