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
            <tr data-order-id="{{ $order->orderid }}" class="order-row">
                <td>
                    <a href="javascript:void(0);" class="text-primary toggle-order-details" data-order-id="{{ $order->orderid }}">
                        <i class="fa fa-plus-circle text-success toggle-icon"></i> {{ $order->orderid }}
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
            <tr class="order-details-row" data-order-id="{{ $order->orderid }}" style="display: none;">
                <td colspan="11" class="bg-light">
                    <div class="p-3">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Order From:</strong> 
                                @if($order->mobiledevice)
                                    {{ ucfirst($order->mobiledevice) }} 
                                    @if($order->platform)
                                        {{ ucfirst($order->platform) }}
                                    @endif
                                @else
                                    Web
                                @endif
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Action:</strong>
                                <a href="#" class="btn btn-sm btn-info ms-2">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="#" class="btn btn-sm btn-warning ms-2">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
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

