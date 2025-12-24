<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="cartsTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Ref #</th>
                <th><i class="fa fa-sort"></i> Name</th>
                <th><i class="fa fa-sort"></i> Email</th>
                <th><i class="fa fa-sort"></i> Total</th>
                <th><i class="fa fa-sort"></i> Order Date</th>
                <th><i class="fa fa-sort"></i> Payment Method</th>
                <th><i class="fa fa-sort"></i> Order From</th>
                <th>Email Count</th>
                <th>Email Send Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carts as $cart)
                <tr>
                    <td>
                        <a href="javascript:void(0);" class="text-primary">{{ $cart->cartid }}</a>
                    </td>
                    <td>
                        @php
                            $name = trim(($cart->customer->firstname ?? '') . ' ' . ($cart->customer->lastname ?? ''));
                        @endphp
                        {{ $name ?: 'N/A' }}
                    </td>
                    <td>{{ $cart->customer->email ?? 'N/A' }}</td>
                    <td>{{ number_format($cart->total ?? 0, 3) }}</td>
                    <td>
                        @if($cart->orderdate)
                            {{ \Carbon\Carbon::parse($cart->orderdate)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if(isset($cart->paymentmethod) && $cart->paymentmethod)
                            {{ ucfirst($cart->paymentmethod) }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @php
                            $orderFrom = '';
                            if (isset($cart->mobiledevice) && $cart->mobiledevice) {
                                $orderFrom = ucfirst($cart->mobiledevice);
                                if (isset($cart->platform) && $cart->platform) {
                                    $orderFrom .= ' ' . $cart->platform;
                                }
                            } elseif (isset($cart->platform) && $cart->platform) {
                                $orderFrom = 'Web ' . $cart->platform;
                            } elseif (isset($cart->browser) && $cart->browser) {
                                $orderFrom = 'Web ' . $cart->browser;
                            } else {
                                $orderFrom = 'Web';
                            }
                        @endphp
                        {{ $orderFrom }}
                    </td>
                    <td>0</td>
                    <td>-</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-cart-btn" data-cart-id="{{ $cart->cartid }}" title="View">
                                <i class="ti ti-eye fs-lg"></i>
                            </a>
                            <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle delete-cart-btn" data-cart-id="{{ $cart->cartid }}" title="Delete">
                                <i class="ti ti-trash fs-lg"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No incomplete shopping carts found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

