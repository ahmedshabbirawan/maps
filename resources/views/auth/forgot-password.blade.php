@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="auth-card mx-auto">
    <div class="p-4 p-md-5">
        <div class="text-center mb-4">
            <div class="brand-icon d-inline-flex mb-3" style="width: 48px; height: 48px; border-radius: 0.65rem; background: linear-gradient(135deg, var(--kt-warning) 0%, #ffe066 100%); color: #7a6200; align-items: center; justify-content: center; font-size: 1.25rem;">
                <i class="bi bi-key"></i>
            </div>
            <h1 class="h4 fw-bold mb-1">Forgot password?</h1>
            <p class="text-muted small mb-0">We'll email you a reset link</p>
        </div>

        @if (session('status'))
            <div class="alert alert-kt alert-kt-success small">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="mb-4">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}" required autofocus autocomplete="email">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="btn btn-kt-primary w-100 py-2">Email reset link</button>
        </form>
        <p class="mt-4 mb-0 text-center small">
            <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--kt-primary);">Back to sign in</a>
        </p>
    </div>
</div>
@endsection
