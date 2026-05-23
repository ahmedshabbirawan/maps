<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Point;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
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

            return $point->load('values.attribute');
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'point' => $this->formatPoint($point, $collection),
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

        $point->load('values.attribute');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'point' => $this->formatPoint($point, $collection),
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
                $point->values()->where('attribute_id', $attribute->id)->delete();

                continue;
            }

            $point->values()->updateOrCreate(
                ['attribute_id' => $attribute->id],
                ['value' => (string) $value]
            );
        }
    }

    protected function formatPoint(Point $point, Collection $collection): array
    {
        $attributes = [];
        foreach ($collection->attributes as $attribute) {
            $attributes[$attribute->id] = [
                'name' => $attribute->name,
                'type' => $attribute->type,
                'value' => $point->valueForAttribute($attribute->id),
            ];
        }

        return [
            'id' => $point->id,
            'name' => $point->name,
            'lat' => (float) $point->lat,
            'lng' => (float) $point->lng,
            'attributes' => $attributes,
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
