<!--sidebar wrapper -->
<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <img src="{{asset('assets/images/logo.png')}}" class="logo-icon" alt="logo icon">
        </div>

        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
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

        <li class="menu-label">Inventory Elements</li>
        <li>
            <a href="javascript:;" class="has-arrow" onclick="toggleDropdown(this)">
                <div class="parent-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform:msFilter">
                        <path d="m20 8-6-6H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM9 19H7v-9h2v9zm4 0h-2v-6h2v6zm4 0h-2v-3h2v3zM14 9h-1V4l5 5h-4z"></path>
                    </svg>
                </div>
                <div class="menu-title">Reports</div>
            </a>
            <ul class="submenu">
                <li>
                    <a href="{{route('admin.reports')}}">
                        <i class="bx bx-right-arrow-alt"></i>Purchase Report
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.reports.salesReport')}}">
                        <i class="bx bx-right-arrow-alt"></i>Sales Report
                    </a>
                </li>
                <li>
                    <a href="{{route('admin.reports.inventoryReport')}}">
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
                        <path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm1 14.915V18h-2v-1.08c-2.339-.367-3-2.002-3-2.92h2c.011.143.159 1 2 1 1.38 0 2-.585 2-1 0-.324 0-1-2-1-3.48 0-4-1.88-4-3 0-1.288 1.029-2.584 3-2.915V6.012h2v1.109c1.734.41 2.4 1.853 2.4 2.879h-1l-1 .018C13.386 9.638 13.185 9 12 9c-1.299 0-2 .516-2 1 0 .374 0 1 2 1 3.48 0 4 1.88 4 3 0 1.288-1.029 2.584-3 2.915z"></path>
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
        <li>
            <a href="{{route('admin.permission')}}" target="">
                <div class="parent-icon"><i class="bx bx-user-circle"></i>
                </div>
                <div class="menu-title">Permission</div>
            </a>
        </li>
        <li>
            <a href="{{route('admin.role')}}" target="">
                <div class="parent-icon"><i class="bx bx-user-circle"></i>
                </div>
                <div class="menu-title">Role</div>
            </a>
        </li>

        <li>
            <a href="{{route('admin.user')}}" target="">
                <div class="parent-icon"><i class="bx bx-user-circle"></i>
                </div>
                <div class="menu-title">User</div>
            </a>
        </li>
        <!-- logout -->
        <li>
            <a href="{{route('logout')}}" target="">
                <div class="parent-icon"><i class="bx bx-lock"></i>
                </div>
                <div class="menu-title">Logout</div>
            </a>
        </li>
    </ul>
    <!--end navigation-->
</div>
<!--end sidebar wrapper -->