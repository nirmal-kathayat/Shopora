<!--sidebar wrapper -->
<style>
    /* ===== Shopora sidebar (Figma-inspired) ===== */
    .sidebar-wrapper.shopora-sidebar {
        box-shadow: none !important;
        border-right: none !important;
        z-index: 12 !important;
        background: #ffffff !important;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .wrapper::before {
        content: "";
        position: fixed;
        top: 0;
        bottom: 0;
        left: 250px;
        width: 1px;
        background: #e4e4e4;
        /* Above topbar so the divider reaches absolute top again */
        z-index: 1040;
        pointer-events: none;
    }

    .wrapper.toggled::before {
        left: 70px;
    }

    .shopora-sidebar .sidebar-header {
        height: 64px !important;
        bottom: auto !important;
        padding: 12px 16px !important;
        gap: 8px;
        align-items: center;
        border-bottom: 1px solid #eef0f3;
        z-index: 13 !important;
        background: #fff;
        flex-shrink: 0;
    }

    .shopora-sidebar .sidebar-header .logo-icon {
        height: 36px !important;
        width: auto !important;
        max-width: 150px !important;
        object-fit: contain;
        display: block;
    }

    .shopora-sidebar .sidebar-header .toggle-icon {
        flex-shrink: 0;
        margin-left: 4px;
        line-height: 1;
        color: #008cff !important;
        font-size: 20px;
    }

    .shopora-sidebar .sidebar-nav-scroll {
        flex: 1 1 auto;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 12px 10px 8px;
        margin-top: 64px;
    }

    /* Override old metismenu top margin when using scroll container */
    .shopora-sidebar .metismenu {
        margin-top: 0 !important;
        padding: 0 !important;
        gap: 4px;
    }

    .shopora-sidebar .metismenu > li {
        margin: 0 !important;
        width: 100%;
    }

    .shopora-sidebar .metismenu > li:first-child {
        margin-top: 0 !important;
    }

    .shopora-sidebar .metismenu > li > a {
        position: relative;
        display: flex !important;
        align-items: center;
        gap: 10px;
        padding: 11px 14px 11px 16px !important;
        margin: 0 !important;
        border-radius: 10px !important;
        color: #374151 !important;
        font-size: 14.5px !important;
        font-weight: 500;
        letter-spacing: 0;
        background: transparent !important;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .shopora-sidebar .metismenu > li > a .parent-icon {
        width: 22px;
        font-size: 20px !important;
        line-height: 1;
        color: #4b5563;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .shopora-sidebar .metismenu > li > a .parent-icon i,
    .shopora-sidebar .metismenu > li > a .parent-icon svg {
        color: inherit !important;
        fill: currentColor !important;
    }

    .shopora-sidebar .metismenu > li > a .menu-title {
        margin-left: 0 !important;
        color: inherit !important;
    }

    .shopora-sidebar .metismenu > li > a:hover {
        background: #f3f6fb !important;
        color: #111827 !important;
    }

    .shopora-sidebar .metismenu > li > a:hover .parent-icon {
        color: #111827;
    }

    /* Active item — Figma: light blue pill + left blue bar */
    .shopora-sidebar .metismenu > li.active-link > a,
    .shopora-sidebar .metismenu > li > a.mm-active,
    .shopora-sidebar .metismenu > li.active-link > a:hover {
        background: #e8f1ff !important;
        color: #008cff !important;
    }

    .shopora-sidebar .metismenu > li.active-link > a .parent-icon,
    .shopora-sidebar .metismenu > li > a.mm-active .parent-icon {
        color: #008cff !important;
    }

    .shopora-sidebar .metismenu > li.active-link > a::before,
    .shopora-sidebar .metismenu > li.has-submenu.open > a.has-arrow::before {
        content: "";
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: #008cff;
    }

    .shopora-sidebar .metismenu > li.has-submenu.open > a.has-arrow {
        background: #e8f1ff !important;
        color: #008cff !important;
    }

    .shopora-sidebar .metismenu > li.has-submenu.open > a.has-arrow .parent-icon {
        color: #008cff !important;
    }

    /* Dropdowns keep working */
    .shopora-sidebar .metismenu > li.has-submenu {
        flex-direction: column;
        align-items: stretch;
    }

    .shopora-sidebar .metismenu > li.has-submenu > a.has-arrow {
        position: relative;
        padding-right: 36px !important;
    }

    .shopora-sidebar .metismenu .has-arrow::after {
        display: none !important;
        content: none !important;
        border: none !important;
    }

    .shopora-sidebar .metismenu .shopora-submenu-chevron {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #9ca3af;
        transition: transform 0.28s ease, color 0.2s ease;
        line-height: 1;
    }

    .shopora-sidebar .metismenu > li.has-submenu.open > a.has-arrow .shopora-submenu-chevron {
        transform: translateY(-50%) rotate(90deg);
        color: #008cff;
    }

    .shopora-sidebar .submenu.shopora-submenu {
        list-style: none;
        margin: 2px 6px 6px 14px;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transform: translateY(-4px);
        transition: max-height 0.32s ease, opacity 0.28s ease, transform 0.28s ease;
        border-left: 2px solid #e8eef5;
        background: transparent;
    }

    .shopora-sidebar .submenu.shopora-submenu.active {
        max-height: 220px;
        opacity: 1;
        transform: translateY(0);
        padding: 4px 0 6px;
    }

    .shopora-sidebar .submenu.shopora-submenu li {
        margin: 0 !important;
        display: block;
        width: 100%;
    }

    .shopora-sidebar .submenu.shopora-submenu a {
        display: flex !important;
        align-items: center;
        gap: 8px;
        margin: 2px 0 2px 8px;
        padding: 8px 12px !important;
        border-radius: 8px;
        color: #4b5563 !important;
        font-size: 13.5px;
        font-weight: 500;
        line-height: 1.3;
        background: transparent;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .shopora-sidebar .submenu.shopora-submenu a i {
        font-size: 16px;
        color: #9ca3af;
    }

    .shopora-sidebar .submenu.shopora-submenu a:hover,
    .shopora-sidebar .submenu.shopora-submenu a:focus {
        background: #f0f6ff !important;
        color: #008cff !important;
    }

    .shopora-sidebar .submenu.shopora-submenu a:hover i,
    .shopora-sidebar .submenu.shopora-submenu a:focus i,
    .shopora-sidebar .submenu.shopora-submenu a.active i {
        color: #008cff;
    }

    .shopora-sidebar .submenu.shopora-submenu a.active {
        background: #e8f1ff !important;
        color: #008cff !important;
        font-weight: 600;
    }

    /* Footer branding (Figma) */
    .shopora-sidebar .sidebar-footer {
        flex-shrink: 0;
        padding: 0;
        border-top: 1px solid #eef0f3;
        background: #fff;
    }

    .shopora-sidebar .collapse-btn {
        width: 100%;
        border: 0;
        background: transparent;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        border-radius: 0;
        color: #4b5563;
        font-size: 14.5px;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .shopora-sidebar .collapse-btn:hover {
        background: #f6f8fb;
        color: #008cff;
    }

    .shopora-sidebar .collapse-btn .collapse-ico {
        font-size: 22px;
        line-height: 1;
        flex-shrink: 0;
        transition: transform 0.2s ease;
    }

    /* Topbar stays aligned */
    .topbar {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        border-bottom: 1px solid #e4e4e4 !important;
        left: 250px !important;
        height: 64px !important;
        z-index: 1030 !important;
        overflow: visible !important;
    }

    .topbar .navbar {
        height: 64px !important;
        overflow: visible !important;
    }

    .wrapper.toggled .topbar {
        left: 70px !important;
    }

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
        height: 64px;
    }

    .topbar .shopora-profile-btn .shopora-profile-caret {
        transition: transform 0.2s ease;
        color: #6c757d;
    }

    .topbar .shopora-profile-btn[aria-expanded="true"] .shopora-profile-caret {
        transform: rotate(180deg);
        color: #008cff;
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

    .topbar .shopora-profile-btn::after {
        display: none !important;
        content: none !important;
    }

    .page-wrapper {
        margin-top: 64px !important;
    }

    /* mini logo shows only when the sidebar is collapsed */
    .shopora-sidebar .sidebar-header .logo-mini {
        display: none;
        height: 30px;
        width: 30px;
        object-fit: contain;
    }

    /* ===== Collapsed (toggled) sidebar — desktop only ===== */
    @media screen and (min-width: 1025px) {

        /* swap wide logo for the compact mark, centered */
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .sidebar-header .logo-icon {
            display: none;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .sidebar-header .logo-mini {
            display: block;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .sidebar-header {
            justify-content: center;
            padding: 12px 0 !important;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .sidebar-header .d-flex {
            flex: 0 0 auto !important;
            justify-content: center;
        }

        /* hide labels and the submenu chevron */
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .metismenu .menu-title,
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .shopora-submenu-chevron {
            display: none !important;
        }

        /* keep each row a centered icon */
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .metismenu > li > a {
            justify-content: center !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
            gap: 0 !important;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .metismenu > li > a .parent-icon {
            width: auto !important;
            margin: 0 !important;
        }

        /* never show open submenus while collapsed */
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .metismenu .submenu {
            display: none !important;
        }

        /* footer collapse button: icon only, flipped to point right (expand) */
        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .collapse-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            gap: 0;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .collapse-label {
            display: none !important;
        }

        .wrapper.toggled:not(.sidebar-hovered) .shopora-sidebar .collapse-btn .collapse-ico {
            transform: rotate(180deg);
        }
    }
</style>

@php
    $isDashboard = request()->routeIs('admin.dashboard*');
    $isReportsSection = request()->routeIs('admin.reports*');
    $isInventoryItems = request()->routeIs('admin.inventoryItem*');
    $isPurchase = request()->routeIs('admin.purchaseInventory')
        || request()->routeIs('admin.purchaseInventory.create')
        || request()->routeIs('admin.purchaseInventory.store')
        || request()->routeIs('admin.purchaseInventory.edit')
        || request()->routeIs('admin.purchaseInventory.update')
        || request()->routeIs('admin.purchaseInventory.delete')
        || request()->routeIs('admin.purchaseInventory.view');
    $isInventoryRecords = request()->routeIs('admin.purchaseInventory.storeRecords')
        || request()->routeIs('admin.purchaseInventory.viewRecord');
    $isSales = request()->routeIs('admin.sales*');
    $isInvoice = request()->routeIs('admin.invoice*');
    $isCustomers = request()->routeIs('admin.customer*');
    $isSettingsSection = request()->routeIs('admin.permission*', 'admin.role*', 'admin.user*');

    $showDashboard = auth()->guard(config('permission.guard'))->check();
    $showReports = canAccessAnyRoute([
        'admin.reports',
        'admin.reports.salesReport',
        'admin.reports.inventoryReport',
    ]);
    $showInventoryItems = canAccessRoute('admin.inventoryItem');
    $showPurchase = canAccessRoute('admin.purchaseInventory');
    $showInventoryRecords = canAccessRoute('admin.purchaseInventory.storeRecords');
    $showSales = canAccessRoute('admin.sales.index');
    $showInvoice = canAccessRoute('admin.invoice.index');
    $showCustomers = canAccessRoute('admin.customer');
    $showPermission = canAccessRoute('admin.permission');
    $showRole = canAccessRoute('admin.role');
    $showUser = canAccessRoute('admin.user');
    $showSettings = $showPermission || $showRole || $showUser;
@endphp

<div class="sidebar-wrapper shopora-sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center flex-grow-1" style="min-width: 0;">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ asset('assets/images/shopora.png') }}"
                     class="logo-icon"
                     alt="Shopora">
                <img src="{{ asset('assets/images/favicon-32x32.png') }}"
                     class="logo-mini"
                     alt="Shopora">
            </a>
        </div>
        <div class="toggle-icon"><i class='bx bx-arrow-to-left'></i></div>
    </div>

    <div class="sidebar-nav-scroll">
        <ul class="metismenu" id="menu">
            @if($showDashboard)
            <li class="{{ $isDashboard ? 'active-link' : '' }}">
                <a href="{{ route('admin.dashboard') }}">
                    <div class="parent-icon"><i class='bx bx-tachometer'></i></div>
                    <div class="menu-title">Dashboard</div>
                </a>
            </li>
            @endif

            @if($showReports)
            <li class="has-submenu {{ $isReportsSection ? 'open' : '' }}">
                <a href="javascript:;" class="has-arrow {{ $isReportsSection ? 'mm-active' : '' }}" onclick="toggleDropdown(this)">
                    <div class="parent-icon"><i class='bx bx-bar-chart-alt-2'></i></div>
                    <div class="menu-title">Reports</div>
                    <i class="bx bx-chevron-right shopora-submenu-chevron"></i>
                </a>
                <ul class="submenu shopora-submenu {{ $isReportsSection ? 'active' : '' }}">
                    @if(canAccessRoute('admin.reports'))
                    <li>
                        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') && !request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Purchase Report
                        </a>
                    </li>
                    @endif
                    @if(canAccessRoute('admin.reports.salesReport'))
                    <li>
                        <a href="{{ route('admin.reports.salesReport') }}" class="{{ request()->routeIs('admin.reports.salesReport') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Sales Report
                        </a>
                    </li>
                    @endif
                    @if(canAccessRoute('admin.reports.inventoryReport'))
                    <li>
                        <a href="{{ route('admin.reports.inventoryReport') }}" class="{{ request()->routeIs('admin.reports.inventoryReport') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Inventory Report
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            @if($showInventoryItems)
            <li class="{{ $isInventoryItems ? 'active-link' : '' }}">
                <a href="{{ route('admin.inventoryItem') }}">
                    <div class="parent-icon"><i class='bx bx-package'></i></div>
                    <div class="menu-title">Inventory Items</div>
                </a>
            </li>
            @endif

            @if($showPurchase)
            <li class="{{ $isPurchase ? 'active-link' : '' }}">
                <a href="{{ route('admin.purchaseInventory') }}">
                    <div class="parent-icon"><i class='bx bx-cart'></i></div>
                    <div class="menu-title">Purchase Inventory</div>
                </a>
            </li>
            @endif

            @if($showInventoryRecords)
            <li class="{{ $isInventoryRecords ? 'active-link' : '' }}">
                <a href="{{ route('admin.purchaseInventory.storeRecords') }}">
                    <div class="parent-icon"><i class='bx bx-list-plus'></i></div>
                    <div class="menu-title">Inventory Records</div>
                </a>
            </li>
            @endif

            @if($showSales)
            <li class="{{ $isSales ? 'active-link' : '' }}">
                <a href="{{ route('admin.sales.index') }}">
                    <div class="parent-icon"><i class='bx bx-shopping-bag'></i></div>
                    <div class="menu-title">Sales</div>
                </a>
            </li>
            @endif

            @if($showInvoice)
            <li class="{{ $isInvoice ? 'active-link' : '' }}">
                <a href="{{ route('admin.invoice.index') }}">
                    <div class="parent-icon"><i class='bx bx-receipt'></i></div>
                    <div class="menu-title">Invoice</div>
                </a>
            </li>
            @endif

            @if($showCustomers)
            <li class="{{ $isCustomers ? 'active-link' : '' }}">
                <a href="{{ route('admin.customer') }}">
                    <div class="parent-icon"><i class='bx bx-user'></i></div>
                    <div class="menu-title">Customers</div>
                </a>
            </li>
            @endif

            @if($showSettings)
            <li class="has-submenu {{ $isSettingsSection ? 'open' : '' }}">
                <a href="javascript:;" class="has-arrow {{ $isSettingsSection ? 'mm-active' : '' }}" onclick="toggleDropdown(this)">
                    <div class="parent-icon"><i class="bx bx-cog"></i></div>
                    <div class="menu-title">Settings</div>
                    <i class="bx bx-chevron-right shopora-submenu-chevron"></i>
                </a>
                <ul class="submenu shopora-submenu {{ $isSettingsSection ? 'active' : '' }}">
                    @if($showPermission)
                    <li>
                        <a href="{{ route('admin.permission') }}" class="{{ request()->routeIs('admin.permission*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Permission
                        </a>
                    </li>
                    @endif
                    @if($showRole)
                    <li>
                        <a href="{{ route('admin.role') }}" class="{{ request()->routeIs('admin.role*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>Role
                        </a>
                    </li>
                    @endif
                    @if($showUser)
                    <li>
                        <a href="{{ route('admin.user') }}" class="{{ request()->routeIs('admin.user*') ? 'active' : '' }}">
                            <i class="bx bx-right-arrow-alt"></i>User
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif
        </ul>
    </div>

    <div class="sidebar-footer">
        <button type="button" class="collapse-btn toggle-icon" aria-label="Collapse sidebar">
            <i class='bx bx-chevrons-left collapse-ico'></i>
            <span class="collapse-label">Collapse</span>
        </button>
    </div>
</div>
<!--end sidebar wrapper -->
