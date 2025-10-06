<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RouterCategoryResource;
use App\Models\RouterCategory;
use Illuminate\Http\Request;

class RouterCategoryController extends Controller
{
    public function index()
    {
        $categories = RouterCategory::withCount('routers')->get();
        
        return RouterCategoryResource::collection($categories);
    }

    public function store(Request $request)
    {
        if (!$request->user()->hasPermission('categories.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category = RouterCategory::create($validated);

        return new RouterCategoryResource($category);
    }

    public function show(RouterCategory $category)
    {
        $category->loadCount('routers');
        
        return new RouterCategoryResource($category);
    }

    public function update(Request $request, RouterCategory $category)
    {
        if (!$request->user()->hasPermission('categories.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'sometimes|required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $category->update($validated);

        return new RouterCategoryResource($category);
    }

    public function destroy(Request $request, RouterCategory $category)
    {
        if (!$request->user()->hasPermission('categories.manage')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($category->routers()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete category with associated routers'
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully'
        ]);
    }
}
