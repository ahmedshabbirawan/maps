<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Map Collections') — {{ config('app.name', 'Maps SaaS') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet">
    @stack('styles')
    <style>
        body { min-height: 100vh; background-color: #f8f9fa; }
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #212529;
            color: #fff;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,.75);
            border-radius: .375rem;
            margin-bottom: .25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .sidebar-brand { font-weight: 600; letter-spacing: .02em; }
        .main-content { flex: 1; min-width: 0; }
        #map { height: 100%; min-height: 420px; border-radius: .375rem; }
        .split-map { min-height: 520px; }
        .attr-badge { font-size: .75rem; }
    </style>
</head>
<body class="@auth @else bg-light @endauth">
    <div class="@auth d-flex @else min-vh-100 d-flex align-items-center @endauth">
        @auth
        <aside class="sidebar p-3 d-flex flex-column">
            <div class="sidebar-brand mb-1">
                <i class="bi bi-geo-alt-fill text-primary"></i> Map Collections
            </div>
            <small class="text-secondary mb-3">{{ auth()->user()->name }}</small>

            <a href="{{ route('collections.index') }}" class="btn btn-sm btn-outline-light mb-3">
                <i class="bi bi-grid"></i> All Collections
            </a>
            <a href="{{ route('collections.create') }}" class="btn btn-sm btn-primary mb-4">
                <i class="bi bi-plus-lg"></i> New Collection
            </a>

            <div class="text-uppercase text-secondary small mb-2">Your Collections</div>
            <nav class="nav flex-column flex-grow-1 overflow-auto">
                @foreach(($sidebarCollections ?? auth()->user()->collections()->orderBy('name')->get()) as $item)
                    <a href="{{ route('collections.show', $item) }}"
                       class="nav-link {{ request()->routeIs('collections.show') && optional(request()->route('collection'))->id === $item->id ? 'active' : '' }}">
                        <i class="bi bi-folder2-open me-1"></i> {{ $item->name }}
                    </a>
                @endforeach
            </nav>

            <form action="{{ route('logout') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary w-100">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </aside>
        @endauth

        <main class="@auth main-content p-4 @else w-100 @endauth">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    </script>
    @stack('scripts')
</body>
</html>
