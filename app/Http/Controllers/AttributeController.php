<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AttributeController extends Controller
{
    public function index(Collection $collection): View
    {
        $this->authorizeCollection($collection);

        $attributes = $collection->attributes()->orderBy('name')->get();
        $sidebarCollections = Auth::user()->collections()->orderBy('name')->get();

        return view('collections.attributes.index', compact('collection', 'attributes', 'sidebarCollections'));
    }

    public function store(Request $request, Collection $collection): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:string,number,date,boolean'],
            'is_visible' => ['sometimes', 'boolean'],
        ]);

        $slug = Attribute::uniqueSlugForCollection(
            $collection->id,
            Str::slug($validated['name'])
        );

        $attribute = $collection->attributes()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'is_visible' => $request->boolean('is_visible', true),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'attribute' => $attribute]);
        }

        return back()->with('success', 'Attribute created successfully.');
    }

    public function update(Request $request, Collection $collection, Attribute $attribute): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);
        $this->authorizeAttribute($collection, $attribute);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:string,number,date,boolean'],
            'is_visible' => ['sometimes', 'boolean'],
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'is_visible' => $request->has('is_visible')
                ? $request->boolean('is_visible')
                : $attribute->is_visible,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'attribute' => $attribute->fresh()]);
        }

        return back()->with('success', 'Attribute updated successfully.');
    }

    public function destroy(Collection $collection, Attribute $attribute): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);
        $this->authorizeAttribute($collection, $attribute);

        $attribute->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Attribute deleted successfully.');
    }

    public function toggleVisibility(Collection $collection, Attribute $attribute): JsonResponse
    {
        $this->authorizeCollection($collection);
        $this->authorizeAttribute($collection, $attribute);

        $attribute->update(['is_visible' => ! $attribute->is_visible]);

        return response()->json([
            'success' => true,
            'attribute' => $attribute->fresh(),
        ]);
    }

    protected function authorizeCollection(Collection $collection): void
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function authorizeAttribute(Collection $collection, Attribute $attribute): void
    {
        if ($attribute->collection_id !== $collection->id) {
            abort(404);
        }
    }
}
