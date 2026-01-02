<div class="modal fade" id="customerViewModal" tabindex="-1" aria-labelledby="customerViewModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerViewModalLabel">Customer Details - {{ $customer->firstname }} {{ $customer->lastname }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th style="width: 40%;">ID:</th>
                                <td>{{ $customer->customerid }}</td>
                            </tr>
                            <tr>
                                <th>First Name:</th>
                                <td>{{ $customer->firstname }}</td>
                            </tr>
                            <tr>
                                <th>Last Name:</th>
                                <td>{{ $customer->lastname ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $customer->email }}</td>
                            </tr>
                            <tr>
                                <th>Mobile:</th>
                                <td>{{ $customer->mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Registration Date:</th>
                                <td>{{ $customer->createdon ? \Carbon\Carbon::parse($customer->createdon)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Login:</th>
                                <td>{{ $customer->last_login ? \Carbon\Carbon::parse($customer->last_login)->format('d-M-Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Login Type:</th>
                                <td>{{ $customer->login_type ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Active:</th>
                                <td>
                                    @if($customer->isactive)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-danger">No</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

