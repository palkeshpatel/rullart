@extends('layouts.vertical', ['title' => 'Customers List'])

@section('content')

@include('layouts.partials/page-title', ['title' => 'Customers List'])

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header justify-content-between align-items-center border-dashed">
                <h4 class="card-title mb-0">Customers List</h4>
                <div class="d-flex gap-2">
                    <a href="javascript:void(0);" class="btn btn-sm btn-primary">
                        <i class="ti ti-file-export me-1"></i> Export Customers
                    </a>
                </div>
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
                        <form method="GET" action="{{ route('admin.customers') }}">
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
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Site</th>
                                <th>Login Type</th>
                                <th>Is Active?</th>
                                <th>Last Login</th>
                                <th>Register Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>{{ $customer->firstname }}</td>
                                <td>{{ $customer->lastname }}</td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->site ?? 'N/A' }}</td>
                                <td>{{ $customer->login_type ?? 'N/A' }}</td>
                                <td>{{ $customer->isactive ? 'Yes' : 'No' }}</td>
                                <td>{{ $customer->last_login ? \Carbon\Carbon::parse($customer->last_login)->format('d-M-Y') : 'N/A' }}</td>
                                <td>{{ $customer->createdon ? \Carbon\Carbon::parse($customer->createdon)->format('d-M-Y') : 'N/A' }}</td>
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
                                <td colspan="9" class="text-center">No matching records found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="row mt-3">
                    <div class="col-sm">
                        <div>Showing {{ $customers->firstItem() ?? 0 }} to {{ $customers->lastItem() ?? 0 }} of {{ $customers->total() }} entries</div>
                    </div>
                    <div class="col-sm-auto">
                        {{ $customers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

