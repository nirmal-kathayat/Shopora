<!--start header -->
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="user-box dropdown px-3" id="shoporaUserBox">
                <button type="button"
                        class="shopora-profile-btn d-flex align-items-center gap-2"
                        id="shoporaProfileDropdown"
                        aria-expanded="false"
                        aria-haspopup="true">
                    <img src="{{ asset('assets/images/avatars/user-img.png') }}" class="user-img" alt="user avatar">
                    <span class="user-info">
                        <span class="user-name mb-0 d-block">{{ \Auth::guard('admin')->user()->name }}</span>
                    </span>
                    <i class="bx bx-chevron-down fs-5 shopora-profile-caret"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0"
                    id="shoporaProfileMenu"
                    style="min-width: 180px;">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('admin.profile') }}">
                            <i class="bx bx-user fs-5"></i><span>My Profile</span>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ route('logout') }}">
                            <i class="bx bx-log-out-circle fs-5"></i><span>Logout</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
<!--end header -->
