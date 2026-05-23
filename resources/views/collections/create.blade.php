@extends('layouts.app')

@section('title', 'New Collection')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-4">Create Map Collection</h1>
                <form method="POST" action="{{ route('collections.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Collection Name</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}"
                               placeholder="e.g. Daily Newspaper Route" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="3"
                                  placeholder="Optional notes about this collection">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Create Collection</button>
                        <a href="{{ route('collections.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
