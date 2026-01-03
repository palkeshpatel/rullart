<!-- Topbar Start -->
<header class="app-topbar">
    <div class="container-fluid topbar-menu">
        <div class="d-flex align-items-center gap-2">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo light -->
                <a href="{{ route('admin.dashboard') }}" class="logo-light">
                    <span class="logo-lg">
                        <img src="{{ asset('resources/images/rullart-logo.svg') }}" alt="Rullart Logo" style="height: 40px;">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('resources/images/rullart-logo.svg') }}" alt="Rullart Logo" style="height: 32px;">
                    </span>
                </a>

                <!-- Logo Dark -->
                <a href="{{ route('admin.dashboard') }}" class="logo-dark">
                    <span class="logo-lg">
                        <img src="{{ asset('resources/images/rullart-logo.svg') }}" alt="Rullart Logo" style="height: 40px;">
                    </span>
                    <span class="logo-sm">
                        <img src="{{ asset('resources/images/rullart-logo.svg') }}" alt="Rullart Logo" style="height: 32px;">
                    </span>
                </a>
            </div>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button btn btn-primary btn-icon">
                <i class="ti ti-menu-4 fs-22"></i>
            </button>



        </div> <!-- .d-flex-->

        <div class="d-flex align-items-center gap-2">



            <!-- Settings Link -->
            <div class="topbar-item d-none d-sm-flex">
                <a href="{{ route('admin.settings') }}" class="topbar-link">
                    <i data-lucide="settings" class="fs-xxl"></i>
                </a>
            </div>

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i data-lucide="moon" class="fs-xxl mode-light-moon"></i>
                    <i data-lucide="sun" class="fs-xxl mode-light-sun"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown"
                        data-bs-offset="0,16" href="#!" aria-haspopup="false" aria-expanded="false">
                        <img src="/images/users/user-2.jpg" width="32" class="rounded-circle me-lg-2 d-flex"
                            alt="user-image">
                        <div class="d-lg-flex align-items-center gap-1 d-none">
                            <h5 class="my-0">Damian D.</h5>
                            <i class="ti ti-chevron-down align-middle"></i>
                        </div>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- Change Password -->
                        <a href="javascript:void(0);" class="dropdown-item" id="changePasswordBtnTopbar">
                            <i class="ti ti-key me-2 fs-17 align-middle"></i>
                            <span class="align-middle">Change Password</span>
                        </a>

                        <!-- Divider -->
                        <div class="dropdown-divider"></div>

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
    </div>
</header>
<!-- Topbar End -->
