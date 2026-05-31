@extends('layouts.app')

@section('title', 'Edit Collection')

@section('breadcrumb')
    <li><a href="{{ route('collections.index') }}">Dashboard</a></li>
    <li class="separator">/</li>
    <li><a href="{{ route('collections.show', $collection) }}">{{ $collection->name }}</a></li>
    <li class="separator">/</li>
    <li>Edit</li>
@endsection

@section('page-title', 'Edit Collection')
@section('page-subtitle', 'Update name and description')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="kt-card">
            <div class="kt-card-header">
                <h2 class="card-title"><i class="bi bi-pencil text-primary"></i> Collection Details</h2>
            </div>
            <div class="kt-card-body p-4">
                <form method="POST" action="{{ route('collections.update', $collection) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Collection Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $collection->name) }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $collection->description) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-kt-primary">Save Changes</button>
                        <a href="{{ route('collections.show', $collection) }}" class="btn btn-kt-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
