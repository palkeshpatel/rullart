<div class="modal fade" id="cartViewModal" tabindex="-1" aria-labelledby="cartViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cartViewModalLabel">Cart Details - #{{ $cart->cartid }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column - Addresses -->
                    <div class="col-md-6">
                        <!-- Delivery Address -->
                        @if($cart->addressid)
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Delivery Address</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>{{ $cart->addressbook->firstname ?? $cart->customer->firstname ?? '' }} {{ $cart->addressbook->lastname ?? $cart->customer->lastname ?? '' }}</strong></p>
                                @if($cart->addressbook && $cart->addressbook->block_number)
                                    <p class="mb-1">Block: {{ $cart->addressbook->block_number }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->street_number)
                                    <p class="mb-1">Street: {{ $cart->addressbook->street_number }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->avenue_number)
                                    <p class="mb-1">Avenue: {{ $cart->addressbook->avenue_number ?: 'Nothing' }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->house_number)
                                    <p class="mb-1">House/building: {{ $cart->addressbook->house_number }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->city)
                                    <p class="mb-1">City: {{ $cart->addressbook->city }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->country)
                                    <p class="mb-1">Country: {{ $cart->addressbook->country }}</p>
                                @endif
                                @if($cart->addressbook && $cart->addressbook->mobile)
                                    <p class="mb-0">Phone: {{ $cart->addressbook->mobile }}</p>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Customer Information -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Name:</strong> {{ ($cart->customer->firstname ?? '') . ' ' . ($cart->customer->lastname ?? '') ?: 'N/A' }}</p>
                                <p class="mb-1"><strong>Email:</strong> {{ $cart->customer->email ?? 'N/A' }}</p>
                                @if($cart->customer && $cart->customer->mobile)
                                    <p class="mb-0"><strong>Mobile:</strong> {{ $cart->customer->mobile }}</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Cart Information -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Cart Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Cart ID:</strong></label>
                                    <p class="mb-0">{{ $cart->cartid }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Order Date:</strong></label>
                                    <p class="mb-0">{{ $cart->orderdate ? \Carbon\Carbon::parse($cart->orderdate)->format('d F Y H:i') : 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Payment Method:</strong></label>
                                    <p class="mb-0">{{ $cart->paymentmethod ?: 'N/A' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Item Total:</strong></label>
                                    <p class="mb-0">KWD {{ number_format($cart->itemtotal ?? 0, 3) }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Shipping Charge:</strong></label>
                                    <p class="mb-0">KWD {{ number_format($cart->shipping_charge ?? 0, 3) }}</p>
                                </div>
                                @if($cart->giftbox_charge)
                                <div class="mb-3">
                                    <label class="form-label"><strong>Gift Box Charge:</strong></label>
                                    <p class="mb-0">KWD {{ number_format($cart->giftbox_charge, 3) }}</p>
                                </div>
                                @endif
                                @if($cart->discount)
                                <div class="mb-3">
                                    <label class="form-label"><strong>Discount:</strong></label>
                                    <p class="mb-0">KWD {{ number_format($cart->discount, 3) }}</p>
                                </div>
                                @endif
                                @if($cart->vat)
                                <div class="mb-3">
                                    <label class="form-label"><strong>VAT:</strong></label>
                                    <p class="mb-0">KWD {{ number_format($cart->vat, 3) }}</p>
                                </div>
                                @endif
                                <div class="mb-3">
                                    <label class="form-label"><strong>Total:</strong></label>
                                    <p class="mb-0"><strong>KWD {{ number_format($cart->total ?? 0, 3) }}</strong></p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Order From:</strong></label>
                                    <p class="mb-0">
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
                                    </p>
                                </div>
                                @if($cart->couponcode)
                                <div class="mb-3">
                                    <label class="form-label"><strong>Coupon Code:</strong></label>
                                    <p class="mb-0">{{ $cart->couponcode }}</p>
                                </div>
                                @endif
                                @if($cart->giftMessage)
                                <div class="mb-3">
                                    <label class="form-label"><strong>Gift Message:</strong></label>
                                    <p class="mb-0">{{ $cart->giftMessage }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Items Table -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Cart Items</h5>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($cartItems as $item)
                                            <tr>
                                                <td>{{ $item->product->title ?? 'N/A' }}</td>
                                                <td>{{ $item->product->productcode ?? 'N/A' }}</td>
                                                <td>{{ $item->size ?? 'N/A' }}</td>
                                                <td>KWD {{ number_format($item->price ?? $item->actualprice ?? 0, 3) }}</td>
                                                <td>{{ $item->qty ?? 0 }}</td>
                                                <td>KWD {{ number_format($item->subtotal ?? 0, 3) }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center">No items found</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                                <td><strong>KWD {{ number_format($cart->total ?? 0, 3) }}</strong></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger delete-cart-btn-modal" data-cart-id="{{ $cart->cartid }}">
                    <i class="ti ti-trash me-1"></i> Delete Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete button in modal
    document.querySelectorAll('.delete-cart-btn-modal').forEach(btn => {
        btn.addEventListener('click', function() {
            const cartId = this.getAttribute('data-cart-id');
            const modal = bootstrap.Modal.getInstance(document.getElementById('cartViewModal'));
            if (modal) modal.hide();
            
            // Trigger delete
            setTimeout(() => {
                const deleteBtn = document.querySelector(`.delete-cart-btn[data-cart-id="${cartId}"]`);
                if (deleteBtn) deleteBtn.click();
            }, 300);
        });
    });
});
</script>

