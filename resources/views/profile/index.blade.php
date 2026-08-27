@extends("layouts.app")

@section("style")
<style>
    .shopora-profile-page {
        max-width: none;
        width: 100%;
    }

    .shopora-profile-crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 13px;
        color: #9ca3af;
        flex-wrap: wrap;
    }

    .shopora-profile-crumb a {
        color: #008cff;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .shopora-profile-crumb a:hover {
        color: #0077db;
    }

    .shopora-profile-crumb .sep {
        color: #d1d5db;
    }

    .shopora-profile-crumb .current {
        color: #6b7280;
    }

    .shopora-profile-heading {
        margin: 0 0 18px;
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .shopora-profile-hero,
    .shopora-profile-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        box-shadow: none !important;
    }

    .shopora-profile-hero {
        padding: 22px 24px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }

    .shopora-avatar-wrap {
        position: relative;
        flex-shrink: 0;
    }

    .shopora-profile-avatar {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e8f1ff;
        background: #f8fafc;
        display: block;
    }

    .shopora-avatar-cam {
        position: absolute;
        right: 0;
        bottom: 2px;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 2px solid #fff;
        background: #008cff;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        padding: 0;
        cursor: default;
        line-height: 1;
    }

    .shopora-profile-hero-meta h2 {
        margin: 0 0 6px;
        font-size: 1.35rem;
        font-weight: 700;
        color: #111827;
        line-height: 1.2;
    }

    .shopora-profile-hero-meta .sub {
        margin: 0;
        font-size: 13px;
        color: #6b7280;
    }

    .shopora-role-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 12px;
    }

    .shopora-role-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 12px;
        border-radius: 999px;
        background: #e8f1ff;
        color: #008cff;
        font-size: 12px;
        font-weight: 600;
    }

    .shopora-profile-panels .shopora-profile-card {
        display: flex;
        flex-direction: column;
        padding: 18px 18px 16px;
        height: 100%;
    }

    .shopora-profile-panels form {
        display: flex;
        flex-direction: column;
        flex: 1 1 auto;
    }

    .shopora-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 16px;
        font-size: 15px;
        font-weight: 700;
        color: #111827;
    }

    .shopora-card-title .ico {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: #e8f1ff;
        color: #008cff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .shopora-overview-row {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #eef0f3;
    }

    .shopora-overview-row:first-of-type {
        padding-top: 0;
    }

    .shopora-overview-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .shopora-overview-row .label {
        font-size: 13px;
        font-weight: 500;
        color: #9ca3af;
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .shopora-overview-row .value {
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        text-align: right;
        word-break: break-word;
        min-width: 0;
    }

    .shopora-form-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 6px;
        margin-bottom: 12px;
    }

    .shopora-form-row label {
        margin: 0;
        font-size: 12px;
        font-weight: 500;
        color: #6b7280;
    }

    .shopora-form-row .field {
        min-width: 0;
    }

    .shopora-profile-card .form-control {
        border: 1px solid #d1d5db;
        border-radius: 8px;
        height: 40px;
        font-size: 13px;
        color: #1f2937;
        box-shadow: none !important;
        background: #fff;
    }

    .shopora-profile-card .form-control:focus {
        border-color: #008cff;
        box-shadow: 0 0 0 3px rgba(0, 140, 255, 0.12) !important;
    }

    .shopora-profile-card .form-control.is-invalid {
        border-color: #dc2626;
    }

    .shopora-pass-wrap {
        position: relative;
    }

    .shopora-pass-wrap .form-control {
        padding-right: 42px;
    }

    .shopora-pass-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: #9ca3af;
        font-size: 18px;
        padding: 0;
        line-height: 1;
        cursor: pointer;
    }

    .shopora-pass-toggle:hover {
        color: #008cff;
    }

    .shopora-field-error {
        margin-top: 4px;
        font-size: 12px;
        color: #dc2626;
    }

    .shopora-profile-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto;
        padding-top: 12px;
    }

    .shopora-btn-primary {
        height: 38px;
        padding: 0 14px;
        border: 0;
        border-radius: 8px;
        background: #008cff;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
    }

    .shopora-btn-primary:hover {
        background: #0077db;
        color: #fff;
    }

    .shopora-btn-ghost {
        height: 38px;
        padding: 0 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        color: #374151;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .shopora-btn-ghost:hover {
        border-color: #008cff;
        color: #008cff;
        text-decoration: none;
    }

    @media (max-width: 991px) {
        .shopora-profile-panels .shopora-profile-card {
            margin-bottom: 0;
        }
    }
</style>
@endsection

@section("wrapper")
@php
    $roleNames = $user->roles->pluck('name')->filter()->values();
    $memberSince = $user->created_at
        ? \Carbon\Carbon::parse($user->created_at)->format('d M Y')
        : '—';
@endphp
<div class="page-wrapper">
    <div class="page-content shopora-profile-page">
        <nav class="shopora-profile-crumb" aria-label="breadcrumb">
            <a href="{{ route('admin.dashboard') }}" title="Home"><i class="bx bx-home-alt"></i></a>
            <span class="sep">›</span>
            <span>Profile</span>
            <span class="sep">›</span>
            <span class="current">My Profile</span>
        </nav>

        <h1 class="shopora-profile-heading">My Profile</h1>

        <div class="shopora-profile-hero">
            <div class="shopora-avatar-wrap">
                <img src="{{ asset('assets/images/avatars/user-img.png') }}" alt="Profile" class="shopora-profile-avatar" id="profileAvatarPreview">
                <button type="button" class="shopora-avatar-cam" title="Profile photo upload coming soon" aria-label="Change photo">
                    <i class="bx bx-camera"></i>
                </button>
            </div>
            <div class="shopora-profile-hero-meta">
                <h2 id="profileDisplayName">{{ $user->name }}</h2>
                <p class="sub">{{ '@' . $user->username }} · {{ $user->email }}</p>
                <div class="shopora-role-badges">
                    @forelse($roleNames as $roleName)
                        <span class="shopora-role-badge">{{ $roleName }}</span>
                    @empty
                        <span class="shopora-role-badge">No role assigned</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row g-3 align-items-stretch shopora-profile-panels">
            <div class="col-12 col-lg-4">
                <div class="shopora-profile-card">
                    <h3 class="shopora-card-title">
                        <span class="ico"><i class="bx bx-user"></i></span>
                        Account Overview
                    </h3>

                    <div class="shopora-overview-row">
                        <span class="label">Full name</span>
                        <span class="value">{{ $user->name }}</span>
                    </div>
                    <div class="shopora-overview-row">
                        <span class="label">Username</span>
                        <span class="value">{{ '@' . $user->username }}</span>
                    </div>
                    <div class="shopora-overview-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $user->email }}</span>
                    </div>
                    <div class="shopora-overview-row">
                        <span class="label">Role</span>
                        <span class="value">{{ $roleNames->isNotEmpty() ? $roleNames->implode(', ') : '—' }}</span>
                    </div>
                    <div class="shopora-overview-row">
                        <span class="label">Member since</span>
                        <span class="value">{{ $memberSince }}</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="shopora-profile-card">
                    <h3 class="shopora-card-title">
                        <span class="ico"><i class="bx bx-edit-alt"></i></span>
                        Edit Profile
                    </h3>

                    <form method="POST" action="{{ route('admin.profile.update') }}" id="profileEditForm">
                        @csrf
                        <div class="shopora-form-row">
                            <label for="name">Full name</label>
                            <div class="field">
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required autocomplete="name">
                                @error('name')<div class="shopora-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="shopora-form-row">
                            <label for="username">Username</label>
                            <div class="field">
                                <input type="text" id="username" name="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}" required autocomplete="username">
                                @error('username')<div class="shopora-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="shopora-form-row">
                            <label for="email">Email</label>
                            <div class="field">
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required autocomplete="email">
                                @error('email')<div class="shopora-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="shopora-profile-actions">
                            <a href="{{ route('admin.profile') }}" class="shopora-btn-ghost">Cancel</a>
                            <button type="submit" class="shopora-btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="shopora-profile-card">
                    <h3 class="shopora-card-title">
                        <span class="ico"><i class="bx bx-lock-alt"></i></span>
                        Change Password
                    </h3>

                    <form method="POST" action="{{ route('admin.profile.password') }}" id="profilePasswordForm">
                        @csrf
                        <div class="shopora-form-row">
                            <label for="current_password">Current password</label>
                            <div class="field">
                                <div class="shopora-pass-wrap">
                                    <input type="password" id="current_password" name="current_password"
                                           class="form-control @error('current_password') is-invalid @enderror"
                                           placeholder="Enter current password" required autocomplete="current-password">
                                    <button type="button" class="shopora-pass-toggle" data-target="current_password" aria-label="Show password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                                @error('current_password')<div class="shopora-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="shopora-form-row">
                            <label for="new_password">New password</label>
                            <div class="field">
                                <div class="shopora-pass-wrap">
                                    <input type="password" id="new_password" name="new_password"
                                           class="form-control @error('new_password') is-invalid @enderror"
                                           placeholder="Enter new password" required autocomplete="new-password" minlength="6">
                                    <button type="button" class="shopora-pass-toggle" data-target="new_password" aria-label="Show password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                                @error('new_password')<div class="shopora-field-error">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="shopora-form-row">
                            <label for="new_password_confirmation">Confirm new password</label>
                            <div class="field">
                                <div class="shopora-pass-wrap">
                                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                           class="form-control"
                                           placeholder="Confirm new password" required autocomplete="new-password" minlength="6">
                                    <button type="button" class="shopora-pass-toggle" data-target="new_password_confirmation" aria-label="Show password">
                                        <i class="bx bx-hide"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="shopora-profile-actions">
                            <button type="submit" class="shopora-btn-primary">Update password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section("script")
<script>
    document.querySelectorAll('.shopora-pass-toggle').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(btn.getAttribute('data-target'));
            if (!input) return;
            var icon = btn.querySelector('i');
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            if (icon) {
                icon.className = show ? 'bx bx-show' : 'bx bx-hide';
            }
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });
</script>
@endsection
