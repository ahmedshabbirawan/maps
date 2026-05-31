<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Services\PointAttributeFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MapCollectionController extends Controller
{
    public function __construct(
        protected PointAttributeFilter $attributeFilter
    ) {}

    public function index(): View
    {
        $collections = Auth::user()
            ->collections()
            ->withCount(['points', 'attributes'])
            ->latest()
            ->get();

        $stats = [
            'collections' => $collections->count(),
            'points' => (int) $collections->sum('points_count'),
            'attributes' => (int) $collections->sum('attributes_count'),
        ];

        return view('collections.index', compact('collections', 'stats'));
    }

    public function create(): View
    {
        return view('collections.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        Auth::user()->collections()->create($validated);

        return redirect()
            ->route('collections.index')
            ->with('success', 'Collection created successfully.');
    }

    public function show(Collection $collection): View
    {
        $this->authorizeCollection($collection);

        $collection->load([
            'attributes' => fn ($query) => $query->orderBy('name'),
            'points.attributeValues.attribute',
        ]);

        $visibleAttributes = $collection->attributes->where('is_visible', true);

        $pointsForMap = $collection->points->map(function ($point) use ($visibleAttributes) {
            $attributes = [];
            foreach ($visibleAttributes as $attribute) {
                $attributes[$attribute->name] = $point->valueForAttribute($attribute->id);
            }

            return [
                'id' => $point->id,
                'name' => $point->name,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
                'attributes' => $attributes,
            ];
        });

        $filterableAttributes = $collection->attributes->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'slug' => $a->slug,
            'type' => $a->type,
            'is_visible' => $a->is_visible,
        ])->values();

        $allAttributesForJs = $collection->attributes->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'slug' => $a->slug,
            'type' => $a->type,
            'is_visible' => $a->is_visible,
        ])->values();

        $visibleAttributesForJs = $visibleAttributes->map(fn ($a) => [
            'id' => $a->id,
            'name' => $a->name,
            'type' => $a->type,
        ])->values();

        $sidebarCollections = Auth::user()->collections()->orderBy('name')->get();

        return view('collections.show', compact(
            'collection',
            'pointsForMap',
            'visibleAttributes',
            'filterableAttributes',
            'allAttributesForJs',
            'visibleAttributesForJs',
            'sidebarCollections'
        ));
    }

    public function edit(Collection $collection): View
    {
        $this->authorizeCollection($collection);

        return view('collections.edit', compact('collection'));
    }

    public function update(Request $request, Collection $collection): RedirectResponse
    {
        $this->authorizeCollection($collection);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $collection->update($validated);

        return redirect()
            ->route('collections.show', $collection)
            ->with('success', 'Collection updated successfully.');
    }

    public function destroy(Collection $collection): RedirectResponse
    {
        $this->authorizeCollection($collection);

        $collection->delete();

        return redirect()
            ->route('collections.index')
            ->with('success', 'Collection deleted successfully.');
    }

    public function export(Request $request, Collection $collection, string $format = 'json'): JsonResponse|Response|StreamedResponse
    {
        $this->authorizeCollection($collection);

        $collection->load(['attributes' => fn ($q) => $q->orderBy('name')]);

        $visibleAttributes = $collection->attributes->where('is_visible', true);

        $query = $collection->points()
            ->with(['attributeValues'])
            ->orderBy('name');

        $this->attributeFilter->apply($query, $collection, $request);

        $pointIds = collect($request->input('point_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($pointIds->isNotEmpty()) {
            $query->whereIn('id', $pointIds);
        }

        $rows = $query->get()->map(function ($point) use ($visibleAttributes) {
            $row = [
                'id' => $point->id,
                'name' => $point->name,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
            ];

            foreach ($visibleAttributes as $attribute) {
                $row[$attribute->name] = $point->valueForAttribute($attribute->id);
            }

            return $row;
        });

        $filename = str($collection->name)->slug().'-'.now()->format('Y-m-d');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows, $visibleAttributes) {
                $handle = fopen('php://output', 'w');

                $headers = array_merge(
                    ['id', 'name', 'lat', 'lng'],
                    $visibleAttributes->pluck('name')->all()
                );
                fputcsv($handle, $headers);

                foreach ($rows as $row) {
                    fputcsv($handle, array_map(fn ($key) => $row[$key] ?? '', $headers));
                }

                fclose($handle);
            }, "{$filename}.csv", [
                'Content-Type' => 'text/csv',
            ]);
        }

        return response()->json([
            'collection' => $collection->name,
            'exported_at' => now()->toIso8601String(),
            'points' => $rows,
        ])->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
    }

    protected function authorizeCollection(Collection $collection): void
    {
        if ($collection->user_id !== Auth::id()) {
            abort(403);
        }
    }
}
