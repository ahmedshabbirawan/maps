<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Map Collections') — {{ config('app.name', 'Maps SaaS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="@auth app-body @else bg-light @endauth">
    @auth
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell">
        <aside class="app-sidebar" id="appSidebar">
            <a href="{{ route('collections.index') }}" class="app-sidebar-brand">
                <span class="brand-icon"><i class="bi bi-geo-alt-fill"></i></span>
                <span>Map Collections</span>
            </a>

            <div class="app-sidebar-menu">
                <div class="menu-section-label">Main</div>
                <a href="{{ route('collections.index') }}"
                   class="menu-link {{ request()->routeIs('collections.index') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="bi bi-grid-1x2"></i></span>
                    Dashboard
                </a>
                <a href="{{ route('collections.create') }}" class="menu-link menu-link-primary">
                    <span class="menu-icon"><i class="bi bi-plus-lg"></i></span>
                    New Collection
                </a>

                <div class="menu-section-label">Collections</div>
                @forelse(($sidebarCollections ?? auth()->user()->collections()->orderBy('name')->get()) as $item)
                    <a href="{{ route('collections.show', $item) }}"
                       class="menu-link {{ request()->routeIs('collections.show') && optional(request()->route('collection'))->id === $item->id ? 'active' : '' }}">
                        <span class="menu-icon"><i class="bi bi-folder2"></i></span>
                        <span class="text-truncate">{{ $item->name }}</span>
                    </a>
                @empty
                    <p class="small text-muted px-3 mb-0">No collections yet</p>
                @endforelse

                <div class="menu-section-label">Support</div>
                <a href="{{ route('contact.create') }}"
                   class="menu-link {{ request()->routeIs('contact.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i class="bi bi-chat-dots"></i></span>
                    Contact &amp; Feedback
                </a>
            </div>

            <div class="app-sidebar-footer">
                @php
                    $user = auth()->user();
                    $initials = collect(explode(' ', $user->name))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join('');
                @endphp
                <div class="user-card">
                    <div class="user-avatar">{{ strtoupper($initials) }}</div>
                    <div class="min-w-0">
                        <div class="user-name text-truncate">{{ $user->name }}</div>
                        <div class="user-role text-truncate">{{ $user->email }}</div>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-kt-light w-100 btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i> Sign out
                    </button>
                </form>
            </div>
        </aside>

        <div class="app-wrapper">
            <header class="app-header">
                <div class="d-flex align-items-center gap-3 min-w-0">
                    <button type="button" class="btn btn-kt-light btn-sm d-lg-none flex-shrink-0" id="sidebarToggle" aria-label="Toggle menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="min-w-0">
                        @hasSection('breadcrumb')
                            <ol class="app-breadcrumb mb-1">@yield('breadcrumb')</ol>
                        @endif
                        <h1 class="page-heading text-truncate">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p class="page-subheading">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>
                @hasSection('page-actions')
                    <div class="toolbar-actions flex-shrink-0">@yield('page-actions')</div>
                @endif
            </header>

            <main class="app-content">
                @if(session('success'))
                    <div class="alert alert-kt alert-kt-success alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-kt alert-kt-danger alert-dismissible fade show mb-4" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @else
    <div class="auth-guest-wrap w-100">
        <div class="w-100">
            @if(session('success'))
                <div class="alert alert-kt alert-kt-success alert-dismissible fade show mx-auto mb-3" style="max-width: 440px;" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-kt alert-kt-danger alert-dismissible fade show mx-auto mb-3" style="max-width: 440px;" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>
    @endauth

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        (function () {
            const sidebar = document.getElementById('appSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggle = document.getElementById('sidebarToggle');
            if (!sidebar || !toggle) return;

            function closeSidebar() {
                sidebar.classList.remove('show');
                backdrop?.classList.remove('show');
            }

            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('show');
                backdrop?.classList.toggle('show');
            });
            backdrop?.addEventListener('click', closeSidebar);
        })();
    </script>
    @stack('scripts')
</body>
</html>
