# Prometheus Integration Guide

## Overview

This system integrates with Prometheus to collect and store metrics from MikroTik routers. Laravel exposes metrics in Prometheus format, which Prometheus scrapes periodically.

## Architecture

\`\`\`
MikroTik Routers → Laravel (collect) → MySQL (store) → Laravel /metrics endpoint → Prometheus (scrape) → Grafana (visualize)
\`\`\`

## Metrics Exposed

### Router Status
- **mikrotik_up**: Router status (1 = up, 0 = down)

### System Metrics
- **mikrotik_cpu_load**: CPU load percentage
- **mikrotik_memory_free**: Free memory in bytes
- **mikrotik_memory_total**: Total memory in bytes
- **mikrotik_disk_free**: Free disk space in bytes
- **mikrotik_disk_total**: Total disk space in bytes
- **mikrotik_uptime_seconds**: Router uptime in seconds

### Labels
Each metric includes the following labels:
- `router_name`: Name of the router
- `router_id`: Database ID
- `ip_address`: Router IP address
- `category`: Router category
- `location`: Physical location

## Setup

### 1. Start Services with Docker Compose

\`\`\`bash
docker-compose up -d
\`\`\`

This will start:
- Laravel application (port 8000)
- MySQL database (port 3306)
- Prometheus (port 9090)
- Grafana (port 3000)
- Node Exporter (port 9100)
- Laravel Scheduler (background)

### 2. Run Migrations

\`\`\`bash
docker-compose exec laravel-app php artisan migrate
docker-compose exec laravel-app php artisan db:seed --class=RolePermissionSeeder
\`\`\`

### 3. Check Router Status

Manual check:
\`\`\`bash
docker-compose exec laravel-app php artisan routers:check
\`\`\`

Check specific router:
\`\`\`bash
docker-compose exec laravel-app php artisan routers:check --router-id=1
\`\`\`

### 4. View Metrics

Access the metrics endpoint:
\`\`\`
http://localhost:8000/api/metrics
\`\`\`

### 5. Access Prometheus

Open Prometheus UI:
\`\`\`
http://localhost:9090
\`\`\`

Example queries:
\`\`\`promql
# All routers status
mikrotik_up

# Routers that are down
mikrotik_up == 0

# CPU load for specific router
mikrotik_cpu_load{router_name="Router1"}

# Memory usage percentage
(1 - (mikrotik_memory_free / mikrotik_memory_total)) * 100

# Average CPU load by category
avg(mikrotik_cpu_load) by (category)
\`\`\`

## Automated Monitoring

The Laravel scheduler automatically checks all routers every 5 minutes. This is configured in `app/Console/Kernel.php`.

To run the scheduler:
\`\`\`bash
php artisan schedule:work
\`\`\`

Or use the scheduler container in docker-compose (already configured).

## Alerts

Alert rules are defined in `alerts.yml`:

1. **RouterDown**: Triggers when router is down for 5+ minutes
2. **HighCPULoad**: Triggers when CPU > 80% for 10+ minutes
3. **LowMemory**: Triggers when free memory < 10% for 10+ minutes
4. **LowDiskSpace**: Triggers when free disk < 20% for 30+ minutes
5. **RouterRestarted**: Triggers when uptime < 10 minutes

## Troubleshooting

### Metrics not showing in Prometheus

1. Check if Laravel app is running:
   \`\`\`bash
   curl http://localhost:8000/api/metrics
   \`\`\`

2. Check Prometheus targets:
   \`\`\`
   http://localhost:9090/targets
   \`\`\`

3. Verify routers have been checked:
   \`\`\`bash
   docker-compose exec laravel-app php artisan routers:check
   \`\`\`

### No data in metrics

Make sure you have:
1. Created routers in the database
2. Run the check command at least once
3. Routers are marked as active (`is_active = true`)

## Production Deployment

For production:

1. Use proper database credentials
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Configure proper Prometheus retention
4. Setup Alertmanager for notifications
5. Use reverse proxy (nginx) for Laravel
6. Enable HTTPS
7. Configure firewall rules
