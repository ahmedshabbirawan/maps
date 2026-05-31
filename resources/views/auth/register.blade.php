@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-card mx-auto" style="max-width: 480px;">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon d-inline-flex mb-3" style="width: 48px; height: 48px; border-radius: 0.65rem; background: linear-gradient(135deg, var(--kt-primary) 0%, #5eb3ff 100%); color: #fff; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="bi bi-building"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">Create account</h1>
            <p class="text-muted small mb-0">Organizations, businesses, and individuals</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">Name / Business Name</label>
                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}" required autofocus>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="username" class="form-label fw-semibold">Username</label>
                <input type="text" name="username" id="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username') }}" required autocomplete="username"
                       pattern="[a-zA-Z0-9_]+" minlength="3" maxlength="30">
                <div class="form-text">Letters, numbers, and underscores only (3–30 characters).</div>
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-kt-primary w-100 py-2">Create account</button>
        </form>
        <p class="mt-4 mb-0 text-center small text-muted">
            Already registered? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--kt-primary);">Sign in</a>
        </p>
    </div>
</div>
@endsection
