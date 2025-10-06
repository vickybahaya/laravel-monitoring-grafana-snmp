<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PrometheusService;

class MetricsController extends Controller
{
    private PrometheusService $prometheus;

    public function __construct(PrometheusService $prometheus)
    {
        $this->prometheus = $prometheus;
    }

    public function index()
    {
        $metrics = $this->prometheus->generateMetrics();
        
        return response($metrics, 200)
            ->header('Content-Type', 'text/plain; version=0.0.4');
    }
}
