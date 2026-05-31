@extends('layouts.frontend')

@section('title', 'Contact Us')

@section('content')
<nav class="navbar navbar-expand-lg home-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('collections.index') : route('home') }}">
            <i class="bi bi-geo-alt-fill text-primary"></i>
            {{ config('app.name', 'Maps SaaS') }}
        </a>
        <div class="d-flex align-items-center gap-2">
            @auth
                <a href="{{ route('collections.index') }}" class="nav-link px-0">Dashboard</a>
            @else
                <a href="{{ route('home') }}" class="nav-link px-0">Home</a>
            @endauth
        </div>
    </div>
</nav>

<section class="py-5" style="min-height: calc(100vh - 80px);">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-6">
                <p class="section-label text-center mb-2">Get in touch</p>
                <h1 class="section-heading text-center mb-2">Contact Us / Suggestions</h1>
                <p class="text-center mb-4" style="color: var(--home-text-muted);">
                    Share feedback, report an issue, or suggest a feature. We read every message.
                </p>

                @if(session('success'))
                    <div class="alert alert-success border-0" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="contact-form-card">
                    <form action="{{ route('contact.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-secondary">(optional if mobile provided)</span></label>
                            <input type="email"
                                   name="email"
                                   id="email"
                                   class="form-control contact-input @error('email') is-invalid @enderror"
                                   value="{{ old('email', auth()->user()?->email) }}"
                                   autocomplete="email"
                                   placeholder="you@example.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile <span class="text-secondary">(optional if email provided)</span></label>
                            <input type="tel"
                                   name="mobile"
                                   id="mobile"
                                   class="form-control contact-input @error('mobile') is-invalid @enderror"
                                   value="{{ old('mobile') }}"
                                   autocomplete="tel"
                                   placeholder="+1 555 000 0000">
                            @error('mobile')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Comment or suggestion <span class="text-danger">*</span></label>
                            <textarea name="message"
                                      id="message"
                                      rows="6"
                                      class="form-control contact-input @error('message') is-invalid @enderror"
                                      required
                                      placeholder="Tell us what’s on your mind…">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="captcha" class="form-label">Security check <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="captcha-prompt">What is {{ $captchaQuestion }}?</span>
                                <a href="{{ route('contact.create', ['refresh' => 1]) }}" class="small text-decoration-none" style="color: var(--home-accent);">New question</a>
                            </div>
                            <input type="number"
                                   name="captcha"
                                   id="captcha"
                                   class="form-control contact-input @error('captcha') is-invalid @enderror"
                                   value="{{ old('captcha') }}"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   required
                                   placeholder="Your answer">
                            @error('captcha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-home-accent w-100 py-2">
                            <i class="bi bi-send me-1"></i> Submit message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
.contact-form-card {
    background: var(--home-surface);
    border: 1px solid var(--home-border);
    border-radius: 1rem;
    padding: 1.75rem;
}
.contact-input {
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid var(--home-border);
    color: var(--home-text);
}
.contact-input:focus {
    background: rgba(0, 0, 0, 0.35);
    border-color: var(--home-accent);
    color: var(--home-text);
    box-shadow: 0 0 0 0.2rem var(--home-accent-glow);
}
.contact-input::placeholder {
    color: var(--home-text-muted);
    opacity: 0.7;
}
.form-label {
    color: var(--home-text);
    font-weight: 500;
    font-size: 0.9rem;
}
.captcha-prompt {
    font-weight: 600;
    color: var(--home-text);
    padding: 0.35rem 0.75rem;
    background: rgba(99, 102, 241, 0.15);
    border-radius: 0.375rem;
}
.alert-success {
    background: rgba(34, 197, 94, 0.15);
    color: #86efac;
}
.alert-danger {
    background: rgba(239, 68, 68, 0.15);
    color: #fca5a5;
}
</style>
@endpush
