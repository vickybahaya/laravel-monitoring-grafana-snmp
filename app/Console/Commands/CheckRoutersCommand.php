<?php

namespace App\Console\Commands;

use App\Models\Router;
use App\Models\RouterStatusLog;
use App\Services\SNMPService;
use Illuminate\Console\Command;

class CheckRoutersCommand extends Command
{
    protected $signature = 'routers:check {--router-id=}';
    protected $description = 'Check status of all routers or specific router via SNMP';

    private SNMPService $snmp;

    public function __construct(SNMPService $snmp)
    {
        parent::__construct();
        $this->snmp = $snmp;
    }

    public function handle()
    {
        $routerId = $this->option('router-id');
        
        if ($routerId) {
            $routers = Router::where('id', $routerId)->where('is_active', true)->get();
        } else {
            $routers = Router::where('is_active', true)->get();
        }

        if ($routers->isEmpty()) {
            $this->error('No active routers found');
            return 1;
        }

        $this->info("Checking {$routers->count()} router(s) via SNMP...");
        
        $bar = $this->output->createProgressBar($routers->count());
        $bar->start();

        $upCount = 0;
        $downCount = 0;

        foreach ($routers as $router) {
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
                    $upCount++;
                    
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
                    $downCount++;
                    $message = 'SNMP connection failed';
                }
            } catch (\Exception $e) {
                $downCount++;
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

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        
        $this->info("Check completed!");
        $this->info("Up: {$upCount} | Down: {$downCount}");

        return 0;
    }
}
