@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your map collections and locations')

@section('page-actions')
    <a href="{{ route('contact.create') }}" class="btn btn-kt-light btn-sm">
        <i class="bi bi-chat-dots me-1"></i> Contact
    </a>
    <a href="{{ route('collections.create') }}" class="btn btn-kt-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> New Collection
    </a>
@endsection

@section('content')
<div class="welcome-banner d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h2>Welcome back, {{ auth()->user()->name }}!</h2>
        <p>Manage delivery routes, customer locations, and custom fields from one place.</p>
    </div>
    <a href="{{ route('collections.create') }}" class="btn btn-light btn-sm px-3">
        <i class="bi bi-plus-circle me-1"></i> Create collection
    </a>
</div>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-4">
        <div class="stat-widget">
            <div class="stat-widget-icon primary"><i class="bi bi-folder2-open"></i></div>
            <div>
                <div class="stat-widget-value">{{ $stats['collections'] }}</div>
                <div class="stat-widget-label">Collections</div>
                <div class="stat-widget-trend">Active map projects</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="stat-widget">
            <div class="stat-widget-icon success"><i class="bi bi-geo-alt"></i></div>
            <div>
                <div class="stat-widget-value">{{ number_format($stats['points']) }}</div>
                <div class="stat-widget-label">Map Points</div>
                <div class="stat-widget-trend">Locations tracked</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-4">
        <div class="stat-widget">
            <div class="stat-widget-icon info"><i class="bi bi-tags"></i></div>
            <div>
                <div class="stat-widget-value">{{ number_format($stats['attributes']) }}</div>
                <div class="stat-widget-label">Custom Attributes</div>
                <div class="stat-widget-trend">Fields across collections</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h5 fw-bold mb-0 text-gray-900" style="color: var(--kt-gray-900);">Your Collections</h2>
    @if($collections->isNotEmpty())
        <span class="kt-badge kt-badge-secondary">{{ $collections->count() }} total</span>
    @endif
</div>

@if($collections->isEmpty())
    <div class="kt-card">
        <div class="empty-state">
            <div class="empty-state-icon"><i class="bi bi-map"></i></div>
            <h3 class="h5 fw-bold mb-2">No collections yet</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 360px;">
                Create your first collection to start tracking locations on the map.
            </p>
            <a href="{{ route('collections.create') }}" class="btn btn-kt-primary">
                <i class="bi bi-plus-lg me-1"></i> Create your first collection
            </a>
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($collections as $collection)
            <div class="col-md-6 col-xl-4">
                <article class="collection-card">
                    <div class="collection-card-top">
                        <div class="collection-card-icon">
                            <i class="bi bi-layers"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="collection-card-title">{{ $collection->name }}</h3>
                            <p class="collection-card-desc">{{ $collection->description ?: 'No description provided' }}</p>
                        </div>
                    </div>
                    <div class="collection-card-stats">
                        <span class="collection-stat">
                            <i class="bi bi-geo-alt"></i> {{ $collection->points_count }} points
                        </span>
                        <span class="collection-stat">
                            <i class="bi bi-tags"></i> {{ $collection->attributes_count }} attributes
                        </span>
                    </div>
                    <div class="collection-card-footer">
                        <span class="small text-muted">Updated {{ $collection->updated_at->diffForHumans() }}</span>
                        <a href="{{ route('collections.show', $collection) }}" class="btn btn-kt-primary btn-sm">
                            Open <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
@endif
@endsection
