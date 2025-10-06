@extends('layouts.app')

@section('title', $router->nama_router)

@section('content')
<div class="mb-6">
    <a href="{{ route('routers.index') }}" class="text-gray-400 hover:text-white inline-flex items-center mb-4">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Routers
    </a>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">{{ $router->nama_router }}</h1>
            <p class="text-gray-400 mt-1">Router details and monitoring information</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('routers.edit', $router) }}" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form action="{{ route('routers.destroy', $router) }}" method="POST" class="inline"
                  onsubmit="return confirm('Are you sure you want to delete this router?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
     Router Information 
    <div class="lg:col-span-2 space-y-6">
        <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Router Information</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-400">Router Name</dt>
                    <dd class="mt-1 text-sm text-white font-medium">{{ $router->nama_router }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">IP Address</dt>
                    <dd class="mt-1 text-sm text-white font-mono">{{ $router->ip_address }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Port</dt>
                    <dd class="mt-1 text-sm text-white">{{ $router->port }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Username</dt>
                    <dd class="mt-1 text-sm text-white">{{ $router->username }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Category</dt>
                    <dd class="mt-1">
                        <span class="px-2 py-1 text-xs rounded-full bg-primary/10 text-primary">
                            {{ $router->category->nama_kategori ?? 'N/A' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-400">Status</dt>
                    <dd class="mt-1">
                        @if($router->is_active)
                            <span class="status-badge status-success">Active</span>
                        @else
                            <span class="status-badge status-danger">Inactive</span>
                        @endif
                    </dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-sm text-gray-400">Location</dt>
                    <dd class="mt-1 text-sm text-white">{{ $router->location ?? 'N/A' }}</dd>
                </div>
                @if($router->latitude && $router->longitude)
                <div class="md:col-span-2">
                    <dt class="text-sm text-gray-400">Coordinates</dt>
                    <dd class="mt-1 text-sm text-white font-mono">
                        {{ $router->latitude }}, {{ $router->longitude }}
                    </dd>
                </div>
                @endif
            </dl>
        </div>

         Map 
        @if($router->latitude && $router->longitude)
        <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Location Map</h2>
            <div id="map" class="h-64 rounded-lg bg-dark-lighter"></div>
        </div>
        @endif
    </div>

     Status & Metrics 
    <div class="space-y-6">
        <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Current Status</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-dark-lighter rounded-lg">
                    <span class="text-sm text-gray-400">Connection</span>
                    <span class="status-badge status-success">Online</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-dark-lighter rounded-lg">
                    <span class="text-sm text-gray-400">Last Check</span>
                    <span class="text-sm text-white">{{ $router->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex items-center justify-between p-3 bg-dark-lighter rounded-lg">
                    <span class="text-sm text-gray-400">Created</span>
                    <span class="text-sm text-white">{{ $router->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
            <div class="space-y-2">
                <button onclick="checkRouter()" class="w-full btn-primary justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Check Status Now
                </button>
                <a href="http://{{ $router->ip_address }}" target="_blank" class="w-full btn-secondary justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Open WebFig
                </a>
            </div>
        </div>
    </div>
</div>

@if($router->latitude && $router->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([{{ $router->latitude }}, {{ $router->longitude }}], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    L.marker([{{ $router->latitude }}, {{ $router->longitude }}])
        .addTo(map)
        .bindPopup('<b>{{ $router->nama_router }}</b><br>{{ $router->location }}')
        .openPopup();
</script>
@endif

<script>
function checkRouter() {
    alert('Checking router status... (This would trigger an API call)');
}
</script>
@endsection
