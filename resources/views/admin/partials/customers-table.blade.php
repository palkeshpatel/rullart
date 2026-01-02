<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover" id="customersTable">
        <thead>
            <tr>
                <th><i class="fa fa-sort"></i> Name</th>
                <th><i class="fa fa-sort"></i> Email</th>
                <th><i class="fa fa-sort"></i> Reg. Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $customer)
            <tr>
                <td>{{ $customer->firstname }} {{ $customer->lastname }}</td>
                <td>{{ $customer->email }}</td>
                <td>{{ $customer->createdon ? \Carbon\Carbon::parse($customer->createdon)->format('d-M-Y') : 'N/A' }}</td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="javascript:void(0);" class="btn btn-light btn-icon btn-sm rounded-circle view-customer-btn" data-customer-id="{{ $customer->customerid }}" title="View">
                            <i class="ti ti-eye fs-lg"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center">No customers found</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

