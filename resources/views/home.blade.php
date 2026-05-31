@extends('layouts.frontend')

@section('title', 'Map Collections')

@section('content')
<nav id="homeNavbar" class="navbar navbar-expand-lg fixed-top home-navbar">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <i class="bi bi-geo-alt-fill text-primary"></i>
            {{ config('app.name', 'Maps SaaS') }}
        </a>
        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#homeNavMenu" aria-controls="homeNavMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="homeNavMenu">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="#features">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#how-it-works">How it works</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#cta">Pricing</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('contact.create') }}" class="nav-link px-0">Contact</a>
                <a href="{{ route('login') }}" class="nav-link px-0">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-home-accent">Get Started</a>
            </div>
        </div>
    </div>
</nav>

<section class="hero-section">
    <div class="container position-relative" style="z-index: 1;">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <span class="hero-badge">Geospatial data, simplified</span>
                <h1 class="hero-title">
                    Organize your maps.<br>
                    <span class="gradient-text">Own your locations.</span>
                </h1>
                <p class="hero-subtitle">
                    Build rich map collections with custom attributes, interactive points, and powerful exports — all in one premium workspace built for teams.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-home-accent btn-lg px-4">Start free today</a>
                    <a href="#features" class="btn btn-home-outline btn-lg px-4 home-scroll-link">Explore features</a>
                </div>
            </div>
            <div class="col-lg-6 position-relative">
                <div class="hero-glow"></div>
                <div class="hero-mockup">
                    <div class="mockup-chrome">
                        <span class="mockup-dot"></span>
                        <span class="mockup-dot"></span>
                        <span class="mockup-dot"></span>
                    </div>
                    <div class="mockup-body">
                        <div class="mockup-sidebar">
                            <div class="mockup-line medium"></div>
                            <div class="mockup-line short"></div>
                            <div class="mockup-line"></div>
                            <div class="mockup-line short"></div>
                            <div class="mockup-line medium"></div>
                        </div>
                        <div class="mockup-map">
                            <span><i class="bi bi-map me-2"></i>Interactive map dashboard</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="features-section">
    <div class="container">
        <p class="section-label text-center">Features</p>
        <h2 class="section-heading text-center">Everything you need to manage locations</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <article class="feature-card">
                    <div class="feature-icon"><i class="bi bi-folder2-open"></i></div>
                    <h3>Map Collections</h3>
                    <p>Group related locations into organized collections with a clean sidebar and instant navigation.</p>
                    <a href="{{ route('register') }}" class="feature-link">Create a collection <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="feature-card">
                    <div class="feature-icon"><i class="bi bi-tags"></i></div>
                    <h3>Custom Attributes</h3>
                    <p>Define flexible metadata fields for every point — text, numbers, dates, and more.</p>
                    <a href="{{ route('register') }}" class="feature-link">Learn more <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="feature-card">
                    <div class="feature-icon"><i class="bi bi-pin-map"></i></div>
                    <h3>Interactive Maps</h3>
                    <p>Plot, edit, and explore points on beautiful Leaflet-powered maps with geocoding search.</p>
                    <a href="{{ route('register') }}" class="feature-link">See it in action <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
            <div class="col-md-6 col-lg-3">
                <article class="feature-card">
                    <div class="feature-icon"><i class="bi bi-download"></i></div>
                    <h3>Export & Share</h3>
                    <p>Download your data as JSON or CSV whenever you need to integrate with other tools.</p>
                    <a href="{{ route('register') }}" class="feature-link">Get started <i class="bi bi-arrow-right"></i></a>
                </article>
            </div>
        </div>
    </div>
</section>

<section id="how-it-works" class="py-5">
    <div class="container py-4">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <p class="section-label">How it works</p>
                <h2 class="section-heading mb-4">Three steps to your first collection</h2>
                <div class="row g-4 text-start">
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <span class="text-primary fw-bold fs-4">01</span>
                            <div>
                                <h3 class="h6 fw-bold mb-2">Register your organization</h3>
                                <p class="small mb-0" style="color: var(--home-text-muted);">Create an account in seconds and access your private workspace.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <span class="text-primary fw-bold fs-4">02</span>
                            <div>
                                <h3 class="h6 fw-bold mb-2">Add points & attributes</h3>
                                <p class="small mb-0" style="color: var(--home-text-muted);">Drop pins on the map and attach the metadata your team relies on.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-3">
                            <span class="text-primary fw-bold fs-4">03</span>
                            <div>
                                <h3 class="h6 fw-bold mb-2">Export when ready</h3>
                                <p class="small mb-0" style="color: var(--home-text-muted);">Pull clean JSON or CSV exports for reporting and integrations.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="cta" class="cta-section">
    <div class="container">
        <div class="cta-panel">
            <h2>Ready to map smarter?</h2>
            <p>Join teams who manage locations with clarity. No credit card required — start building your first collection today.</p>
            <a href="{{ route('register') }}" class="btn btn-home-accent btn-lg px-5">Sign up free</a>
        </div>
    </div>
</section>

<footer class="home-footer">
    <div class="container">
        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="footer-brand d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-geo-alt-fill text-primary"></i>
                    {{ config('app.name', 'Maps SaaS') }}
                </div>
                <p class="small mb-0">Premium geospatial collections for modern teams.</p>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white small fw-bold mb-3">Product</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#features">Features</a></li>
                    <li class="mb-2"><a href="#how-it-works">How it works</a></li>
                    <li class="mb-2"><a href="{{ route('register') }}">Get started</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-2">
                <h6 class="text-white small fw-bold mb-3">Account</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="{{ route('login') }}">Sign in</a></li>
                    <li class="mb-2"><a href="{{ route('register') }}">Register</a></li>
                    <li class="mb-2"><a href="{{ route('contact.create') }}">Contact / Suggestions</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="text-white small fw-bold mb-3">Legal</h6>
                <ul class="list-unstyled small">
                    <li class="mb-2"><a href="#">Privacy Policy</a></li>
                    <li class="mb-2"><a href="#">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 pt-3 border-top border-secondary border-opacity-25">
            <p class="small mb-0">&copy; {{ date('Y') }} {{ config('app.name', 'Maps SaaS') }}. All rights reserved.</p>
            <div class="d-flex gap-2">
                <a href="#" class="social-link" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="social-link" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                <a href="#" class="social-link" aria-label="GitHub"><i class="bi bi-github"></i></a>
            </div>
        </div>
    </div>
</footer>
@endsection

@push('scripts')
<script>
$(function () {
    var $navbar = $('#homeNavbar');
    var scrollThreshold = 50;

    function updateNavbar() {
        if ($(window).scrollTop() > scrollThreshold) {
            $navbar.addClass('glass-nav');
        } else {
            $navbar.removeClass('glass-nav');
        }
    }

    $(window).on('scroll', updateNavbar);
    updateNavbar();

    $('.home-scroll-link, .home-navbar .nav-link[href^="#"]').on('click', function (e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - ($navbar.outerHeight() || 0) - 16
            }, 600);
            var collapse = document.getElementById('homeNavMenu');
            if (collapse && collapse.classList.contains('show')) {
                bootstrap.Collapse.getOrCreateInstance(collapse).hide();
            }
        }
    });
});
</script>
@endpush
