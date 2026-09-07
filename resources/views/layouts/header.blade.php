<!--start header -->
<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand gap-3">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>

            <div class="top-menu ms-auto">
                <ul class="navbar-nav align-items-center gap-1">
                    @php($bellUnread = \Auth::guard('admin')->user()?->unreadNotifications()->count() ?? 0)
                    <li class="nav-item shopora-bell" id="shoporaBell">
                        <button type="button"
                                class="shopora-bell-btn"
                                id="shoporaBellBtn"
                                aria-expanded="false"
                                aria-haspopup="true"
                                aria-label="Notifications">
                            <i class='bx bx-bell'></i>
                            {{-- rendered server-side so the count is right on the
                                 first paint, before the poll has run once --}}
                            <span class="shopora-bell-count"
                                  id="shoporaBellCount"
                                  @if(! $bellUnread) hidden @endif>{{ $bellUnread > 9 ? '9+' : $bellUnread }}</span>
                        </button>

                        <div class="shopora-bell-panel" id="shoporaBellPanel" hidden>
                            <div class="shopora-bell-head">
                                <h6>Notifications</h6>
                                <button type="button"
                                        class="shopora-bell-readall"
                                        id="shoporaBellReadAll"
                                        @if(! $bellUnread) disabled @endif>Mark all read</button>
                            </div>
                            <ul class="shopora-bell-list" id="shoporaBellList"></ul>
                            <button type="button" class="shopora-bell-more" id="shoporaBellMore" hidden>Load more</button>
                        </div>
                    </li>

                    {{-- Dark mode toggle - off for now. The handlers in app.js
                         and on the dashboard bind to .dark-mode, so they simply
                         never fire; put this back to turn it on again. --}}
                    {{--
                    <li class="nav-item dark-mode d-none d-sm-flex">
                        <a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
                        </a>
                    </li>
                    --}}
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

<script>
    window.SHOPORA_BELL = {
        index: "{{ route('admin.notifications') }}",
        unreadCount: "{{ route('admin.notifications.unreadCount') }}",
        read: "{{ route('admin.notifications.read', ['id' => ':id']) }}",
        readAll: "{{ route('admin.notifications.readAll') }}",
        destroy: "{{ route('admin.notifications.destroy', ['id' => ':id']) }}",
        unread: {{ $bellUnread }},
    };
</script>
