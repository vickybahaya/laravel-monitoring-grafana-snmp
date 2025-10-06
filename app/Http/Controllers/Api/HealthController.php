<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    /**
     * Health check endpoint
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $health = [
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'services' => []
        ];

        // Check database
        try {
            DB::connection()->getPdo();
            $health['services']['database'] = [
                'status' => 'up',
                'connection' => config('database.default')
            ];
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['database'] = [
                'status' => 'down',
                'error' => $e->getMessage()
            ];
        }

        // Check cache
        try {
            Cache::put('health_check', true, 10);
            $cacheWorks = Cache::get('health_check');
            
            $health['services']['cache'] = [
                'status' => $cacheWorks ? 'up' : 'down',
                'driver' => config('cache.default')
            ];
        } catch (\Exception $e) {
            $health['status'] = 'degraded';
            $health['services']['cache'] = [
                'status' => 'down',
                'error' => $e->getMessage()
            ];
        }

        // Check storage
        $storagePath = storage_path('app');
        $health['services']['storage'] = [
            'status' => is_writable($storagePath) ? 'up' : 'down',
            'writable' => is_writable($storagePath)
        ];

        // Application info
        $health['app'] = [
            'name' => config('app.name'),
            'env' => config('app.env'),
            'debug' => config('app.debug'),
            'version' => '1.0.0'
        ];

        $statusCode = $health['status'] === 'ok' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * Readiness check endpoint
     * 
     * @return JsonResponse
     */
    public function ready(): JsonResponse
    {
        try {
            // Check if migrations are up to date
            DB::connection()->getPdo();
            
            return response()->json([
                'status' => 'ready',
                'timestamp' => now()->toIso8601String()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'not_ready',
                'error' => $e->getMessage(),
                'timestamp' => now()->toIso8601String()
            ], 503);
        }
    }

    /**
     * Liveness check endpoint
     * 
     * @return JsonResponse
     */
    public function live(): JsonResponse
    {
        return response()->json([
            'status' => 'alive',
            'timestamp' => now()->toIso8601String()
        ]);
    }
}
