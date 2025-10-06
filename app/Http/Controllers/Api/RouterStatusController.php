<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Router;
use App\Models\RouterStatusLog;
use App\Services\SNMPService;
use Illuminate\Http\Request;

class RouterStatusController extends Controller
{
    private SNMPService $snmp;

    public function __construct(SNMPService $snmp)
    {
        $this->snmp = $snmp;
    }

    public function check(Router $router)
    {
        if (!request()->user()->hasPermission('monitoring.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $status = 'down';
        $message = null;
        $metrics = null;

        try {
            $connected = $this->snmp->checkConnection(
                $router->ip_address,
                $router->snmp_community,
                $router->snmp_version,
                $router->snmp_port ?? 161
            );

            if ($connected) {
                $status = 'up';
                
                $systemInfo = $this->snmp->getSystemInfo(
                    $router->ip_address,
                    $router->snmp_community,
                    $router->snmp_version,
                    $router->snmp_port ?? 161
                );
                
                $resources = $this->snmp->getResources(
                    $router->ip_address,
                    $router->snmp_community,
                    $router->snmp_version,
                    $router->snmp_port ?? 161
                );
                
                $metrics = array_merge($systemInfo, $resources);
            } else {
                $message = 'SNMP connection failed';
            }
        } catch (\Exception $e) {
            $status = 'down';
            $message = $e->getMessage();
        }

        // Update router status
        $router->update([
            'status' => $status,
            'last_checked_at' => now(),
        ]);

        // Log status
        RouterStatusLog::create([
            'router_id' => $router->id,
            'status' => $status,
            'message' => $message,
            'metrics' => $metrics,
            'checked_at' => now(),
        ]);

        return response()->json([
            'router_id' => $router->id,
            'status' => $status,
            'message' => $message,
            'metrics' => $metrics,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    public function checkAll()
    {
        if (!request()->user()->hasPermission('monitoring.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $routers = Router::where('is_active', true)->get();
        $results = [];

        foreach ($routers as $router) {
            $status = 'down';
            $metrics = null;

            try {
                $connected = $this->snmp->checkConnection(
                    $router->ip_address,
                    $router->snmp_community,
                    $router->snmp_version,
                    $router->snmp_port ?? 161
                );

                if ($connected) {
                    $status = 'up';
                    $resources = $this->snmp->getResources(
                        $router->ip_address,
                        $router->snmp_community,
                        $router->snmp_version,
                        $router->snmp_port ?? 161
                    );
                    
                    $metrics = $resources;
                }
            } catch (\Exception $e) {
                $status = 'down';
            }

            $router->update([
                'status' => $status,
                'last_checked_at' => now(),
            ]);

            RouterStatusLog::create([
                'router_id' => $router->id,
                'status' => $status,
                'metrics' => $metrics,
                'checked_at' => now(),
            ]);

            $results[] = [
                'router_id' => $router->id,
                'name' => $router->name,
                'status' => $status,
            ];
        }

        return response()->json([
            'checked_count' => count($results),
            'results' => $results,
        ]);
    }

    public function history(Router $router, Request $request)
    {
        if (!request()->user()->hasPermission('monitoring.view')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = $router->statusLogs()->orderBy('checked_at', 'desc');

        if ($request->has('from')) {
            $query->where('checked_at', '>=', $request->from);
        }

        if ($request->has('to')) {
            $query->where('checked_at', '<=', $request->to);
        }

        $logs = $query->paginate($request->get('per_page', 50));

        return response()->json($logs);
    }
}
