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
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr data-order-id="{{ $order->orderid }}" class="order-row">
                <td>
                    <a href="javascript:void(0);" class="text-primary toggle-order-details d-inline-flex align-items-center gap-2" data-order-id="{{ $order->orderid }}" style="text-decoration: none;">
                        <i class="fa fa-plus-circle text-success toggle-icon" style="font-size: 16px;"></i>
                        <span class="text-primary">{{ $order->orderid }}</span>
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
            </tr>
            <tr class="order-details-row" data-order-id="{{ $order->orderid }}" style="display: none;">
                <td colspan="9" class="bg-light">
                    <div class="p-3">
                        <div class="row align-items-center">
                            <div class="col-md-4">
                                <strong>Ref #:</strong> {{ $order->tranid ?? $order->paymentid ?? 'N/A' }}
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4 text-end">
                                <div class="d-flex justify-content-end gap-1 align-items-center">
                                    <strong>Action:</strong>
                                    <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-order-btn" data-order-id="{{ $order->orderid }}" title="View">
                                        <i class="ti ti-eye fs-lg"></i>
                                    </a>
                                    <a href="{{ route('admin.orders.edit', $order->orderid) }}" class="btn btn-light btn-icon btn-sm rounded-circle" title="Edit">
                                        <i class="ti ti-edit fs-lg"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No orders found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
