<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:255'],
        ]);

        $response = Http::withHeaders([
            'User-Agent' => config('app.name', 'Maps SaaS').' Geocoder (contact@example.com)',
            'Accept-Language' => 'en',
        ])->timeout(10)->get('https://nominatim.openstreetmap.org/search', [
            'format' => 'json',
            'q' => $validated['q'],
            'limit' => 6,
            'addressdetails' => 1,
        ]);

        if (! $response->successful()) {
            return response()->json(['results' => []], 502);
        }

        $results = collect($response->json())->map(fn (array $item) => [
            'display_name' => $item['display_name'],
            'lat' => (float) $item['lat'],
            'lng' => (float) $item['lon'],
        ])->values();

        return response()->json(['results' => $results]);
    }
}
