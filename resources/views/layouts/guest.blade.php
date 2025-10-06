<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') - Network Management</title>
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-[#0a0a0a] text-white">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="w-full max-w-md">
             Logo 
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-[#3b82f6] rounded-lg flex items-center justify-center">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <span class="text-2xl font-semibold">NetMonitor</span>
                </div>
                <p class="text-[#a0a0a0]">Network Monitoring & Management System</p>
            </div>

             Card 
            <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-8">
                @yield('content')
            </div>

             Footer 
            <p class="text-center text-sm text-[#666] mt-6">
                &copy; {{ date('Y') }} NetMonitor. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
