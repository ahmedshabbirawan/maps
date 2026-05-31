@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Sign in to your account</h1>

                @if (session('status'))
                    <div class="alert alert-success small">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label">Username or email</label>
                        <input type="text" name="login" id="login" class="form-control @error('login') is-invalid @enderror"
                               value="{{ old('login') }}" required autofocus autocomplete="username">
                        @error('login')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required autocomplete="current-password">
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check mb-0">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" value="1">
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="small">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="mt-3 mb-0 text-center small">
                    No account? <a href="{{ route('register') }}">Register your organization</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
