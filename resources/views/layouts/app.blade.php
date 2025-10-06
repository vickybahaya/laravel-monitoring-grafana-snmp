<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Router Monitoring') - Network Management</title>
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    @stack('styles')
</head>
<body class="bg-[#0a0a0a] text-white">
    <div x-data="{ sidebarOpen: true }" class="flex h-screen overflow-hidden">
         Sidebar 
        <aside 
            :class="sidebarOpen ? 'w-64' : 'w-20'" 
            class="bg-[#1a1a1a] border-r border-[#333] transition-all duration-300 flex flex-col"
        >
             Logo 
            <div class="p-6 border-b border-[#333] flex items-center justify-between">
                <div x-show="sidebarOpen" class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-[#3b82f6] rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <span class="font-semibold text-lg">NetMonitor</span>
                </div>
                <button @click="sidebarOpen = !sidebarOpen" class="text-[#a0a0a0] hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

             Navigation 
            <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>

                <a href="{{ route('routers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('routers.*') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    <span x-show="sidebarOpen">Routers</span>
                </a>

                <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('categories.*') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                    <span x-show="sidebarOpen">Categories</span>
                </a>

                <a href="{{ route('maps.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('maps.*') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                    </svg>
                    <span x-show="sidebarOpen">Maps</span>
                </a>

                @can('manage-users')
                <div class="pt-4 mt-4 border-t border-[#333]">
                    <p x-show="sidebarOpen" class="px-3 mb-2 text-xs font-semibold text-[#666] uppercase">Administration</p>
                    
                    <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('users.*') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span x-show="sidebarOpen">Users</span>
                    </a>

                    <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ request()->routeIs('roles.*') ? 'bg-[#3b82f6] text-white' : 'text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span x-show="sidebarOpen">Roles</span>
                    </a>
                </div>
                @endcan

                <div class="pt-4 mt-4 border-t border-[#333]">
                    <a href="http://localhost:3000" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded-lg text-[#a0a0a0] hover:bg-[#2a2a2a] hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span x-show="sidebarOpen">Grafana</span>
                    </a>
                </div>
            </nav>

             User Menu 
            <div class="p-4 border-t border-[#333]" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" class="flex items-center gap-3 w-full px-3 py-2 rounded-lg hover:bg-[#2a2a2a] transition-colors">
                        <div class="w-8 h-8 bg-[#3b82f6] rounded-full flex items-center justify-center">
                            <span class="text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                        <div x-show="sidebarOpen" class="flex-1 text-left">
                            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#666]">{{ auth()->user()->role->name }}</p>
                        </div>
                    </button>

                    <div x-show="open" @click.away="open = false" class="absolute bottom-full left-0 right-0 mb-2 bg-[#2a2a2a] border border-[#333] rounded-lg shadow-lg overflow-hidden">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-[#333] transition-colors">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm hover:bg-[#333] transition-colors text-[#ef4444]">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

         Main Content 
        <div class="flex-1 flex flex-col overflow-hidden">
             Header 
            <header class="bg-[#1a1a1a] border-b border-[#333] px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">@yield('page-title', 'Dashboard')</h1>
                        <p class="text-sm text-[#a0a0a0] mt-1">@yield('page-description', 'Welcome back')</p>
                    </div>

                    <div class="flex items-center gap-4">
                         Notifications 
                        <button class="relative p-2 text-[#a0a0a0] hover:text-white hover:bg-[#2a2a2a] rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-[#ef4444] rounded-full"></span>
                        </button>

                         Search 
                        <div class="relative">
                            <input type="text" placeholder="Search..." class="bg-[#2a2a2a] border border-[#333] rounded-lg px-4 py-2 pl-10 text-sm focus:outline-none focus:border-[#3b82f6] w-64">
                            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-[#666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </header>

             Page Content 
            <main class="flex-1 overflow-y-auto p-6">
                @if(session('success'))
                <div class="mb-6 bg-[#10b981]/20 border border-[#10b981] text-[#10b981] px-4 py-3 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-[#ef4444]/20 border border-[#ef4444] text-[#ef4444] px-4 py-3 rounded-lg flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
