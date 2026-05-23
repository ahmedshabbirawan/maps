<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MapCollectionController extends Controller
{
    public function index(): View
    {
        $collections = Auth::user()
            ->collections()
            ->withCount(['points', 'attributes'])
            ->latest()
            ->get();

        return view('collections.index', compact('collections'));
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
            'points.values.attribute',
        ]);

        $pointsForMap = $collection->points->map(function ($point) use ($collection) {
            $attributes = [];
            foreach ($collection->attributes as $attribute) {
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

        $sidebarCollections = Auth::user()->collections()->orderBy('name')->get();

        return view('collections.show', compact('collection', 'pointsForMap', 'sidebarCollections'));
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

    public function storeAttribute(Request $request, Collection $collection): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:string,number,boolean'],
        ]);

        $attribute = $collection->attributes()->create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'attribute' => $attribute,
            ]);
        }

        return back()->with('success', 'Attribute added successfully.');
    }

    public function destroyAttribute(Collection $collection, Attribute $attribute): RedirectResponse|JsonResponse
    {
        $this->authorizeCollection($collection);

        if ($attribute->collection_id !== $collection->id) {
            abort(404);
        }

        $attribute->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Attribute removed successfully.');
    }

    public function export(Collection $collection, string $format = 'json'): Response|StreamedResponse
    {
        $this->authorizeCollection($collection);

        $collection->load(['attributes', 'points.values']);

        $rows = $collection->points->map(function ($point) use ($collection) {
            $row = [
                'id' => $point->id,
                'name' => $point->name,
                'lat' => (float) $point->lat,
                'lng' => (float) $point->lng,
            ];

            foreach ($collection->attributes as $attribute) {
                $row[$attribute->name] = $point->valueForAttribute($attribute->id);
            }

            return $row;
        });

        $filename = str($collection->name)->slug().'-'.now()->format('Y-m-d');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($rows, $collection) {
                $handle = fopen('php://output', 'w');

                $headers = array_merge(
                    ['id', 'name', 'lat', 'lng'],
                    $collection->attributes->pluck('name')->all()
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
