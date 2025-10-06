<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use Illuminate\Http\JsonResponse;

class RouterMapController extends Controller
{
    public function index(): JsonResponse
    {
        $routers = Router::with('category')
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function ($router) {
                return [
                    'id' => $router->id,
                    'name' => $router->name,
                    'ip_address' => $router->ip_address,
                    'location' => $router->location,
                    'latitude' => (float) $router->latitude,
                    'longitude' => (float) $router->longitude,
                    'status' => $router->status,
                    'category' => $router->category?->name,
                    'last_checked_at' => $router->last_checked_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $routers,
        ]);
    }

    public function geojson(): JsonResponse
    {
        $routers = Router::with('category')
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $features = $routers->map(function ($router) {
            return [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [
                        (float) $router->longitude,
                        (float) $router->latitude,
                    ],
                ],
                'properties' => [
                    'id' => $router->id,
                    'name' => $router->name,
                    'ip_address' => $router->ip_address,
                    'location' => $router->location,
                    'status' => $router->status,
                    'category' => $router->category?->name,
                    'description' => $router->description,
                    'last_checked_at' => $router->last_checked_at?->toIso8601String(),
                ],
            ];
        });

        $geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        return response()->json($geojson);
    }

    public function metrics(): string
    {
        $routers = Router::with('category')
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        $metrics = [];
        
        $metrics[] = "# HELP mikrotik_location Router location coordinates";
        $metrics[] = "# TYPE mikrotik_location gauge";

        foreach ($routers as $router) {
            $labels = $this->formatLabels([
                'router_name' => $router->name,
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'category' => $router->category->name ?? 'unknown',
                'location' => $router->location ?? 'unknown',
                'status' => $router->status,
                'latitude' => (string) $router->latitude,
                'longitude' => (string) $router->longitude,
            ]);
            
            // Use status as value (1 for up, 0 for down)
            $value = $router->status === 'up' ? 1 : 0;
            $metrics[] = "mikrotik_location{$labels} $value";
        }

        return response(implode("\n", $metrics) . "\n", 200)
            ->header('Content-Type', 'text/plain; version=0.0.4');
    }

    private function formatLabels(array $labels): string
    {
        $formatted = [];
        foreach ($labels as $key => $value) {
            $escapedValue = $this->escapeLabel($value);
            $formatted[] = "$key=\"$escapedValue\"";
        }
        return '{' . implode(',', $formatted) . '}';
    }

    private function escapeLabel(string $value): string
    {
        return str_replace(['"', '\\', "\n"], ['\\"', '\\\\', '\\n'], $value);
    }
}
