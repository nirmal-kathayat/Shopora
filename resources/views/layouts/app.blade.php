<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="icon" href="{{ asset('assets/images/shopora.png') }}" type="image/png" />
    <!--plugins-->
    @yield("style")
    @stack('style')
    <link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
    <link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
    <!-- <link href="{{asset('assets/plugins/metisMenu/css/metisMenu.min.css')}}" rel="stylesheet" /> -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Bootstrap CSS -->
    <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/theme-plugin.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/plugins/sweetalert2/sweetalert2.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}" />
    <title>Shopora</title>
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">
        <!--start header -->
        @include("layouts.header")
        <!--end header -->
        <!--navigation-->
        @include("layouts.nav")
        <!--end navigation-->
        <!--start page wrapper -->
        @yield("wrapper")
        <!--end page wrapper -->
        <!--start overlay-->
        <div class="overlay toggle-icon"></div>
        <!--end overlay-->
        <!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
        <!--End Back To Top Button-->
        <footer class="page-footer">
            <p class="mb-0">Copyright © {{ date("Y") }}. All right reserved.</p>
        </footer>
    </div>
    <!--end wrapper-->
    <!--start switcher-->
    <div class="switcher-wrapper">
    </div>

    </div>
    <!--end switcher-->
    <!--plugins-->
    <script src="{{asset('assets/js/jquery.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- <script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script> -->
    <!--app JS-->
    <script src="{{asset('assets/js/app.js')}}"></script>
    <script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
    <script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
    <script src="{{asset('assets/plugins/sweetalert2/sweetalert2.all.min.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function toggleDropdown(element) {
            const submenu = element.nextElementSibling;
            const parent = element.closest('li.has-submenu');
            if (!submenu) return;

            const isOpen = submenu.classList.contains('active');

            document.querySelectorAll('#menu .submenu.shopora-submenu.active').forEach(function (el) {
                if (el !== submenu) {
                    el.classList.remove('active');
                    const li = el.closest('li.has-submenu');
                    if (li) li.classList.remove('open');
                }
            });

            submenu.classList.toggle('active', !isOpen);
            if (parent) parent.classList.toggle('open', !isOpen);
        }

        // Header profile dropdown (custom — Bootstrap data-api was not toggling on click)
        document.addEventListener('DOMContentLoaded', function () {
            var trigger = document.getElementById('shoporaProfileDropdown');
            var menu = document.getElementById('shoporaProfileMenu');
            if (!trigger || !menu) return;

            function closeMenu() {
                menu.classList.remove('show');
                trigger.setAttribute('aria-expanded', 'false');
            }

            function openMenu() {
                menu.classList.add('show');
                trigger.setAttribute('aria-expanded', 'true');
            }

            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (menu.classList.contains('show')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            document.addEventListener('click', function (e) {
                if (!menu.classList.contains('show')) return;
                if (trigger.contains(e.target) || menu.contains(e.target)) return;
                closeMenu();
            });
        });
    </script>
    @include('scripts.message')
    @stack('scripts')
    @stack('script')
    @yield("script")
</body>

</html>