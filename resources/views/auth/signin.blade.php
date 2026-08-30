<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('assets/images/shopora.png') }}" type="image/png" />
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <title>Shopora Login</title>
    <style>
        :root {
            --brand: #2563eb;
            --brand-strong: #1d4ed8;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
            --field-bg: #f8fafc;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Poppins', system-ui, -apple-system, sans-serif;
            color: var(--ink);
            background: linear-gradient(180deg, #eaf1fc 0%, #eef4fd 40%, #f4f8fe 100%);
            position: relative;
            overflow-x: hidden;
        }

        /* ---------- decorative background layers ---------- */
        .bg-decor {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .bg-decor .dots {
            position: absolute;
            top: 34px;
            left: 34px;
            display: grid;
            grid-template-columns: repeat(9, 10px);
            gap: 12px;
            opacity: .55;
        }

        .bg-decor .dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #b9cdf0;
        }

        .bg-decor .chart {
            position: absolute;
            top: 60px;
            right: 60px;
            color: #c5d8f4;
            opacity: .8;
        }

        .bg-decor .shelf-left {
            position: absolute;
            left: 0;
            bottom: 90px;
            width: 300px;
            max-width: 22vw;
            opacity: .9;
        }

        .bg-decor .shelf-right {
            position: absolute;
            right: 0;
            bottom: 90px;
            width: 300px;
            max-width: 22vw;
            opacity: .9;
        }

        .bg-decor .wave {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
        }

        @media (max-width: 991px) {

            .bg-decor .shelf-left,
            .bg-decor .shelf-right,
            .bg-decor .chart,
            .bg-decor .dots {
                display: none;
            }
        }

        /* ---------- layout ---------- */
        .page {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px 24px;
        }

        .brand-logo {
            height: 84px;
            width: auto;
            max-width: 320px;
            object-fit: contain;
            margin-bottom: 26px;
        }

        .login-card {
            width: 100%;
            max-width: 620px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e6ebf2;
            padding: 40px 52px 44px;
        }

        .shield {
            width: 62px;
            height: 62px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #e8f0fe;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--brand);
        }

        .login-card h1 {
            text-align: center;
            font-size: 30px;
            font-weight: 700;
            margin: 0 0 8px;
            color: #1e293b;
        }

        .login-card .sub {
            text-align: center;
            color: var(--muted);
            font-size: 15px;
            margin: 0 0 30px;
        }

        .field {
            margin-bottom: 20px;
        }

        .field > label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrap .lead-icon {
            position: absolute;
            left: 16px;
            color: #9aa6b8;
            font-size: 19px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            height: 54px;
            border: 1px solid var(--line);
            background: var(--field-bg);
            border-radius: 12px;
            padding: 0 48px;
            font-size: 15px;
            font-family: inherit;
            color: var(--ink);
            transition: border-color .18s, box-shadow .18s, background .18s;
        }

        .input-wrap input::placeholder {
            color: #9aa6b8;
        }

        .input-wrap input:focus {
            outline: none;
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
        }

        .input-wrap .toggle-eye {
            position: absolute;
            right: 8px;
            width: 38px;
            height: 38px;
            border: 0;
            background: transparent;
            color: #94a3b8;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
        }

        .input-wrap .toggle-eye:hover {
            color: var(--brand);
        }

        .validation-error {
            color: #dc2626;
            font-size: 13px;
            margin: 7px 2px 0;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 6px 0 22px;
            font-size: 14.5px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 18px;
            height: 18px;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .btn-signin {
            width: 100%;
            height: 54px;
            border: 0;
            border-radius: 12px;
            background: var(--brand);
            color: #fff;
            font-size: 16px;
            font-weight: 600;
            font-family: inherit;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            transition: background .18s, transform .05s;
            box-shadow: 0 12px 22px -10px rgba(37, 99, 235, .7);
        }

        .btn-signin:hover {
            background: var(--brand-strong);
        }

        .btn-signin:active {
            transform: translateY(1px);
        }

        .btn-signin i {
            font-size: 19px;
        }

        .alert-failed {
            display: flex;
            align-items: center;
            gap: 9px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 14px;
            margin-bottom: 22px;
        }

        /* ---------- feature strip ---------- */
        .features {
            width: 100%;
            max-width: 900px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
            margin: 34px auto 0;
        }

        .feature {
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 6px 4px;
        }

        .feature + .feature {
            border-left: 1px solid #dbe6f7;
            padding-left: 22px;
        }

        .feature .f-icon {
            flex-shrink: 0;
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background: #e8f0fe;
            color: var(--brand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .feature .f-title {
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.25;
        }

        .feature .f-desc {
            font-size: 13px;
            color: var(--muted);
            line-height: 1.3;
        }

        .footer-copy {
            text-align: center;
            color: #8a94a6;
            font-size: 13.5px;
            margin-top: 40px;
        }

        @media (max-width: 640px) {
            .login-card {
                padding: 30px 22px 32px;
                border-radius: 12px;
            }

            .login-card h1 {
                font-size: 25px;
            }

            .brand-logo {
                height: 64px;
            }

            .features {
                grid-template-columns: 1fr;
                gap: 14px;
                max-width: 620px;
            }

            .feature + .feature {
                border-left: 0;
                padding-left: 4px;
                border-top: 1px solid #dbe6f7;
                padding-top: 14px;
            }
        }
    </style>
</head>

<body>
    <!-- decorative background -->
    <div class="bg-decor" aria-hidden="true">
        <div class="dots">
            @for ($i = 0; $i < 45; $i++)<span></span>@endfor
        </div>
        <svg class="chart" width="150" height="90" viewBox="0 0 150 90" fill="none">
            <rect x="6" y="60" width="14" height="26" rx="3" fill="currentColor" opacity=".6" />
            <rect x="30" y="46" width="14" height="40" rx="3" fill="currentColor" opacity=".7" />
            <rect x="54" y="32" width="14" height="54" rx="3" fill="currentColor" opacity=".85" />
            <path d="M8 54 L40 40 L66 24 L100 30 L142 6" stroke="currentColor" stroke-width="3"
                stroke-linecap="round" stroke-linejoin="round" fill="none" />
            <path d="M132 6 H142 V16" stroke="currentColor" stroke-width="3" stroke-linecap="round"
                stroke-linejoin="round" fill="none" />
        </svg>

        <!-- left shelves + POS -->
        <svg class="shelf-left" viewBox="0 0 300 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g fill="#cbdcf5" opacity="0.9">
                <rect x="0" y="10" width="180" height="300" rx="6" fill="#dbe7fa" />
                <rect x="10" y="20" width="160" height="8" rx="4" />
                <rect x="10" y="110" width="160" height="8" rx="4" />
                <rect x="10" y="200" width="160" height="8" rx="4" />
                <g fill="#b7ccf0">
                    <rect x="22" y="42" width="18" height="60" rx="3" />
                    <rect x="48" y="52" width="18" height="50" rx="3" />
                    <rect x="74" y="40" width="18" height="62" rx="3" />
                    <rect x="100" y="54" width="18" height="48" rx="3" />
                    <rect x="126" y="44" width="18" height="58" rx="3" />
                    <rect x="30" y="134" width="18" height="60" rx="3" />
                    <rect x="56" y="144" width="18" height="50" rx="3" />
                    <rect x="82" y="132" width="18" height="62" rx="3" />
                    <rect x="108" y="146" width="18" height="48" rx="3" />
                    <rect x="134" y="136" width="18" height="58" rx="3" />
                </g>
            </g>
            <!-- POS terminal -->
            <g>
                <rect x="120" y="196" width="96" height="66" rx="8" fill="#7ea6ea" />
                <rect x="130" y="205" width="76" height="48" rx="5" fill="#e8f0fe" />
                <rect x="150" y="262" width="36" height="10" rx="3" fill="#5b86d6" />
                <rect x="132" y="272" width="72" height="14" rx="4" fill="#7ea6ea" />
                <rect x="222" y="228" width="10" height="40" rx="4" fill="#5b86d6" />
                <circle cx="227" cy="224" r="9" fill="#5b86d6" />
            </g>
        </svg>

        <!-- right shelves + basket -->
        <svg class="shelf-right" viewBox="0 0 300 320" fill="none" xmlns="http://www.w3.org/2000/svg">
            <g opacity="0.9">
                <rect x="120" y="10" width="180" height="300" rx="6" fill="#dbe7fa" />
                <rect x="130" y="20" width="160" height="8" rx="4" fill="#cbdcf5" />
                <rect x="130" y="110" width="160" height="8" rx="4" fill="#cbdcf5" />
                <rect x="130" y="200" width="160" height="8" rx="4" fill="#cbdcf5" />
                <g fill="#b7ccf0">
                    <rect x="142" y="42" width="18" height="60" rx="3" />
                    <rect x="168" y="52" width="18" height="50" rx="3" />
                    <rect x="194" y="40" width="18" height="62" rx="3" />
                    <rect x="220" y="54" width="18" height="48" rx="3" />
                    <rect x="246" y="44" width="18" height="58" rx="3" />
                    <rect x="150" y="134" width="18" height="60" rx="3" />
                    <rect x="176" y="144" width="18" height="50" rx="3" />
                    <rect x="202" y="132" width="18" height="62" rx="3" />
                    <rect x="228" y="146" width="18" height="48" rx="3" />
                </g>
            </g>
            <!-- shopping basket -->
            <g>
                <path d="M40 232 L120 232 L110 300 L50 300 Z" fill="#6a97e6" />
                <path d="M40 232 L120 232 L117 246 L43 246 Z" fill="#5b86d6" />
                <path d="M58 232 V214 a22 22 0 0 1 44 0 V232" stroke="#6a97e6" stroke-width="9"
                    fill="none" stroke-linecap="round" />
                <g stroke="#bcd2f4" stroke-width="4" opacity=".8">
                    <line x1="60" y1="252" x2="56" y2="292" />
                    <line x1="76" y1="252" x2="74" y2="292" />
                    <line x1="92" y1="252" x2="92" y2="292" />
                    <line x1="108" y1="252" x2="104" y2="292" />
                </g>
            </g>
        </svg>

        <svg class="wave" viewBox="0 0 1440 220" preserveAspectRatio="none" fill="none">
            <path d="M0 120 C240 60 420 180 720 130 C1020 80 1200 170 1440 110 V220 H0 Z" fill="#d6e4fb"
                opacity=".7" />
            <path d="M0 160 C260 110 460 210 760 165 C1060 120 1220 200 1440 150 V220 H0 Z" fill="#c3d7f7"
                opacity=".8" />
        </svg>
    </div>

    <div class="page">
        <img class="brand-logo" src="{{ asset('assets/images/shopora.png') }}" alt="Shopora" />

        <div class="login-card">
            <div class="shield">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2 L20 5 V11 C20 16 16.5 19.5 12 21 C7.5 19.5 4 16 4 11 V5 Z"
                        fill="currentColor" opacity=".18" />
                    <path d="M12 2 L20 5 V11 C20 16 16.5 19.5 12 21 C7.5 19.5 4 16 4 11 V5 Z"
                        stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" />
                    <path d="M8.6 11.4 L11 13.8 L15.4 9" stroke="currentColor" stroke-width="1.9"
                        stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <h1>Welcome back!</h1>
            <p class="sub">Sign in to access your Shopora inventory &amp; POS system</p>

            <form action="{{ route('loginProcess') }}" method="post" id="loginForm">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <i class="lead-icon bx bxs-user"></i>
                        <input type="text" name="username" id="username" value="{{ old('username') }}"
                            placeholder="Username" autocomplete="username" autofocus>
                    </div>
                    @error('username')
                        <p class="validation-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap" id="pwWrap">
                        <i class="lead-icon bx bxs-lock-alt"></i>
                        <input type="password" name="password" id="password" placeholder="Password"
                            autocomplete="current-password">
                        <button type="button" class="toggle-eye" id="togglePw" aria-label="Show password">
                            <i class="bx bx-show"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="validation-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="remember">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Remember me
                </label>

                <button type="submit" class="btn-signin" id="signinBtn">
                    <i class="bx bxs-lock-open"></i> <span>Sign in</span>
                </button>
            </form>
        </div>

        <div class="features">
            <div class="feature">
                <div class="f-icon"><i class="bx bxs-bar-chart-alt-2"></i></div>
                <div>
                    <div class="f-title">Make selling easy</div>
                    <div class="f-desc">Fast billing &amp; reports</div>
                </div>
            </div>
            <div class="feature">
                <div class="f-icon"><i class="bx bxs-package"></i></div>
                <div>
                    <div class="f-title">Full stock control</div>
                    <div class="f-desc">Track &amp; manage inventory</div>
                </div>
            </div>
            <div class="feature">
                <div class="f-icon"><i class="bx bxs-shield-alt-2"></i></div>
                <div>
                    <div class="f-title">Secure system</div>
                    <div class="f-desc">Your data is protected</div>
                </div>
            </div>
        </div>

        <p class="footer-copy">© {{ date('Y') }} Shopora. All rights reserved.</p>
    </div>

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script>
        (function () {
            var toggle = document.getElementById('togglePw');
            var pw = document.getElementById('password');
            var icon = toggle ? toggle.querySelector('i') : null;

            if (toggle && pw && icon) {
                toggle.addEventListener('click', function () {
                    var show = pw.type === 'password';
                    pw.type = show ? 'text' : 'password';
                    icon.classList.toggle('bx-show', !show);
                    icon.classList.toggle('bx-hide', show);
                    toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
                });
            }

            var form = document.getElementById('loginForm');
            var btn = document.getElementById('signinBtn');
            if (form && btn) {
                form.addEventListener('submit', function () {
                    btn.disabled = true;
                    btn.style.opacity = '.85';
                    btn.querySelector('span').textContent = 'Signing in…';
                });
            }
        })();
    </script>
</body>

</html>
