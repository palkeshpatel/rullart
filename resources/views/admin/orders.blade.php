@extends('layouts.vertical', ['title' => 'Orders List'])

@section('content')

@include('layouts.partials/page-title', ['title' => 'Orders List'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Orders List</h4>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Show 
                            <select class="form-select form-select-sm d-inline-block" style="width: auto;">
                                <option value="25" selected>25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select> entries
                        </label>
                    </div>
                    <div class="col-md-6 text-end">
                        <form method="GET" action="{{ route('admin.orders') }}">
                            <div class="input-group" style="max-width: 300px; margin-left: auto;">
                                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search:" value="{{ request('search') }}">
                                <button class="btn btn-sm btn-primary" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Name</th>
                                <th>Order Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>{{ $order->orderid }}</td>
                                <td>{{ $order->firstname }} {{ $order->lastname }}</td>
                                <td>{{ $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d-M-Y') : 'N/A' }}</td>
                                <td>{{ number_format($order->total, 2) }} {{ $order->currencycode }}</td>
                                <td>
                                    @if($order->fkorderstatus == 2)
                                        <span class="badge badge-soft-warning">Process</span>
                                    @elseif($order->fkorderstatus == 7)
                                        <span class="badge badge-soft-success">Delivered</span>
                                    @else
                                        <span class="badge badge-soft-info">Pending</span>
                                    @endif
                                </td>
                                <td>{{ $order->paymentmethod }}</td>
                                <td>
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle text-muted drop-arrow-none card-drop p-0" data-bs-toggle="dropdown">
                                            <i class="ti ti-dots-vertical fs-lg"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#" class="dropdown-item">View Details</a>
                                            <a href="#" class="dropdown-item">Edit</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No matching records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-sm">
                        <div>Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} entries</div>
                    </div>
                    <div class="col-sm-auto">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

