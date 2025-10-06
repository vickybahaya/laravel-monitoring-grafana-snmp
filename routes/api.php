<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\MetricsController;
use App\Http\Controllers\Api\RouterController;
use App\Http\Controllers\Api\RouterCategoryController;
use App\Http\Controllers\Api\RouterMapController;
use App\Http\Controllers\Api\RouterStatusController;
use Illuminate\Support\Facades\Route;

// Health check routes (public)
Route::get('/health', [HealthController::class, 'index']);
Route::get('/health/ready', [HealthController::class, 'ready']);
Route::get('/health/live', [HealthController::class, 'live']);

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/metrics', [MetricsController::class, 'index']);

Route::get('/map/routers', [RouterMapController::class, 'index']);
Route::get('/map/geojson', [RouterMapController::class, 'geojson']);
Route::get('/map/metrics', [RouterMapController::class, 'metrics']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Routers
    Route::middleware('permission:routers.view')->group(function () {
        Route::get('/routers', [RouterController::class, 'index']);
        Route::get('/routers/{router}', [RouterController::class, 'show']);
    });
    
    Route::middleware('permission:routers.create')->group(function () {
        Route::post('/routers', [RouterController::class, 'store']);
    });
    
    Route::middleware('permission:routers.edit')->group(function () {
        Route::put('/routers/{router}', [RouterController::class, 'update']);
        Route::patch('/routers/{router}', [RouterController::class, 'update']);
    });
    
    Route::middleware('permission:routers.delete')->group(function () {
        Route::delete('/routers/{router}', [RouterController::class, 'destroy']);
    });
    
    // Router Categories
    Route::get('/categories', [RouterCategoryController::class, 'index']);
    Route::get('/categories/{category}', [RouterCategoryController::class, 'show']);
    Route::post('/categories', [RouterCategoryController::class, 'store']);
    Route::put('/categories/{category}', [RouterCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [RouterCategoryController::class, 'destroy']);
    
    // Router Status Monitoring
    Route::get('/routers/{router}/check', [RouterStatusController::class, 'check']);
    Route::post('/routers/check-all', [RouterStatusController::class, 'checkAll']);
    Route::get('/routers/{router}/history', [RouterStatusController::class, 'history']);
});
