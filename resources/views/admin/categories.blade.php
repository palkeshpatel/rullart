@extends('layouts.vertical', ['title' => 'Admin Categories'])

@section('css')
@endsection

@section('content')

@include('layouts.partials/page-title', ['title' => 'Categories'])

<div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1 align-items-center g-1">
    <div class="col">
        <div class="card mb-1">
            <div class="card-body">
                <a href="#!" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                <h5 title="Total Categories">Categories</h5>
                <div class="d-flex align-items-center gap-2 my-3">
                    <div class="avatar-md flex-shrink-0">
                        <span class="avatar-title text-bg-primary rounded-circle fs-22">
                            <i class="ti ti-tags"></i>
                        </span>
                    </div>
                    <h3 class="mb-0">24</h3>
                    <span class="badge badge-soft-primary fw-medium ms-2 fs-xs ms-auto">+3 New</span>
                </div>
                <p class="mb-0">
                    <span class="text-primary"><i class="ti ti-point-filled"></i></span>
                    <span class="text-nowrap text-muted">Active Categories</span>
                    <span class="float-end"><b>20</b></span>
                </p>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card mb-1">
            <div class="card-body">
                <a href="#!" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                <h5 title="Total Products">Products</h5>
                <div class="d-flex align-items-center gap-2 my-3">
                    <div class="avatar-md flex-shrink-0">
                        <span class="avatar-title text-bg-secondary rounded-circle fs-22">
                            <i class="ti ti-package"></i>
                        </span>
                    </div>
                    <h3 class="mb-0">1,240</h3>
                    <span class="badge badge-soft-secondary fw-medium ms-2 fs-xs ms-auto">+24 New</span>
                </div>
                <p class="mb-0">
                    <span class="text-secondary"><i class="ti ti-point-filled"></i></span>
                    <span class="text-nowrap text-muted">Total Products</span>
                    <span class="float-end"><b>980</b></span>
                </p>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card mb-1">
            <div class="card-body">
                <a href="#!" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                <h5 title="Sub Categories">Sub Categories</h5>
                <div class="d-flex align-items-center gap-2 my-3">
                    <div class="avatar-md flex-shrink-0">
                        <span class="avatar-title text-bg-success rounded-circle fs-22">
                            <i class="ti ti-folder"></i>
                        </span>
                    </div>
                    <h3 class="mb-0">156</h3>
                    <span class="badge badge-soft-success fw-medium ms-2 fs-xs ms-auto">+12 New</span>
                </div>
                <p class="mb-0">
                    <span class="text-success"><i class="ti ti-point-filled"></i></span>
                    <span class="text-nowrap text-muted">Total Sub Categories</span>
                    <span class="float-end"><b>142</b></span>
                </p>
            </div>
        </div>
    </div>

    <div class="col">
        <div class="card mb-1">
            <div class="card-body">
                <a href="#!" class="text-muted float-end mt-n1 fs-xl"><i class="ti ti-external-link"></i></a>
                <h5 title="Active Listings">Active Listings</h5>
                <div class="d-flex align-items-center gap-2 my-3">
                    <div class="avatar-md flex-shrink-0">
                        <span class="avatar-title text-bg-info rounded-circle fs-22">
                            <i class="ti ti-check"></i>
                        </span>
                    </div>
                    <h3 class="mb-0">980</h3>
                    <span class="badge badge-soft-info fw-medium ms-2 fs-xs ms-auto">+8.2%</span>
                </div>
                <p class="mb-0">
                    <span class="text-info"><i class="ti ti-point-filled"></i></span>
                    <span class="text-nowrap text-muted">Published Categories</span>
                    <span class="float-end"><b>20</b></span>
                </p>
            </div>
        </div>
    </div>
</div><!-- end row -->

<div class="row">
    <div class="col-12">
        <div data-table data-table-rows-per-page="8" class="card">
            <div class="card-header border-light justify-content-between">

                <div class="d-flex gap-2">
                    <div class="app-search">
                        <input data-table-search type="search" class="form-control" placeholder="Search category name...">
                        <i data-lucide="search" class="app-search-icon text-muted"></i>
                    </div>
                    <button data-table-delete-selected class="btn btn-danger d-none">Delete</button>
                </div>

                <div class="d-flex align-items-center gap-2">

                    <span class="me-2 fw-semibold">Filter By:</span>

                    <!-- Status Filter -->
                    <div class="app-search">
                        <select data-table-filter="status" class="form-select form-control my-1 my-md-0">
                            <option value="">Status</option>
                            <option value="Published">Published</option>
                            <option value="Pending">Pending</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                        <i data-lucide="box" class="app-search-icon text-muted"></i>
                    </div>

                    <!-- Records Per Page -->
                    <div>
                        <select data-table-set-rows-per-page class="form-select form-control my-1 my-md-0">
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="20">20</option>
                        </select>
                    </div>

                </div>

                <div class="d-flex gap-1">
                    <a href="/admin/category" class="btn btn-danger ms-1">
                        <i data-lucide="plus" class="fs-sm me-2"></i> Add Category
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-centered table-select table-hover w-100 mb-0">
                    <thead class="bg-light align-middle bg-opacity-25 thead-sm">
                        <tr class="text-uppercase fs-xxs">
                            <th class="ps-3" style="width: 1%;">
                                <input data-table-select-all class="form-check-input form-check-input-light fs-14 mt-0" type="checkbox" id="select-all-categories" value="option">
                            </th>
                            <th data-table-sort="category">Category</th>
                            <th>Slug</th>
                            <th data-table-sort>Products</th>
                            <th data-table-sort>Sub Categories</th>
                            <th data-table-sort data-column="status">Status</th>
                            <th data-table-sort>Created</th>
                            <th class="text-center" style="width: 1%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Category Row -->
                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ti ti-device-laptop fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Electronics</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>electronics</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">156</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">12</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>18 Apr, 2025 <small class="text-muted">12:24 PM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-secondary-subtle text-secondary rounded">
                                            <i class="ti ti-shirt fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Fashion</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>fashion</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">234</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">18</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>22 Apr, 2025 <small class="text-muted">09:45 AM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-success-subtle text-success rounded">
                                            <i class="ti ti-home fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Home & Living</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>home-living</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">189</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">15</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>24 Apr, 2025 <small class="text-muted">03:10 PM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-info-subtle text-info rounded">
                                            <i class="ti ti-dumbbell fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Sports & Fitness</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>sports-fitness</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">98</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">8</h5>
                            </td>
                            <td><span class="badge badge-soft-warning fs-xxs">Pending</span></td>
                            <td>23 Apr, 2025 <small class="text-muted">10:12 AM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded">
                                            <i class="ti ti-mood-smile fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Beauty & Personal Care</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>beauty-personal-care</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">142</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">10</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>19 Apr, 2025 <small class="text-muted">05:56 PM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded">
                                            <i class="ti ti-gamepad fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Gaming</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>gaming</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">87</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">6</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>18 Apr, 2025 <small class="text-muted">11:30 AM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded">
                                            <i class="ti ti-book fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Books & Media</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>books-media</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">76</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">5</h5>
                            </td>
                            <td><span class="badge badge-soft-success fs-xxs">Published</span></td>
                            <td>17 Apr, 2025 <small class="text-muted">04:21 PM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input form-check-input-light fs-14 category-item-check mt-0" type="checkbox" value="option">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-md me-3">
                                        <span class="avatar-title bg-secondary-subtle text-secondary rounded">
                                            <i class="ti ti-car fs-18"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">
                                            <a data-sort="category" href="#" class="link-reset">Automotive</a>
                                        </h5>
                                        <p class="text-muted mb-0 fs-xxs">Main Category</p>
                                    </div>
                                </div>
                            </td>
                            <td>automotive</td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">54</h5>
                            </td>
                            <td>
                                <h5 class="fs-base mb-0 fw-medium">4</h5>
                            </td>
                            <td><span class="badge badge-soft-warning fs-xxs">Pending</span></td>
                            <td>25 Apr, 2025 <small class="text-muted">10:10 AM</small></td>
                            <td>
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-eye fs-lg"></i></a>
                                    <a href="#" class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-edit fs-lg"></i></a>
                                    <a href="#" data-table-delete-row class="btn btn-light btn-icon btn-sm rounded-circle"><i class="ti ti-trash fs-lg"></i></a>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

            </div>
            <div class="card-footer border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div data-table-pagination-info="categories"></div>
                    <div data-table-pagination></div>
                </div>
            </div>
        </div>

    </div><!-- end col -->
</div><!-- end row -->

@endsection

@section('scripts')
@vite(['resources/js/pages/custom-table.js'])
@endsection

