<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_routers' => Router::count(),
            'active_routers' => Router::where('is_active', true)->count(),
            'total_categories' => RouterCategory::count(),
            'total_users' => User::count(),
        ];

        $routers_by_category = RouterCategory::withCount('routers')->get();
        
        $recent_routers = Router::with('category')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact('stats', 'routers_by_category', 'recent_routers'));
    }
}
