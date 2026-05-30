<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\Collection;
use App\Models\Point;
use App\Services\PointAttributeFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    public function __construct(
        protected PointAttributeFilter $attributeFilter
    ) {}

    public function index(Request $request, Collection $collection): JsonResponse
    {
        $this->authorizeCollection($collection);

        $collection->load(['attributes' => fn ($q) => $q->orderBy('name')]);

        $query = $collection->points()
            ->with(['attributeValues.attribute'])
            ->orderBy('name');

        $this->attributeFilter->apply($query, $collection, $request);

        $visibleAttributes = $collection->attributes->where('is_visible', true);

        $points = $query->get()->map(
            fn (Point $point) => $this->formatPoint($point, $visibleAttributes)
        );

        return response()->json([
            'success' => true,
            'points' => $points,
            'count' => $points->count(),
        ]);
    }

    public function store(Request $request, Collection $collection): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);

        $validated = $this->validatePoint($request, $collection);

        $point = DB::transaction(function () use ($collection, $validated, $request) {
            $point = $collection->points()->create([
                'name' => $validated['name'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
            ]);

            $this->syncAttributeValues($point, $collection, $request->input('attributes', []));

            return $point->load('attributeValues.attribute');
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'point' => $this->formatPoint($point, $collection->attributes),
            ]);
        }

        return back()->with('success', 'Point added successfully.');
    }

    public function update(Request $request, Collection $collection, Point $point): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);
        $this->authorizePoint($collection, $point);

        $validated = $this->validatePoint($request, $collection);

        DB::transaction(function () use ($point, $collection, $validated, $request) {
            $point->update([
                'name' => $validated['name'],
                'lat' => $validated['lat'],
                'lng' => $validated['lng'],
            ]);

            $this->syncAttributeValues($point, $collection, $request->input('attributes', []));
        });

        $point->load('attributeValues.attribute');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'point' => $this->formatPoint($point, $collection->attributes),
            ]);
        }

        return back()->with('success', 'Point updated successfully.');
    }

    public function destroy(Collection $collection, Point $point): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);
        $this->authorizePoint($collection, $point);

        $point->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Point deleted successfully.');
    }

    protected function validatePoint(Request $request, Collection $collection): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ];

        foreach ($collection->attributes as $attribute) {
            $key = 'attributes.'.$attribute->id;
            $rules[$key] = match ($attribute->type) {
                'number' => ['nullable', 'numeric'],
                'boolean' => ['nullable', 'boolean'],
                'date' => ['nullable', 'date'],
                default => ['nullable', 'string', 'max:65535'],
            };
        }

        return $request->validate($rules);
    }

    protected function syncAttributeValues(Point $point, Collection $collection, array $attributes): void
    {
        foreach ($collection->attributes as $attribute) {
            $value = $attributes[$attribute->id] ?? null;

            if ($attribute->type === 'boolean') {
                $value = isset($attributes[$attribute->id]) && filter_var($attributes[$attribute->id], FILTER_VALIDATE_BOOLEAN)
                    ? '1'
                    : '0';
            } elseif ($value === null || $value === '') {
                $point->attributeValues()->where('attribute_id', $attribute->id)->delete();

                continue;
            }

            $point->attributeValues()->updateOrCreate(
                ['attribute_id' => $attribute->id],
                ['value' => AttributeValue::serializeValue($attribute, $value)]
            );
        }
    }

    protected function formatPoint(Point $point, $attributes): array
    {
        $attributeData = [];

        foreach ($attributes as $attribute) {
            $raw = $point->valueForAttribute($attribute->id);
            $attributeData[$attribute->id] = [
                'name' => $attribute->name,
                'slug' => $attribute->slug,
                'type' => $attribute->type,
                'is_visible' => $attribute->is_visible,
                'value' => $raw,
            ];
        }

        return [
            'id' => $point->id,
            'name' => $point->name,
            'lat' => (float) $point->lat,
            'lng' => (float) $point->lng,
            'attributes' => $attributeData,
        ];
    }

    protected function authorizeCollection(Collection $collection): void
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function authorizePoint(Collection $collection, Point $point): void
    {
        if ($point->collection_id !== $collection->id) {
            abort(404);
        }
    }
}
