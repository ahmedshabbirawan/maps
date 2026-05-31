@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-card mx-auto">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon d-inline-flex mb-3" style="width: 48px; height: 48px; border-radius: 0.65rem; background: linear-gradient(135deg, var(--kt-primary) 0%, #5eb3ff 100%); color: #fff; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">Sign in</h1>
            <p class="text-muted small mb-0">Access your map collections dashboard</p>
        </div>

        @if (session('status'))
            <div class="alert alert-kt alert-kt-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="login" class="form-label fw-semibold">Username or email</label>
                <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror"
                       value="{{ old('login') }}" required autofocus autocomplete="username">
                @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check mb-0">
                    <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
                    <label for="remember" class="form-check-label small">Remember me</label>
                </div>
                <a href="{{ route('password.request') }}" class="small text-decoration-none" style="color: var(--kt-primary);">Forgot password?</a>
            </div>
            <button type="submit" class="btn btn-kt-primary w-100 py-2">Sign in</button>
        </form>
        <p class="mt-4 mb-0 text-center small text-muted">
            No account? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--kt-primary);">Register your organization</a>
        </p>
    </div>
</div>
@endsection
