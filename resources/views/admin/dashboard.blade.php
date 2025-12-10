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
    <div class="col-12">
        <div class="card">
            <div class="card-header border-dashed card-tabs d-flex align-items-center">
                <div class="flex-grow-1">
                    <h4 class="card-title">Orders Statics</h4>
                </div>
                <ul class="nav nav-tabs nav-justified card-header-tabs nav-bordered">
                    <li class="nav-item">
                        <a href="#today-ct" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <i class="ti ti-home d-md-none d-block"></i>
                            <span class="d-none d-md-block">Today</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#monthly-ct" data-bs-toggle="tab" aria-expanded="true" class="nav-link active">
                            <i class="ti ti-user-circle d-md-none d-block"></i>
                            <span class="d-none d-md-block">Monthly</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#annual-ct" data-bs-toggle="tab" aria-expanded="false" class="nav-link">
                            <i class="ti ti-settings d-md-none d-block"></i>
                            <span class="d-none d-md-block">Annual</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="row g-0">
                    <div class="col-xxl-8 border-end border-dashed">
                        <div id="orders-chart" style="min-height: 405px;"></div>
                    </div><!-- end col -->
                    <div class="col-xxl-4">
                        <div class="p-3 bg-light-subtle border-bottom border-dashed">
                            <div class="row">
                                <div class="col">
                                    <h4 class="fs-sm mb-1">Would you like the full report?</h4>
                                    <small class="text-muted fs-xs mb-0">
                                        All 120 orders have been successfully delivered
                                    </small>
                                </div>
                                <div class="col-auto align-self-center">
                                    <button type="button" class="btn btn-sm btn-default rounded-circle btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Download">
                                        <i class="ti ti-download fs-xl"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="row row-cols-xxl-2 row-cols-md-2 row-cols-1 g-1 p-1">
                            <!-- Total Sales Widget -->
                            <div class="col">
                                <div class="card rounded-0 border shadow-none border-dashed mb-0">
                                    <div class="card-body">
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <h5 class="fs-xl mb-0">$24,500</h5>
                                            <span>18.45% <i class="ti ti-arrow-up text-success"></i></span>
                                        </div>
                                        <p class="text-muted mb-2"><span>Total sales in period</span></p>
                                        <div class="progress progress-sm mb-0">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 18.45%" aria-valuenow="18.45" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <!-- Number of Customers Widget -->
                            <div class="col">
                                <div class="card rounded-0 border shadow-none border-dashed mb-0">
                                    <div class="card-body">
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <h5 class="fs-xl mb-0">1,240</h5>
                                            <span>10.35% <i class="ti ti-arrow-down text-danger"></i></span>
                                        </div>
                                        <p class="text-muted mb-2"><span>Number of customers</span></p>
                                        <div class="progress progress-sm mb-0">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 10.35%" aria-valuenow="10.35" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <!-- Products Sold Widget -->
                            <div class="col">
                                <div class="card rounded-0 border shadow-none border-dashed mb-0">
                                    <div class="card-body">
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <h5 class="fs-xl mb-0">3,750</h5>
                                            <span>22.61% <i class="ti ti-bolt text-primary"></i></span>
                                        </div>
                                        <p class="text-muted mb-2 text-truncate"><span>Products sold in the period</span>
                                        </p>
                                        <div class="progress progress-sm mb-0">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 22.61%" aria-valuenow="22.61" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->

                            <!-- Average Order Value Widget -->
                            <div class="col">
                                <div class="card rounded-0 border shadow-none border-dashed mb-0">
                                    <div class="card-body">
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <h5 class="fs-xl mb-0">$65.49 <small class="fs-6">USD</small>
                                            </h5>
                                            <span>5.92% <i class="ti ti-arrow-up text-success"></i></span>
                                        </div>
                                        <p class="text-muted mb-2"><span>Average order value</span></p>
                                        <div class="progress progress-sm mb-0">
                                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 5.92%" aria-valuenow="5.92" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end col -->
                        </div><!-- end row -->

                        <div class="text-center my-3">
                            <a href="#" class="link-reset text-decoration-underline fw-semibold link-offset-3">
                                View all Reports <i class="ti ti-send-2"></i>
                            </a>
                        </div>

                    </div> <!-- end col-->
                </div> <!-- end row-->
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col-->
</div> <!-- end row-->

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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header justify-content-between align-items-center">
                <h5 class="card-title">Transactions Worldwide</h5>
                <div class="card-action">
                    <a href="#!" class="card-action-item" data-action="card-toggle"><i class="ti ti-chevron-up"></i></a>
                    <a href="#!" class="card-action-item" data-action="card-refresh"><i class="ti ti-refresh"></i></a>
                    <a href="#!" class="card-action-item" data-action="card-close"><i class="ti ti-x"></i></a>
                </div>
            </div>
            <div class="card-body pt-2">
                <div class="row align-items-center">
                    <div class="col-xl-6">
                        <div class="table-responsive">
                            <table class="table table-custom table-nowrap table-hover table-centered mb-0">
                                <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                                    <tr class="text-uppercase fs-xxs">
                                        <th class="text-muted">Tran. No.</th>
                                        <th class="text-muted">Order</th>
                                        <th class="text-muted">Date</th>
                                        <th class="text-muted">Amount</th>
                                        <th class="text-muted">Status</th>
                                        <th class="text-muted">Payment Method</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><a href="#!" class="link-reset fw-semibold">#TR-3468</a></td>
                                        <td>#ORD-1003 - Smart Watch</td>
                                        <td>27 Apr 2025 <small class="text-muted">02:15 PM</small></td>
                                        <td class="fw-semibold">$89.99</td>
                                        <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                        <td>
                                            <img src="/images/cards/mastercard.svg" alt="" class="me-2" height="28"> xxxx 1123
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="#!" class="link-reset fw-semibold">#TR-3469</a></td>
                                        <td>#ORD-1004 - Gaming Mouse</td>
                                        <td>26 Apr 2025 <small class="text-muted">09:42 AM</small></td>
                                        <td class="fw-semibold">$24.99</td>
                                        <td><span class="badge badge-soft-danger fs-xxs"><i class="ti ti-point-filled"></i> Failed</span></td>
                                        <td>
                                            <img src="/images/cards/visa.svg" alt="" class="me-2" height="28"> xxxx 3490
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="#!" class="link-reset fw-semibold">#TR-3470</a></td>
                                        <td>#ORD-1005 - Fitness Tracker Band</td>
                                        <td>25 Apr 2025 <small class="text-muted">11:10 AM</small></td>
                                        <td class="fw-semibold">$34.95</td>
                                        <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                        <td>
                                            <img src="/images/cards/american-express.svg" alt="" class="me-2" height="28"> xxxx 8765
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="#!" class="link-reset fw-semibold">#TR-3471</a></td>
                                        <td>#ORD-1006 - Wireless Keyboard</td>
                                        <td>24 Apr 2025 <small class="text-muted">08:58 PM</small></td>
                                        <td class="fw-semibold">$59.00</td>
                                        <td><span class="badge badge-soft-warning fs-xxs"><i class="ti ti-point-filled"></i> Pending</span></td>
                                        <td>
                                            <img src="/images/cards/mastercard.svg" alt="" class="me-2" height="28"> xxxx 5566
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><a href="#!" class="link-reset fw-semibold">#TR-3472</a></td>
                                        <td>#ORD-1007 - Portable Charger</td>
                                        <td>23 Apr 2025 <small class="text-muted">05:37 PM</small></td>
                                        <td class="fw-semibold">$45.80</td>
                                        <td><span class="badge badge-soft-success fs-xxs"><i class="ti ti-point-filled"></i> Paid</span></td>
                                        <td>
                                            <img src="/images/cards/visa.svg" alt="" class="me-2" height="28"> xxxx 9012
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> <!-- end table-responsive-->

                        <div class="text-center mt-3">
                            <a href="#!" class="link-reset text-decoration-underline fw-semibold link-offset-3">
                                View All Transactions <i class="ti ti-send-2"></i>
                            </a>
                        </div>
                    </div> <!-- end col-->
                    <div class="col-xl-6">
                        <div id="map_1" class="w-100 mt-4 mt-xl-0" style="height: 297px"></div>
                    </div> <!-- end col-->
                </div><!-- end row-->
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col-->
</div> <!-- end row-->

@endsection

@section('scripts')
@vite(['resources/js/pages/custom-table.js','resources/js/pages/dashboard-2.js'])
@endsection