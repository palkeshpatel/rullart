<!-- Sidenav Menu Start -->
<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="/" class="logo">
        <span class="logo logo-light">
            <span class="logo-lg"><span class="text-white fw-bold fs-18">Rullart</span></span>
            <span class="logo-sm"><span class="text-white fw-bold">Rullart</span></span>
        </span>

        <span class="logo logo-dark">
            <span class="logo-lg"><span class="text-dark fw-bold fs-18">Rullart</span></span>
            <span class="logo-sm"><span class="text-dark fw-bold">Rullart</span></span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-on-hover">
        <i class="ti ti-menu-4 fs-22 align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-offcanvas">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div class="scrollbar" data-simplebar>

        <!-- User -->
        <div class="sidenav-user">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="javascript:void(0);" class="link-reset">
                        <img src="/images/users/user-2.jpg" alt="user-image" class="rounded-circle mb-2 avatar-md">
                        <span
                            class="sidenav-user-name fw-bold">{{ Auth::guard('admin')->user()->name ?? 'Admin' }}</span>
                        <span class="fs-12 fw-semibold"
                            data-lang="user-role">{{ Auth::guard('admin')->user()->user ?? 'Admin User' }}</span>
                    </a>
                </div>
                <div>
                    <a class="dropdown-toggle drop-arrow-none link-reset sidenav-user-set-icon"
                        data-bs-toggle="dropdown" data-bs-offset="0,12" href="#!" aria-haspopup="false"
                        aria-expanded="false">
                        <i class="ti ti-settings fs-24 align-middle ms-1"></i>
                    </a>

                    <div class="dropdown-menu">
                        <!-- Header -->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome back!</h6>
                        </div>

                        <!-- My Profile -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-user-circle me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Profile</span>
                        </a>

                        <!-- Notifications -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-bell-ringing me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Notifications</span>
                        </a>

                        <!-- Wallet -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-credit-card me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Balance: <span class="fw-semibold">$985.25</span></span>
                        </a>

                        <!-- Settings -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-settings-2 me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Account Settings</span>
                        </a>

                        <!-- Support -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-headset me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Support Center</span>
                        </a>

                        <!-- Divider -->
                        <div class="dropdown-divider"></div>

                        <!-- Lock -->
                        <a href="javascript:void(0);" class="dropdown-item">
                            <i class="ti ti-lock me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Lock Screen</span>
                        </a>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit"
                                class="dropdown-item text-danger fw-semibold w-100 text-start border-0 bg-transparent">
                                <i class="ti ti-logout-2 me-2 fs-17 align-middle"></i>
                                <span class="align-middle">Log Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-title">MAIN NAVIGATION</li>

            <li class="side-nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-layout-dashboard"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#manageOrders"
                    aria-expanded="{{ request()->routeIs('admin.customers') || request()->routeIs('admin.orders') || request()->routeIs('admin.orders-not-process') || request()->routeIs('admin.wishlist') || request()->routeIs('admin.product-rate') || request()->routeIs('admin.mobile-device') || request()->routeIs('admin.return-request') ? 'true' : 'false' }}"
                    aria-controls="manageOrders"
                    class="side-nav-link {{ request()->routeIs('admin.customers') || request()->routeIs('admin.orders') || request()->routeIs('admin.orders-not-process') || request()->routeIs('admin.wishlist') || request()->routeIs('admin.product-rate') || request()->routeIs('admin.mobile-device') || request()->routeIs('admin.return-request') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-flag"></i></span>
                    <span class="menu-text">Manage Orders</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.customers') || request()->routeIs('admin.orders') || request()->routeIs('admin.orders-not-process') || request()->routeIs('admin.wishlist') || request()->routeIs('admin.product-rate') || request()->routeIs('admin.mobile-device') || request()->routeIs('admin.return-request') ? 'show' : '' }}"
                    id="manageOrders">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.customers') }}"
                                class="side-nav-link {{ request()->routeIs('admin.customers') ? 'active' : '' }}">
                                <span class="menu-text">Customers</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.orders') }}"
                                class="side-nav-link {{ request()->routeIs('admin.orders') ? 'active' : '' }}">
                                <span class="menu-text">Orders</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.orders-not-process') }}"
                                class="side-nav-link {{ request()->routeIs('admin.orders-not-process') ? 'active' : '' }}">
                                <span class="menu-text">Shopping Cart Not Complete</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.wishlist') }}"
                                class="side-nav-link {{ request()->routeIs('admin.wishlist') ? 'active' : '' }}">
                                <span class="menu-text">Wishlist</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.product-rate') }}"
                                class="side-nav-link {{ request()->routeIs('admin.product-rate') ? 'active' : '' }}">
                                <span class="menu-text">Product Review</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.mobile-device') }}"
                                class="side-nav-link {{ request()->routeIs('admin.mobile-device') ? 'active' : '' }}">
                                <span class="menu-text">Mobile Devices</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.return-request') }}"
                                class="side-nav-link {{ request()->routeIs('admin.return-request') ? 'active' : '' }}">
                                <span class="menu-text">Return Request</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#manageProducts"
                    aria-expanded="{{ request()->routeIs('admin.category') || request()->routeIs('admin.occassion') || request()->routeIs('admin.products') || request()->routeIs('admin.gift-products') || request()->routeIs('admin.gift-products4') ? 'true' : 'false' }}"
                    aria-controls="manageProducts"
                    class="side-nav-link {{ request()->routeIs('admin.category') || request()->routeIs('admin.occassion') || request()->routeIs('admin.products') || request()->routeIs('admin.gift-products') || request()->routeIs('admin.gift-products4') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-apple"></i></span>
                    <span class="menu-text">Manage Products</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.category') || request()->routeIs('admin.occassion') || request()->routeIs('admin.products') || request()->routeIs('admin.gift-products') || request()->routeIs('admin.gift-products4') ? 'show' : '' }}"
                    id="manageProducts">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.category') }}"
                                class="side-nav-link {{ request()->routeIs('admin.category') ? 'active' : '' }}">
                                <span class="menu-text">Category</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.occassion') }}"
                                class="side-nav-link {{ request()->routeIs('admin.occassion') ? 'active' : '' }}">
                                <span class="menu-text">Occassion</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.products') }}"
                                class="side-nav-link {{ request()->routeIs('admin.products') ? 'active' : '' }}">
                                <span class="menu-text">Products</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.gift-products') }}"
                                class="side-nav-link {{ request()->routeIs('admin.gift-products') ? 'active' : '' }}">
                                <span class="menu-text">Gift Product</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.gift-products4') }}"
                                class="side-nav-link {{ request()->routeIs('admin.gift-products4') ? 'active' : '' }}">
                                <span class="menu-text">Gift Product 4</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#reports"
                    aria-expanded="{{ request()->routeIs('admin.sales-report-date') || request()->routeIs('admin.sales-report-month') || request()->routeIs('admin.sales-report-year') || request()->routeIs('admin.sales-report-customer') || request()->routeIs('admin.top-product-month') || request()->routeIs('admin.top-product-rate') ? 'true' : 'false' }}"
                    aria-controls="reports"
                    class="side-nav-link {{ request()->routeIs('admin.sales-report-date') || request()->routeIs('admin.sales-report-month') || request()->routeIs('admin.sales-report-year') || request()->routeIs('admin.sales-report-customer') || request()->routeIs('admin.top-product-month') || request()->routeIs('admin.top-product-rate') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-chart-bar"></i></span>
                    <span class="menu-text">Reports</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.sales-report-date') || request()->routeIs('admin.sales-report-month') || request()->routeIs('admin.sales-report-year') || request()->routeIs('admin.sales-report-customer') || request()->routeIs('admin.top-product-month') || request()->routeIs('admin.top-product-rate') ? 'show' : '' }}"
                    id="reports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sales-report-date') }}"
                                class="side-nav-link {{ request()->routeIs('admin.sales-report-date') ? 'active' : '' }}">
                                <span class="menu-text">Sales Report - Datewise</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sales-report-month') }}"
                                class="side-nav-link {{ request()->routeIs('admin.sales-report-month') ? 'active' : '' }}">
                                <span class="menu-text">Sales Report - Monthwise</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sales-report-year') }}"
                                class="side-nav-link {{ request()->routeIs('admin.sales-report-year') ? 'active' : '' }}">
                                <span class="menu-text">Sales Report - Yearwise</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sales-report-customer') }}"
                                class="side-nav-link {{ request()->routeIs('admin.sales-report-customer') ? 'active' : '' }}">
                                <span class="menu-text">Sales Report - Customerwise</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.top-product-month') }}"
                                class="side-nav-link {{ request()->routeIs('admin.top-product-month') ? 'active' : '' }}">
                                <span class="menu-text">Top Selling Products</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.top-product-rate') }}"
                                class="side-nav-link {{ request()->routeIs('admin.top-product-rate') ? 'active' : '' }}">
                                <span class="menu-text">Top Rating Products</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#masters"
                    aria-expanded="{{ request()->routeIs('admin.colors') || request()->routeIs('admin.areas') || request()->routeIs('admin.countries') || request()->routeIs('admin.sizes') || request()->routeIs('admin.coupon-code') || request()->routeIs('admin.discounts') || request()->routeIs('admin.courier-company') || request()->routeIs('admin.messages') ? 'true' : 'false' }}"
                    aria-controls="masters"
                    class="side-nav-link {{ request()->routeIs('admin.colors') || request()->routeIs('admin.areas') || request()->routeIs('admin.countries') || request()->routeIs('admin.sizes') || request()->routeIs('admin.coupon-code') || request()->routeIs('admin.discounts') || request()->routeIs('admin.courier-company') || request()->routeIs('admin.messages') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-list"></i></span>
                    <span class="menu-text">Masters</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.colors') || request()->routeIs('admin.areas') || request()->routeIs('admin.countries') || request()->routeIs('admin.sizes') || request()->routeIs('admin.coupon-code') || request()->routeIs('admin.discounts') || request()->routeIs('admin.courier-company') || request()->routeIs('admin.messages') ? 'show' : '' }}"
                    id="masters">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.colors') }}"
                                class="side-nav-link {{ request()->routeIs('admin.colors') ? 'active' : '' }}">
                                <span class="menu-text">Colors</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.areas') }}"
                                class="side-nav-link {{ request()->routeIs('admin.areas') ? 'active' : '' }}">
                                <span class="menu-text">Areas</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.countries') }}"
                                class="side-nav-link {{ request()->routeIs('admin.countries') ? 'active' : '' }}">
                                <span class="menu-text">Countries</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.sizes') }}"
                                class="side-nav-link {{ request()->routeIs('admin.sizes') ? 'active' : '' }}">
                                <span class="menu-text">Sizes</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.coupon-code') }}"
                                class="side-nav-link {{ request()->routeIs('admin.coupon-code') ? 'active' : '' }}">
                                <span class="menu-text">Coupon code</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.discounts') }}"
                                class="side-nav-link {{ request()->routeIs('admin.discounts') ? 'active' : '' }}">
                                <span class="menu-text">Discount Offer</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.courier-company') }}"
                                class="side-nav-link {{ request()->routeIs('admin.courier-company') ? 'active' : '' }}">
                                <span class="menu-text">Courier Company</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.messages') }}"
                                class="side-nav-link {{ request()->routeIs('admin.messages') ? 'active' : '' }}">
                                <span class="menu-text">Gift Messages</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#managePages"
                    aria-expanded="{{ request()->routeIs('admin.home-gallery') || request()->routeIs('admin.pages.home') || request()->routeIs('admin.pages.aboutus') || request()->routeIs('admin.pages.corporate-gift') || request()->routeIs('admin.pages.franchises') || request()->routeIs('admin.pages.contactus') || request()->routeIs('admin.pages.shipping') || request()->routeIs('admin.pages.terms') || request()->routeIs('admin.pages.newsletter') ? 'true' : 'false' }}"
                    aria-controls="managePages"
                    class="side-nav-link {{ request()->routeIs('admin.home-gallery') || request()->routeIs('admin.pages.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-book"></i></span>
                    <span class="menu-text">Manage Pages</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.home-gallery') || request()->routeIs('admin.pages.*') ? 'show' : '' }}"
                    id="managePages">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.home-gallery') }}"
                                class="side-nav-link {{ request()->routeIs('admin.home-gallery') ? 'active' : '' }}">
                                <span class="menu-text">Home Gallery</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.home') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.home') ? 'active' : '' }}">
                                <span class="menu-text">Home</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.aboutus') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.aboutus') ? 'active' : '' }}">
                                <span class="menu-text">AboutUs</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.corporate-gift') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.corporate-gift') ? 'active' : '' }}">
                                <span class="menu-text">CorporateGifts</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.franchises') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.franchises') ? 'active' : '' }}">
                                <span class="menu-text">Franchises</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.contactus') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.contactus') ? 'active' : '' }}">
                                <span class="menu-text">ContactUs</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.shipping') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.shipping') ? 'active' : '' }}">
                                <span class="menu-text">Shipping</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.newsletter') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.newsletter') ? 'active' : '' }}">
                                <span class="menu-text">Newsletter</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.pages.terms') }}"
                                class="side-nav-link {{ request()->routeIs('admin.pages.terms') ? 'active' : '' }}">
                                <span class="menu-text">Terms & Conditions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('admin.settings') }}"
                    class="side-nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span>
                    <span class="menu-text">Settings</span>
                </a>
            </li>

        </ul>
    </div>
</div>
<!-- Sidenav Menu End -->
