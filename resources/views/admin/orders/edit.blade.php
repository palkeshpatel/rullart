@extends('layouts.vertical', ['title' => 'Edit Order'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'Edit Order'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Order #{{ $order->orderid }}</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.orders.update', $order->orderid) }}" id="orderEditForm">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Left Column - Addresses -->
                            <div class="col-md-6">
                                <!-- Delivery Address -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Delivery Address</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>{{ $order->firstname }} {{ $order->lastname }}</strong></p>
                                        @if($order->block_number)
                                            <p class="mb-1">Block: {{ $order->block_number }}</p>
                                        @endif
                                        @if($order->street_number)
                                            <p class="mb-1">Street: {{ $order->street_number }}</p>
                                        @endif
                                        @if($order->avenue_number)
                                            <p class="mb-1">Avenue: {{ $order->avenue_number ?: 'Nothing' }}</p>
                                        @endif
                                        @if($order->house_number)
                                            <p class="mb-1">House/building: {{ $order->house_number }}</p>
                                        @endif
                                        @if($order->areaname)
                                            <p class="mb-1">Area: {{ $order->areaname }}</p>
                                        @endif
                                        @if($order->country)
                                            <p class="mb-1">Country: {{ $order->country }}</p>
                                        @endif
                                        @if($order->mobile)
                                            <p class="mb-0">Phone: {{ $order->mobile }}</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Billing Address -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Billing Address</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-1"><strong>{{ $order->firstnameBill ?: $order->firstname }} {{ $order->lastnameBill ?: $order->lastname }}</strong></p>
                                        @if($order->block_numberBill ?: $order->block_number)
                                            <p class="mb-1">Block: {{ $order->block_numberBill ?: $order->block_number }}</p>
                                        @endif
                                        @if($order->street_numberBill ?: $order->street_number)
                                            <p class="mb-1">Street: {{ $order->street_numberBill ?: $order->street_number }}</p>
                                        @endif
                                        @if($order->avenue_numberBill ?: $order->avenue_number)
                                            <p class="mb-1">Avenue: {{ ($order->avenue_numberBill ?: $order->avenue_number) ?: 'Nothing' }}</p>
                                        @endif
                                        @if($order->house_numberBill ?: $order->house_number)
                                            <p class="mb-1">House/building: {{ $order->house_numberBill ?: $order->house_number }}</p>
                                        @endif
                                        @if($order->areanameBill ?: $order->areaname)
                                            <p class="mb-1">Area: {{ $order->areanameBill ?: $order->areaname }}</p>
                                        @endif
                                        @if($order->countryBill ?: $order->country)
                                            <p class="mb-1">Country: {{ $order->countryBill ?: $order->country }}</p>
                                        @endif
                                        @if($order->mobileBill ?: $order->mobile)
                                            <p class="mb-0">Phone: {{ $order->mobileBill ?: $order->mobile }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column - Order Information -->
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Order Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Order ID:</strong></label>
                                            <p class="mb-0">{{ $order->orderid }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Order Date:</strong></label>
                                            <p class="mb-0">{{ $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d F Y') : 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Payment Method:</strong></label>
                                            <p class="mb-0">{{ $order->paymentmethod ?: 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Customer Email:</strong></label>
                                            <p class="mb-0">{{ $order->customer->email ?? 'N/A' }}</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Reference Number:</strong></label>
                                            <p class="mb-0">{{ $order->tranid ?? $order->paymentid ?? 'N/A' }}</p>
                                        </div>
                                        @if($order->paymentid)
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Payment ID:</strong></label>
                                            <p class="mb-0">{{ $order->paymentid }}</p>
                                        </div>
                                        @endif
                                        @if($order->tranid)
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Transaction ID:</strong></label>
                                            <p class="mb-0">{{ $order->tranid }}</p>
                                        </div>
                                        @endif
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Status:</strong></label>
                                            <select name="fkorderstatus" class="form-select form-select-sm">
                                                @foreach($orderStatuses as $status)
                                                    <option value="{{ $status->statusid }}" {{ $order->fkorderstatus == $status->statusid ? 'selected' : '' }}>
                                                        {{ $status->status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Table -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Order Items</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Title</th>
                                                        <th>Product Code</th>
                                                        <th>Size</th>
                                                        <th>Item Price</th>
                                                        <th>Qty</th>
                                                        <th>Subtotal</th>
                                                        <th>Status</th>
                                                        <th width="50">
                                                            <input type="checkbox" id="selectAllItems">
                                                        </th>
                                                        <th width="80">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($order->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product->title ?? $item->title ?? 'N/A' }}</td>
                                                        <td>{{ $item->product->productcode ?? 'N/A' }}</td>
                                                        <td>{{ $item->size ?? 'N/A' }}</td>
                                                        <td>KWD {{ number_format($item->price ?? $item->actualprice ?? 0, 3) }}</td>
                                                        <td>{{ $item->qty ?? 0 }}</td>
                                                        <td>KWD {{ number_format($item->subtotal ?? 0, 3) }}</td>
                                                        <td>
                                                            @php
                                                                $itemStatus = DB::table('orderstatus')
                                                                    ->where('statusid', $item->fkstatusid ?? $order->fkorderstatus)
                                                                    ->first();
                                                            @endphp
                                                            {{ $itemStatus->status ?? 'Process' }}
                                                        </td>
                                                        <td>
                                                            <input type="checkbox" name="selected_items[]" value="{{ $item->orderitemid }}" class="item-checkbox">
                                                        </td>
                                                        <td>
                                                            <a href="#" class="btn btn-sm btn-info btn-icon rounded-circle" title="Edit">
                                                                <i class="ti ti-edit fs-lg"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center">No items found</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                        <td colspan="4"><strong>KWD {{ number_format($order->total, 3) }}</strong></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div class="mt-3 text-end">
                                            <button type="button" class="btn btn-danger" id="removeSelectedItems">
                                                Remove Selected Items
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add Items Section -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Add Items:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <label class="form-label">Product Code</label>
                                                <input type="text" class="form-control" id="productCode" placeholder="Enter product code">
                                            </div>
                                            <div class="col-md-2 d-flex align-items-end">
                                                <button type="button" class="btn btn-primary" id="searchProduct">
                                                    Search
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Status & Tracking -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Order Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Order Status:</label>
                                            <select name="fkorderstatus" class="form-select">
                                                @foreach($orderStatuses as $status)
                                                    <option value="{{ $status->statusid }}" {{ $order->fkorderstatus == $status->statusid ? 'selected' : '' }}>
                                                        {{ $status->status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" class="btn btn-primary">Update</button>
                                            <button type="button" class="btn btn-secondary">
                                                <i class="ti ti-printer me-1"></i> Print
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">DHL Tracking Number</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Tracking Number</label>
                                            <input type="text" name="trackingno" class="form-control" value="{{ $order->trackingno ?? '' }}" placeholder="Enter tracking number">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all items checkbox
    document.getElementById('selectAllItems')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Remove selected items
    document.getElementById('removeSelectedItems')?.addEventListener('click', function() {
        const selectedItems = Array.from(document.querySelectorAll('.item-checkbox:checked'))
            .map(cb => cb.value);
        
        if (selectedItems.length === 0) {
            alert('Please select items to remove');
            return;
        }

        if (confirm('Are you sure you want to remove selected items?')) {
            // TODO: Implement AJAX call to remove items
            console.log('Remove items:', selectedItems);
        }
    });

    // Search product
    document.getElementById('searchProduct')?.addEventListener('click', function() {
        const productCode = document.getElementById('productCode').value;
        if (!productCode) {
            alert('Please enter a product code');
            return;
        }
        // TODO: Implement product search and add to order
        console.log('Search product:', productCode);
    });
});
</script>
@endsection

