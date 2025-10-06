# Grafana Configuration Guide

## Overview

Grafana is configured to automatically load dashboards and connect to Prometheus as a data source. All configurations are provisioned automatically when using Docker Compose.

## Access Grafana

URL: `http://localhost:3000`

Default credentials:
- Username: `admin`
- Password: `admin`

You will be prompted to change the password on first login.

## Available Dashboards

### 1. Router Monitoring - Overview
**UID**: `router-overview`

Main dashboard showing:
- Total routers count
- Online/offline routers
- Uptime percentage
- Router status over time
- Status table with all routers
- Routers distribution by category
- CPU load trends
- Memory usage trends

**Best for**: Quick overview of entire network health

### 2. Router Monitoring - Details
**UID**: `router-details`

Detailed view for individual routers:
- Current status (UP/DOWN)
- CPU load gauge
- Free memory gauge
- Uptime counter
- Historical CPU load
- Historical memory usage
- Historical disk usage
- Uptime history

**Features**:
- Router selector dropdown (top of dashboard)
- Select any router to view detailed metrics
- 30-second auto-refresh

**Best for**: Deep dive into specific router performance

### 3. Network Performance Overview
**UID**: `network-performance`

Network-wide performance metrics:
- Average CPU load by category
- Average free memory by category
- Performance summary table sorted by CPU load

**Best for**: Comparing performance across router categories

## Dashboard Features

### Auto-Refresh
All dashboards auto-refresh every 30 seconds to show real-time data.

To change refresh interval:
1. Click the refresh dropdown (top right)
2. Select desired interval (10s, 30s, 1m, 5m, etc.)

### Time Range
Default time range: Last 6 hours

To change:
1. Click time range selector (top right)
2. Choose from presets or set custom range
3. Options: Last 5m, 15m, 1h, 6h, 12h, 24h, 7d, 30d

### Variables
Some dashboards use variables for filtering:

**Router Details Dashboard**:
- `$router` - Select specific router to view

Variables appear as dropdowns at the top of the dashboard.

## Creating Custom Dashboards

### Using Prometheus Data Source

1. Click "+" → "Dashboard"
2. Click "Add new panel"
3. Select "Prometheus" as data source
4. Enter PromQL query

### Example Queries

**Router Status**:
\`\`\`promql
mikrotik_up
\`\`\`

**Routers Down**:
\`\`\`promql
mikrotik_up == 0
\`\`\`

**CPU Load Above 80%**:
\`\`\`promql
mikrotik_cpu_load > 80
\`\`\`

**Memory Usage Percentage**:
\`\`\`promql
(1 - (mikrotik_memory_free / mikrotik_memory_total)) * 100
\`\`\`

**Average CPU by Category**:
\`\`\`promql
avg(mikrotik_cpu_load) by (category)
\`\`\`

**Uptime in Days**:
\`\`\`promql
mikrotik_uptime_seconds / 86400
\`\`\`

## Alerting in Grafana

### Creating Alert Rules

1. Open any dashboard panel
2. Click panel title → "Edit"
3. Go to "Alert" tab
4. Click "Create Alert"
5. Configure conditions
6. Set notification channel

### Example Alert: Router Down

**Condition**:
\`\`\`
WHEN last() OF query(A, 5m, now) IS BELOW 1
\`\`\`

**Query A**:
\`\`\`promql
mikrotik_up{router_name="Router1"}
\`\`\`

This alerts when router has been down for 5 minutes.

### Notification Channels

Configure in: Configuration → Notification channels

Supported:
- Email
- Slack
- Telegram
- Discord
- Webhook
- PagerDuty
- And many more

## Exporting Dashboards

### Export as JSON

1. Open dashboard
2. Click dashboard settings (gear icon)
3. Click "JSON Model"
4. Copy JSON or click "Save to file"

### Import Dashboard

1. Click "+" → "Import"
2. Paste JSON or upload file
3. Select Prometheus data source
4. Click "Import"

## Dashboard Provisioning

Dashboards are automatically provisioned from:
\`\`\`
grafana/dashboards/*.json
\`\`\`

To add new dashboard:
1. Create JSON file in `grafana/dashboards/`
2. Restart Grafana container
3. Dashboard appears automatically

Configuration:
\`\`\`
grafana/provisioning/dashboards/default.yml
\`\`\`

## Data Source Configuration

Prometheus is automatically configured as default data source.

Configuration file:
\`\`\`
grafana/provisioning/datasources/prometheus.yml
\`\`\`

To add additional data sources:
1. Create YAML file in `grafana/provisioning/datasources/`
2. Restart Grafana

## Plugins

Pre-installed plugins (via docker-compose.yml):
- grafana-worldmap-panel (for maps)
- grafana-piechart-panel (for pie charts)

To install more plugins:
1. Add to `GF_INSTALL_PLUGINS` in docker-compose.yml
2. Restart Grafana container

Example:
\`\`\`yaml
environment:
  - GF_INSTALL_PLUGINS=grafana-worldmap-panel,grafana-clock-panel
\`\`\`

## Troubleshooting

### Dashboard Not Loading

1. Check Prometheus is running:
   \`\`\`bash
   curl http://localhost:9090/-/healthy
   \`\`\`

2. Check data source connection:
   - Go to Configuration → Data Sources
   - Click Prometheus
   - Click "Test" button

3. Verify metrics are available:
   \`\`\`bash
   curl http://localhost:8000/api/metrics
   \`\`\`

### No Data in Panels

1. Check time range (top right)
2. Verify routers have been checked:
   \`\`\`bash
   docker-compose exec laravel-app php artisan routers:check
   \`\`\`

3. Check Prometheus targets:
   \`\`\`
   http://localhost:9090/targets
   \`\`\`

### Permission Denied

Reset admin password:
\`\`\`bash
docker-compose exec grafana grafana-cli admin reset-admin-password newpassword
\`\`\`

## Best Practices

1. **Use Variables**: Create reusable dashboards with variables
2. **Set Alerts**: Configure alerts for critical metrics
3. **Organize**: Use folders to organize dashboards
4. **Document**: Add text panels to explain dashboard purpose
5. **Share**: Export and version control dashboard JSON files
6. **Optimize**: Use appropriate time ranges and refresh intervals
7. **Test**: Verify alerts trigger correctly before relying on them
