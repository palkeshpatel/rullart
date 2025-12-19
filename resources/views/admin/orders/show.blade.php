@extends('layouts.vertical', ['title' => 'View Order'])

@section('content')
    @include('layouts.partials/page-title', ['title' => 'View Order'])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Order #{{ $order->orderid }}</h4>
                    <a href="{{ route('admin.orders.edit', $order->orderid) }}" class="btn btn-primary">
                        <i class="ti ti-edit me-1"></i> Edit Order
                    </a>
                </div>
                <div class="card-body">
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
                                        @php
                                            $currentStatus = DB::table('orderstatus')
                                                ->where('statusid', $order->fkorderstatus)
                                                ->first();
                                        @endphp
                                        <p class="mb-0">
                                            <span class="badge badge-soft-{{ $currentStatus->classname ?? 'primary' }}">
                                                {{ $currentStatus->status ?? 'N/A' }}
                                            </span>
                                        </p>
                                    </div>
                                    @if($order->trackingno)
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Tracking Number:</strong></label>
                                        <p class="mb-0">{{ $order->trackingno }}</p>
                                    </div>
                                    @endif
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
                                                        <span class="badge badge-soft-{{ $itemStatus->classname ?? 'primary' }}">
                                                            {{ $itemStatus->status ?? 'Process' }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="7" class="text-center">No items found</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                    <td colspan="2"><strong>KWD {{ number_format($order->total, 3) }}</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

