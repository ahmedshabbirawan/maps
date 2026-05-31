@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="auth-card mx-auto">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon d-inline-flex mb-3" style="width: 48px; height: 48px; border-radius: 0.65rem; background: linear-gradient(135deg, var(--kt-success) 0%, #6ee7a0 100%); color: #fff; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">Reset password</h1>
            <p class="text-muted small mb-0">Choose a new password for your account</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $email) }}" required autofocus autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">New password</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm new password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-kt-primary w-100 py-2">Reset password</button>
        </form>
    </div>
</div>
@endsection
