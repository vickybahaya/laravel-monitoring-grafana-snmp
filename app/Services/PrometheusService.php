<?php

namespace App\Services;

use App\Models\Router;
use Illuminate\Support\Collection;

class PrometheusService
{
    public function generateMetrics(): string
    {
        $metrics = [];
        
        // Add header
        $metrics[] = "# HELP mikrotik_up Router status (1 = up, 0 = down)";
        $metrics[] = "# TYPE mikrotik_up gauge";
        
        $metrics[] = "# HELP mikrotik_cpu_load CPU load percentage";
        $metrics[] = "# TYPE mikrotik_cpu_load gauge";
        
        $metrics[] = "# HELP mikrotik_memory_free Free memory in bytes";
        $metrics[] = "# TYPE mikrotik_memory_free gauge";
        
        $metrics[] = "# HELP mikrotik_memory_total Total memory in bytes";
        $metrics[] = "# TYPE mikrotik_memory_total gauge";
        
        $metrics[] = "# HELP mikrotik_disk_free Free disk space in bytes";
        $metrics[] = "# TYPE mikrotik_disk_free gauge";
        
        $metrics[] = "# HELP mikrotik_disk_total Total disk space in bytes";
        $metrics[] = "# TYPE mikrotik_disk_total gauge";
        
        $metrics[] = "# HELP mikrotik_uptime_seconds Router uptime in seconds";
        $metrics[] = "# TYPE mikrotik_uptime_seconds counter";
        
        // Get all active routers with their latest status
        $routers = Router::with(['category', 'statusLogs' => function ($query) {
            $query->latest('checked_at')->limit(1);
        }])->where('is_active', true)->get();
        
        foreach ($routers as $router) {
            $labels = $this->formatLabels([
                'router_name' => $router->name,
                'router_id' => $router->id,
                'ip_address' => $router->ip_address,
                'category' => $router->category->name ?? 'unknown',
                'location' => $router->location ?? 'unknown',
            ]);
            
            // Router status
            $statusValue = $router->status === 'up' ? 1 : 0;
            $metrics[] = "mikrotik_up{$labels} $statusValue";
            
            // Get latest metrics from status log
            $latestLog = $router->statusLogs->first();
            
            if ($latestLog && $latestLog->metrics) {
                $metricsData = $latestLog->metrics;
                
                // CPU Load
                if (isset($metricsData['cpu_load'])) {
                    $cpuLoad = (int) $metricsData['cpu_load'];
                    $metrics[] = "mikrotik_cpu_load{$labels} $cpuLoad";
                }
                
                // Memory
                if (isset($metricsData['free_memory'])) {
                    $freeMemory = (int) $metricsData['free_memory'];
                    $metrics[] = "mikrotik_memory_free{$labels} $freeMemory";
                }
                
                if (isset($metricsData['total_memory'])) {
                    $totalMemory = (int) $metricsData['total_memory'];
                    $metrics[] = "mikrotik_memory_total{$labels} $totalMemory";
                }
                
                // Disk
                if (isset($metricsData['free_hdd_space'])) {
                    $freeDisk = (int) $metricsData['free_hdd_space'];
                    $metrics[] = "mikrotik_disk_free{$labels} $freeDisk";
                }
                
                if (isset($metricsData['total_hdd_space'])) {
                    $totalDisk = (int) $metricsData['total_hdd_space'];
                    $metrics[] = "mikrotik_disk_total{$labels} $totalDisk";
                }
                
                // Uptime
                if (isset($metricsData['uptime'])) {
                    $uptimeSeconds = $this->parseUptime($metricsData['uptime']);
                    $metrics[] = "mikrotik_uptime_seconds{$labels} $uptimeSeconds";
                }
            }
        }
        
        return implode("\n", $metrics) . "\n";
    }
    
    private function formatLabels(array $labels): string
    {
        $formatted = [];
        foreach ($labels as $key => $value) {
            $escapedValue = $this->escapeLabel($value);
            $formatted[] = "$key=\"$escapedValue\"";
        }
        return '{' . implode(',', $formatted) . '}';
    }
    
    private function escapeLabel(string $value): string
    {
        return str_replace(['"', '\\', "\n"], ['\\"', '\\\\', '\\n'], $value);
    }
    
    private function parseUptime(string $uptime): int
    {
        // Parse MikroTik uptime format: 1w2d3h4m5s
        $seconds = 0;
        
        if (preg_match('/(\d+)w/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 604800; // weeks
        }
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 86400; // days
        }
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 3600; // hours
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 60; // minutes
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds += (int) $matches[1]; // seconds
        }
        
        return $seconds;
    }
}
