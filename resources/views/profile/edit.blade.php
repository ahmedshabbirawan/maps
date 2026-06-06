@extends('layouts.app')

@section('title', 'Profile')

@section('breadcrumb')
    <li><a href="{{ route('collections.index') }}">Dashboard</a></li>
    <li class="separator">/</li>
    <li>Profile</li>
@endsection

@section('page-title', 'Profile')
@section('page-subtitle', 'Manage your account')

@section('content')
@php
    $activeTab = request('tab', 'account');
    if ($errors->hasAny(['current_password', 'password', 'password_confirmation'])) {
        $activeTab = 'password';
    }
@endphp

<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="kt-card">
            <div class="kt-card-header border-bottom-0 pb-0">
                <ul class="nav nav-tabs profile-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'account' ? 'active' : '' }}"
                                id="account-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#account-panel"
                                type="button"
                                role="tab"
                                aria-controls="account-panel"
                                aria-selected="{{ $activeTab === 'account' ? 'true' : 'false' }}">
                            <i class="bi bi-person me-1"></i> Account
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTab === 'password' ? 'active' : '' }}"
                                id="password-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#password-panel"
                                type="button"
                                role="tab"
                                aria-controls="password-panel"
                                aria-selected="{{ $activeTab === 'password' ? 'true' : 'false' }}">
                            <i class="bi bi-shield-lock me-1"></i> Password
                        </button>
                    </li>
                </ul>
            </div>

            <div class="kt-card-body p-4">
                <div class="tab-content">
                    <div class="tab-pane fade {{ $activeTab === 'account' ? 'show active' : '' }}"
                         id="account-panel"
                         role="tabpanel"
                         aria-labelledby="account-tab"
                         tabindex="0">
                        <form method="POST" action="{{ route('profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Name / Business Name</label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}" required autofocus>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Username</label>
                                <input type="text" name="username" id="username"
                                       class="form-control @error('username') is-invalid @enderror"
                                       value="{{ old('username', $user->username) }}" required autocomplete="username"
                                       pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
                                <div class="form-text">Letters, numbers, and underscores only (3–30 characters).</div>
                                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <input type="email" name="email" id="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $user->email) }}" required autocomplete="email">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-kt-primary">Save Account</button>
                                <a href="{{ route('collections.index') }}" class="btn btn-kt-light">Cancel</a>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}"
                         id="password-panel"
                         role="tabpanel"
                         aria-labelledby="password-tab"
                         tabindex="0">
                        <p class="text-muted small mb-3">Change your sign-in password. Your current password is required.</p>

                        <form method="POST" action="{{ route('profile.password.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">Current Password</label>
                                <input type="password" name="current_password" id="current_password"
                                       class="form-control @error('current_password') is-invalid @enderror"
                                       required autocomplete="current-password">
                                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">New Password</label>
                                <input type="password" name="password" id="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       required autocomplete="new-password" minlength="8">
                                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="form-control" required autocomplete="new-password" minlength="8">
                            </div>

                            <button type="submit" class="btn btn-kt-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-tabs {
    border-bottom: 1px solid var(--kt-card-border);
    gap: 0.25rem;
}
.profile-tabs .nav-link {
    color: var(--kt-gray-600);
    font-weight: 600;
    font-size: 0.875rem;
    border: none;
    border-bottom: 2px solid transparent;
    border-radius: 0;
    padding: 0.65rem 1rem;
    margin-bottom: -1px;
}
.profile-tabs .nav-link:hover {
    color: var(--kt-gray-900);
    border-color: transparent;
}
.profile-tabs .nav-link.active {
    color: var(--kt-primary);
    background: transparent;
    border-color: transparent transparent var(--kt-primary);
}
</style>
@endpush
