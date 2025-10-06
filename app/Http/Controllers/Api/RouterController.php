<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRouterRequest;
use App\Http\Requests\UpdateRouterRequest;
use App\Http\Resources\RouterResource;
use App\Models\Router;
use Illuminate\Http\Request;

class RouterController extends Controller
{
    public function index(Request $request)
    {
        $query = Router::with('category');

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $routers = $query->paginate($request->get('per_page', 15));

        return RouterResource::collection($routers);
    }

    public function store(StoreRouterRequest $request)
    {
        $router = Router::create($request->validated());
        $router->load('category');

        return new RouterResource($router);
    }

    public function show(Router $router)
    {
        $router->load('category', 'statusLogs');
        
        return new RouterResource($router);
    }

    public function update(UpdateRouterRequest $request, Router $router)
    {
        $router->update($request->validated());
        $router->load('category');

        return new RouterResource($router);
    }

    public function destroy(Router $router)
    {
        if (!request()->user()->hasPermission('routers.delete')) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $router->delete();

        return response()->json([
            'message' => 'Router deleted successfully'
        ]);
    }
}
