<!--sidebar wrapper -->
<style>
    /* Shopora: clean header/sidebar borders */
    .sidebar-wrapper {
        box-shadow: none !important;
        /* line drawn by .wrapper::before so it reaches absolute top */
        border-right: none !important;
        z-index: 12 !important;
    }

    /* Full-height vertical divider (same style as header separators) */
    .wrapper::before {
        content: "";
        position: fixed;
        top: 0;
        bottom: 0;
        left: 250px;
        width: 1px;
        background: #e4e4e4;
        z-index: 20;
        pointer-events: none;
    }

    .wrapper.toggled::before {
        left: 70px;
    }

    .sidebar-wrapper .sidebar-header {
        height: 60px !important;
        bottom: auto !important;
        padding: 8px 12px !important;
        gap: 8px;
        align-items: center;
        border-bottom: 1px solid #e4e4e4;
        z-index: 13 !important;
    }

    .sidebar-wrapper .sidebar-header .logo-icon {
        height: 40px !important;
        width: auto !important;
        max-width: 160px !important;
        object-fit: contain;
        display: block;
    }

    .sidebar-wrapper .sidebar-header .toggle-icon {
        flex-shrink: 0;
        margin-left: 4px;
        line-height: 1;
    }

    .sidebar-wrapper .metismenu {
        margin-top: 60px !important;
        padding: 12px 10px 10px !important;
    }

    .sidebar-wrapper .metismenu > li:first-child {
        margin-top: 0 !important;
    }

    /* Header: simple bottom line, no shadow */
    .topbar {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        border-bottom: 1px solid #e4e4e4 !important;
        left: 250px !important;
        height: 60px !important;
        z-index: 1030 !important;
        overflow: visible !important;
    }

    .topbar .navbar {
        height: 60px !important;
        overflow: visible !important;
    }

    .wrapper.toggled .topbar {
        left: 70px !important;
    }

    /* Profile dropdown must be clickable and visible */
    .topbar .user-box {
        border-left: none !important;
        border-right: none !important;
        position: relative;
        z-index: 1031;
    }

    .topbar .shopora-profile-btn {
        border: 0;
        background: transparent;
        padding: 0;
        cursor: pointer;
        color: inherit;
        height: 60px;
    }

    .topbar .shopora-profile-btn .shopora-profile-caret {
        transition: transform 0.2s ease;
        color: #6c757d;
    }

    .topbar .shopora-profile-btn[aria-expanded="true"] .shopora-profile-caret {
        transform: rotate(180deg);
        color: #0d6efd;
    }

    .topbar .user-box .dropdown-menu {
        display: none;
        position: absolute !important;
        right: 0;
        left: auto;
        top: 100%;
        margin-top: 0;
        z-index: 1080 !important;
    }

    .topbar .user-box .dropdown-menu.show {
        display: block !important;
    }

    /* no bootstrap double caret */
    .topbar .shopora-profile-btn::after {
        display: none !important;
        content: none !important;
    }

    /* Smooth Reports dropdown */
    .sidebar-wrapper .metismenu > li.has-submenu {
        flex-direction: column;
        align-items: stretch;
    }

    .sidebar-wrapper .metismenu > li.has-submenu > a.has-arrow {
        position: relative;
        padding-right: 36px !important;
    }

    /* Hide broken/default arrow; use real chevron icon instead */
    .sidebar-wrapper .metismenu .has-arrow::after {
        display: none !important;
        content: none !important;
        border: none !important;
    }

    .sidebar-wrapper .metismenu .shopora-submenu-chevron {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: #6c757d;
        transition: transform 0.28s ease, color 0.2s ease;
        line-height: 1;
    }

    .sidebar-wrapper .metismenu > li.has-submenu.open > a.has-arrow .shopora-submenu-chevron {
        transform: translateY(-50%) rotate(90deg);
        color: #0d6efd;
    }

    .sidebar-wrapper .submenu.shopora-submenu {
        list-style: none;
        margin: 0 8px 4px 12px;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-4px);
        transition: max-height 0.32s ease, opacity 0.28s ease, transform 0.28s ease;
        border-left: 2px solid #e9ecef;
        background: transparent;
    }

    .sidebar-wrapper .submenu.shopora-submenu.active {
        max-height: 220px;
        opacity: 1;
        transform: translateY(0);
        padding: 6px 0 8px;
    }

    .sidebar-wrapper .submenu.shopora-submenu li {
        margin: 0 !important;
        display: block;
        width: 100%;
    }

    .sidebar-wrapper .submenu.shopora-submenu a {
        display: flex !important;
        align-items: center;
        gap: 8px;
        margin: 3px 0 3px 10px;
        padding: 8px 12px !important;
        border-radius: 8px;
        color: #5f6368 !important;
        font-size: 13.5px;
        line-height: 1.3;
        background: transparent;
        transition: background 0.2s ease, color 0.2s ease, padding-left 0.2s ease;
    }

    .sidebar-wrapper .submenu.shopora-submenu a i {
        font-size: 16px;
        color: #98a2b3;
        transition: color 0.2s ease;
    }

    .sidebar-wrapper .submenu.shopora-submenu a:hover,
    .sidebar-wrapper .submenu.shopora-submenu a:focus {
        background: #f0f6ff !important;
        color: #0d6efd !important;
        padding-left: 16px !important;
    }

    .sidebar-wrapper .submenu.shopora-submenu a:hover i,
    .sidebar-wrapper .submenu.shopora-submenu a:focus i {
        color: #0d6efd;
    }

    .sidebar-wrapper .submenu.shopora-submenu a.active {
        background: #e8f1ff !important;
        color: #0d6efd !important;
        font-weight: 500;
    }

    .sidebar-wrapper .submenu.shopora-submenu a.active i {
        color: #0d6efd;
    }
</style>
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('assets/images/shopora.png') }}"
                     class="logo-icon"
                     alt="Shopora">
            </a>
        </div>

        <div class="toggle-icon"><i class='bx bx-arrow-to-left'></i>
        </div>
    </div>
    <!--navigation-->
    <ul class="metismenu" id="menu">
        <li>
            <a href="{{route('admin.dashboard')}}">
                <div class="parent-icon"><i class='bx bx-home-circle'></i>
                </div>
                <div class="menu-title">Dashboard</div>
            </a>
        </li>

        @php
            $isReportsSection = request()->routeIs('admin.reports*');
        @endphp
        <li class="has-submenu {{ $isReportsSection ? 'open' : '' }}">
            <a href="javascript:;" class="has-arrow {{ $isReportsSection ? 'mm-active' : '' }}" onclick="toggleDropdown(this)">
                <div class="parent-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform:msFilter">
                        <path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path>
                    </svg>
                </div>
                <div class="menu-title">Reports</div>
                <i class="bx bx-chevron-right shopora-submenu-chevron"></i>
            </a>
            <ul class="submenu shopora-submenu {{ $isReportsSection ? 'active' : '' }}">
                <li>
                    <a href="{{route('admin.reports')}}" class="{{ request()->routeIs('admin.reports') && !request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>Purchase Report
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.reports.salesReport')}}" class="{{ request()->routeIs('admin.reports.salesReport') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>Sales Report
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.reports.inventoryReport')}}" class="{{ request()->routeIs('admin.reports.inventoryReport') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>Inventory Report
                    </a>
                </li>
            </ul>
        </li>

        <li>
            <a href="{{route('admin.inventoryItem')}}">
                <div class="parent-icon"><i class='bx bx-package'></i>
                </div>
                <div class="menu-title">Inventory Items</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.purchaseInventory')}}">
                <div class="parent-icon"><i class='bx bx-cart'></i>
                </div>
                <div class="menu-title">Purchase Inventory</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.purchaseInventory.storeRecords')}}">
                <div class="parent-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: msFilter">
                        <path d="M19 15v-3h-2v3h-3v2h3v3h2v-3h3v-2h-.937zM4 7h11v2H4zm0 4h11v2H4zm0 4h8v2H4z"></path>
                    </svg></i>
                </div>
                <div class="menu-title">Inventory Records</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.sales.index')}}">
                <div class="parent-icon"><i class='bx bxs-bar-chart-alt-2'></i>
                </div>
                <div class="menu-title">Sales</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.invoice.index')}}">
                <div class="parent-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform:msFilter">
                        <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 14.915V18h-2v-1.08c-2.339-.367-3-2.002-3-2.92h2c.011.143.159 1 2 1 1.38 0 2-.585 2-1 0-.324 0-1-2-1-3.48 0-4-1.88-4-3 0 1.288-1.029 2.584-3 2.915V6.012h2v1.109c1.734.41 2.4 1.853 2.4 2.879h-1l-1 .018C13.386 9.638 13.185 9 12 9c-1.299 0-2 .516-2 1 0 .374 0 1 2 1 3.48 0 4 1.88 4 3 0 1.288-1.029 2.584-3 2.915z"></path>
                    </svg></i>
                </div>
                <div class="menu-title">Invoice</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.customer')}}">
                <div class="parent-icon"><i class='bx bxs-group'></i>
                </div>
                <div class="menu-title">Customers</div>
            </a>
        </li>

        @php
            $isSettingsSection = request()->routeIs('admin.permission*', 'admin.role*', 'admin.user*');
        @endphp
        <li class="has-submenu {{ $isSettingsSection ? 'open' : '' }}">
            <a href="javascript:;" class="has-arrow {{ $isSettingsSection ? 'mm-active' : '' }}" onclick="toggleDropdown(this)">
                <div class="parent-icon"><i class="bx bx-cog"></i>
                </div>
                <div class="menu-title">Settings</div>
                <i class="bx bx-chevron-right shopora-submenu-chevron"></i>
            </a>
            <ul class="submenu shopora-submenu {{ $isSettingsSection ? 'active' : '' }}">
                <li>
                    <a href="{{ route('admin.permission') }}" class="{{ request()->routeIs('admin.permission*') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>Permission
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.role') }}" class="{{ request()->routeIs('admin.role*') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>Role
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.user') }}" class="{{ request()->routeIs('admin.user*') ? 'active' : '' }}">
                        <i class="bx bx-right-arrow-alt"></i>User
                    </a>
                </li>
            </ul>
        </li>
    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->
