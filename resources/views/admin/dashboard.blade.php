@extends('layouts.vertical', ['title' => 'Admin Dashboard'])

@section('css')
@vite(['node_modules/jsvectormap/dist/jsvectormap.min.css'])
@endsection

@section('content')

@include('layouts.partials/page-title', ['title' => 'Admin Dashboard'])

<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">
    <!-- Orders Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Orders</h5>
                <span class="badge badge-soft-primary">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalOrders) }}</h3>
                        <p class="mb-0 text-muted">Total Orders</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-primary">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->

    <!-- Return Request Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Return Request</h5>
                <span class="badge badge-soft-info">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalReturnRequests) }}</h3>
                        <p class="mb-0 text-muted">Return Requests</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.return-request') }}" class="btn btn-sm btn-info">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->

    <!-- Customers Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Customers</h5>
                <span class="badge badge-soft-success">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalCustomers) }}</h3>
                        <p class="mb-0 text-muted">Total Customers</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.customers') }}" class="btn btn-sm btn-success">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->

    <!-- Products Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Products</h5>
                <span class="badge badge-soft-warning">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalProducts) }}</h3>
                        <p class="mb-0 text-muted">Total Products</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.products') }}" class="btn btn-sm btn-warning">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->

    <!-- Categories Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Categories</h5>
                <span class="badge badge-soft-danger">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalCategories) }}</h3>
                        <p class="mb-0 text-muted">Total Categories</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.category') }}" class="btn btn-sm btn-danger">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->

    <!-- Product Review Widget -->
    <div class="col">
        <div class="card">
            <div class="card-header d-flex border-dashed justify-content-between align-items-center">
                <h5 class="card-title">Product Review</h5>
                <span class="badge badge-soft-danger">Total</span>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="donut-chart" data-chart="donut" style="min-height: 60px; width: 60px;"></div>
                    <div class="text-end">
                        <h3 class="mb-2 fw-normal">{{ number_format($totalProductReviews) }}</h3>
                        <p class="mb-0 text-muted">Total Reviews</p>
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('admin.product-rate') }}" class="btn btn-sm btn-danger">More info</a>
                </div>
            </div>
        </div>
    </div><!-- end col -->
</div><!-- end row -->

<div class="row">
    <!-- Last 10 Orders -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Last 10 Orders</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Name</th>
                                <th>Order Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastOrders as $order)
                            <tr>
                                <td>{{ $order->orderid }}</td>
                                <td>{{ $order->firstname }} {{ $order->lastname }}</td>
                                <td>{{ $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d-M-Y') : 'N/A' }}</td>
                                <td>
                                    @if($order->fkorderstatus == 2)
                                        <span class="badge badge-soft-warning">Process</span>
                                    @elseif($order->fkorderstatus == 7)
                                        <span class="badge badge-soft-success">Delivered</span>
                                    @else
                                        <span class="badge badge-soft-info">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No orders found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-primary">View All Orders</a>
            </div>
        </div>
    </div>

    <!-- Last 10 Customers -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Last 10 Customers</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Reg. Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastCustomers as $customer)
                            <tr>
                                <td>{{ $customer->firstname }} {{ $customer->lastname }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->createdon ? \Carbon\Carbon::parse($customer->createdon)->format('d-M-Y') : 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">No customers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.customers') }}" class="btn btn-sm btn-primary">View All Customers</a>
            </div>
        </div>
    </div>

    <!-- Last 10 Product Review -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Last 10 Product Review</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-centered table-custom table-sm table-nowrap table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Rate</th>
                                <th>Date</th>
                                <th>Is Published</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lastReviews as $review)
                            <tr>
                                <td>{{ $review->customer ? $review->customer->firstname . ' ' . $review->customer->lastname : 'N/A' }}</td>
                                <td>{{ $review->rate }}</td>
                                <td>{{ $review->submiton ? \Carbon\Carbon::parse($review->submiton)->format('d-M-Y') : 'N/A' }}</td>
                                <td>{{ $review->ispublished ? 'Yes' : 'No' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">No reviews found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <a href="{{ route('admin.product-rate') }}" class="btn btn-sm btn-primary">View All Reviews</a>
            </div>
        </div>
    </div>
</div> <!-- end row-->

@endsection

@section('scripts')
@vite(['resources/js/pages/custom-table.js','resources/js/pages/dashboard-2.js'])
@endsection