<div class="table-responsive">
    <table class="table table-custom table-nowrap table-hover table-centered mb-0" id="cartsTable">
        <thead class="bg-light align-middle bg-opacity-25 thead-sm">
            <tr class="text-uppercase fs-xxs">
                <th class="text-muted">Ref #</th>
                <th class="text-muted">Name</th>
                <th class="text-muted">Email</th>
                <th class="text-muted">Total</th>
                <th class="text-muted">Order Date</th>
                <th class="text-muted">Payment Method</th>
                <th class="text-muted">Order From</th>
                <th class="text-muted">Email Count</th>
                <th class="text-muted">Email Send Date</th>
                <th class="text-muted">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carts as $cart)
                <tr>
                    <td>
                        <a href="#" class="link-reset fw-semibold">#{{ $cart->shoppingcartid }}</a>
                    </td>
                    <td>
                        {{ $cart->customer ? ($cart->customer->firstname . ' ' . $cart->customer->lastname) : 'N/A' }}
                    </td>
                    <td>{{ $cart->customer ? $cart->customer->email : 'N/A' }}</td>
                    <td class="fw-semibold">{{ number_format($cart->totalamt ?? 0, 3) }}</td>
                    <td>
                        @if($cart->updatedon)
                            {{ \Carbon\Carbon::parse($cart->updatedon)->format('d/M/Y H:i') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-soft-info">Pending</span>
                    </td>
                    <td>
                        <span class="badge badge-soft-secondary">Web</span>
                    </td>
                    <td>0</td>
                    <td>-</td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" title="View">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-4">
                        <div class="text-muted">No incomplete shopping carts found.</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

