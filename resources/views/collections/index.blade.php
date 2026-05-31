@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Map Collections</h1>
        <p class="text-muted mb-0">Manage delivery routes, customer locations, and custom fields.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('contact.create') }}" class="btn btn-outline-info">
            <i class="bi bi-chat-dots"></i> Contact
        </a>
        <a href="{{ route('collections.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New Collection
        </a>
    </div>
</div>

@if($collections->isEmpty())
    <div class="card shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-map display-4 text-muted"></i>
            <p class="mt-3 mb-3">You have no collections yet. Create one to start tracking locations.</p>
            <a href="{{ route('collections.create') }}" class="btn btn-primary">Create your first collection</a>
        </div>
    </div>
@else
    <div class="row g-3">
        @foreach($collections as $collection)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h2 class="h5 card-title">{{ $collection->name }}</h2>
                        <p class="card-text text-muted small">{{ $collection->description ?: 'No description' }}</p>
                        <div class="d-flex gap-2 small text-secondary mb-3">
                            <span><i class="bi bi-geo"></i> {{ $collection->points_count }} points</span>
                            <span><i class="bi bi-tags"></i> {{ $collection->attributes_count }} attributes</span>
                        </div>
                        <a href="{{ route('collections.show', $collection) }}" class="btn btn-sm btn-outline-primary">Open Dashboard</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
