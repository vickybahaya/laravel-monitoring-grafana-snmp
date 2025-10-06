<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RouterController extends Controller
{
    public function index(Request $request)
    {
        $query = Router::with('category');

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_router', 'like', '%' . $request->search . '%')
                  ->orWhere('ip_address', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('router_category_id', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $routers = $query->latest()->paginate(15);
        $categories = RouterCategory::all();

        return view('routers.index', compact('routers', 'categories'));
    }

    public function create()
    {
        $categories = RouterCategory::all();
        return view('routers.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_router' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers,ip_address',
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'router_category_id' => 'required|exists:router_categories,id',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = encrypt($validated['password']);
        $validated['is_active'] = $request->has('is_active');

        Router::create($validated);

        return redirect()->route('routers.index')
            ->with('success', 'Router created successfully.');
    }

    public function show(Router $router)
    {
        $router->load('category');
        return view('routers.show', compact('router'));
    }

    public function edit(Router $router)
    {
        $categories = RouterCategory::all();
        return view('routers.edit', compact('router', 'categories'));
    }

    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'nama_router' => 'required|string|max:255',
            'ip_address' => 'required|ip|unique:routers,ip_address,' . $router->id,
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'port' => 'required|integer|min:1|max:65535',
            'router_category_id' => 'required|exists:router_categories,id',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = encrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->has('is_active');

        $router->update($validated);

        return redirect()->route('routers.index')
            ->with('success', 'Router updated successfully.');
    }

    public function destroy(Router $router)
    {
        $router->delete();

        return redirect()->route('routers.index')
            ->with('success', 'Router deleted successfully.');
    }
}
