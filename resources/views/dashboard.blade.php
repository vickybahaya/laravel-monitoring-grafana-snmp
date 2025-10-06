@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Network monitoring overview')

@section('content')
<div class="space-y-6">
     Stats Cards 
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
         Total Routers 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#3b82f6]/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <span class="text-2xl font-semibold">{{ $stats['total_routers'] }}</span>
            </div>
            <p class="text-sm text-[#a0a0a0]">Total Routers</p>
        </div>

         Online Routers 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#10b981]/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-semibold text-[#10b981]">{{ $stats['online_routers'] }}</span>
            </div>
            <p class="text-sm text-[#a0a0a0]">Online Routers</p>
        </div>

         Offline Routers 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#ef4444]/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#ef4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-semibold text-[#ef4444]">{{ $stats['offline_routers'] }}</span>
            </div>
            <p class="text-sm text-[#a0a0a0]">Offline Routers</p>
        </div>

         Uptime 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-[#f59e0b]/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-[#f59e0b]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <span class="text-2xl font-semibold text-[#f59e0b]">{{ number_format($stats['uptime_percentage'], 1) }}%</span>
            </div>
            <p class="text-sm text-[#a0a0a0]">Network Uptime</p>
        </div>
    </div>

     Charts Row 
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
         Router Status Chart 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Router Status Distribution</h3>
            <canvas id="statusChart" height="200"></canvas>
        </div>

         Category Distribution 
        <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Routers by Category</h3>
            <canvas id="categoryChart" height="200"></canvas>
        </div>
    </div>

     Recent Activity 
    <div class="bg-[#1a1a1a] border border-[#333] rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Recent Router Status</h3>
            <a href="{{ route('routers.index') }}" class="text-sm text-[#3b82f6] hover:text-[#2563eb]">View all</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-[#333]">
                        <th class="text-left py-3 px-4 text-sm font-medium text-[#a0a0a0]">Router</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-[#a0a0a0]">IP Address</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-[#a0a0a0]">Category</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-[#a0a0a0]">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-[#a0a0a0]">Last Check</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_routers as $router)
                    <tr class="border-b border-[#333] hover:bg-[#2a2a2a] transition-colors">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-[#3b82f6]/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                                <span class="font-medium">{{ $router->nama_router }}</span>
                            </div>
                        </td>
                        <td class="py-3 px-4 text-[#a0a0a0] font-mono text-sm">{{ $router->ip_address }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-1 bg-[#2a2a2a] border border-[#333] rounded text-xs">
                                {{ $router->category->name }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($router->last_status === 'up')
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-[#10b981]/20 text-[#10b981] rounded text-xs font-medium">
                                <span class="w-1.5 h-1.5 bg-[#10b981] rounded-full"></span>
                                Online
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-[#ef4444]/20 text-[#ef4444] rounded text-xs font-medium">
                                <span class="w-1.5 h-1.5 bg-[#ef4444] rounded-full"></span>
                                Offline
                            </span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-[#a0a0a0] text-sm">
                            {{ $router->last_checked_at ? $router->last_checked_at->diffForHumans() : 'Never' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-[#666]">
                            No routers found. <a href="{{ route('routers.create') }}" class="text-[#3b82f6] hover:text-[#2563eb]">Add your first router</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Online', 'Offline'],
        datasets: [{
            data: [{{ $stats['online_routers'] }}, {{ $stats['offline_routers'] }}],
            backgroundColor: ['#10b981', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#a0a0a0',
                    padding: 20,
                    font: {
                        size: 12
                    }
                }
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($category_stats->pluck('name')) !!},
        datasets: [{
            label: 'Routers',
            data: {!! json_encode($category_stats->pluck('count')) !!},
            backgroundColor: '#3b82f6',
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: '#a0a0a0',
                    stepSize: 1
                },
                grid: {
                    color: '#333'
                }
            },
            x: {
                ticks: {
                    color: '#a0a0a0'
                },
                grid: {
                    display: false
                }
            }
        }
    }
});
</script>
@endpush
