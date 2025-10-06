# Installation Guide - Laravel Router Monitoring System

## Prerequisites

### System Requirements
- PHP >= 8.1
- Composer
- MySQL >= 8.0 or PostgreSQL >= 13
- SNMP PHP Extension
- Docker & Docker Compose (optional, for containerized deployment)

### Install PHP SNMP Extension

**Ubuntu/Debian:**
\`\`\`bash
sudo apt-get update
sudo apt-get install php-snmp snmp snmp-mibs-downloader
\`\`\`

**CentOS/RHEL:**
\`\`\`bash
sudo yum install php-snmp net-snmp net-snmp-utils
\`\`\`

**macOS:**
\`\`\`bash
brew install net-snmp
pecl install snmp
\`\`\`

**Verify installation:**
\`\`\`bash
php -m | grep snmp
\`\`\`

## Installation Methods

### Method 1: Fresh Laravel 10 Installation (Recommended)

This method installs Laravel 10 fresh and adds custom files.

#### Step 1: Create New Laravel Project

\`\`\`bash
# Create new Laravel 10 project
composer create-project laravel/laravel:^10.0 router-monitoring

# Navigate to project directory
cd router-monitoring
\`\`\`

#### Step 2: Copy Custom Files

Copy these custom files from this repository to your Laravel project:

**Application Files:**
\`\`\`
app/
├── Console/
│   ├── Commands/
│   │   └── CheckRoutersCommand.php
│   └── Kernel.php
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── HealthController.php
│   │   │   ├── MetricsController.php
│   │   │   ├── RouterCategoryController.php
│   │   │   ├── RouterController.php
│   │   │   ├── RouterMapController.php
│   │   │   └── RouterStatusController.php
│   │   └── Web/
│   │       ├── AuthController.php
│   │       ├── DashboardController.php
│   │       └── RouterController.php
│   ├── Middleware/
│   │   ├── Authenticate.php
│   │   └── CheckPermission.php
│   ├── Requests/
│   │   ├── StoreRouterRequest.php
│   │   └── UpdateRouterRequest.php
│   └── Resources/
│       ├── RouterCategoryResource.php
│       └── RouterResource.php
├── Models/
│   ├── Permission.php
│   ├── Role.php
│   ├── Router.php
│   ├── RouterCategory.php
│   ├── RouterStatusLog.php
│   └── User.php
└── Services/
    ├── PrometheusService.php
    └── SNMPService.php
\`\`\`

**Database Files:**
\`\`\`
database/
├── migrations/
│   ├── 2024_01_01_000001_create_roles_table.php
│   ├── 2024_01_01_000002_create_permissions_table.php
│   ├── 2024_01_01_000003_create_role_permission_table.php
│   ├── 2024_01_01_000004_create_users_table.php
│   ├── 2024_01_01_000005_create_router_categories_table.php
│   ├── 2024_01_01_000006_create_routers_table.php
│   ├── 2024_01_01_000007_create_router_status_logs_table.php
│   ├── 2024_01_01_000008_add_coordinates_to_routers_table.php
│   └── 2024_01_08_000000_modify_routers_table_for_snmp.php
└── seeders/
    ├── DummyRouterSeeder.php
    └── RolePermissionSeeder.php
\`\`\`

**Views:**
\`\`\`
resources/views/
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── layouts/
│   ├── app.blade.php
│   └── guest.blade.php
├── routers/
│   ├── create.blade.php
│   ├── edit.blade.php
│   ├── index.blade.php
│   └── show.blade.php
└── dashboard.blade.php
\`\`\`

**Routes:**
\`\`\`
routes/
├── api.php
└── web.php
\`\`\`

**Configuration & Docker:**
\`\`\`
├── docker-compose.yml
├── Dockerfile
├── .dockerignore
├── prometheus.yml
├── alerts.yml
└── grafana/
    ├── provisioning/
    │   ├── datasources/
    │   │   └── prometheus.yml
    │   └── dashboards/
    │       └── default.yml
    └── dashboards/
        ├── router-overview.json
        ├── router-details.json
        ├── router-map.json
        └── network-performance.json
\`\`\`

**Public Assets:**
\`\`\`
public/
└── css/
    └── app.css
\`\`\`

#### Step 3: Install Dependencies

\`\`\`bash
# Install PHP dependencies
composer install

# Install additional packages
composer require php-snmp/php-snmp
composer require laravel/sanctum

# Publish Sanctum config
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
\`\`\`

#### Step 4: Environment Configuration

\`\`\`bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
\`\`\`

Edit `.env` file:
\`\`\`env
APP_NAME="Router Monitoring"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=your_password

# SNMP Configuration
SNMP_DEFAULT_COMMUNITY=public
SNMP_DEFAULT_VERSION=2c
SNMP_TIMEOUT=5
SNMP_RETRIES=3

# Prometheus
PROMETHEUS_ENABLED=true
\`\`\`

#### Step 5: Database Setup

\`\`\`bash
# Create database
mysql -u root -p -e "CREATE DATABASE router_monitoring CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
php artisan migrate

# Seed initial data (roles, permissions, admin user)
php artisan db:seed --class=RolePermissionSeeder

# Optional: Seed dummy routers for testing
php artisan db:seed --class=DummyRouterSeeder
\`\`\`

#### Step 6: Set Permissions

\`\`\`bash
# Set storage permissions
chmod -R 775 storage bootstrap/cache

# Set ownership (if on Linux server)
sudo chown -R www-data:www-data storage bootstrap/cache
\`\`\`

#### Step 7: Start Application

**Development:**
\`\`\`bash
php artisan serve
\`\`\`

**Production (with Nginx):**
See `docs/DEPLOYMENT.md` for production setup.

---

### Method 2: Docker Compose (All-in-One)

This method runs everything in Docker containers.

#### Step 1: Clone/Copy Project Files

Ensure you have all project files including `docker-compose.yml`.

#### Step 2: Configure Environment

\`\`\`bash
cp .env.example .env
\`\`\`

Edit `.env` for Docker:
\`\`\`env
DB_HOST=mysql
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=secret
\`\`\`

#### Step 3: Build and Start Containers

\`\`\`bash
# Build containers
docker-compose build

# Start all services
docker-compose up -d

# Wait for MySQL to be ready (about 15 seconds)
sleep 15

# Run migrations
docker-compose exec laravel-app php artisan migrate --seed
\`\`\`

#### Step 4: Access Services

- **Laravel Web UI:** http://localhost:8000
- **Grafana:** http://localhost:3000 (admin/admin)
- **Prometheus:** http://localhost:9090
- **MySQL:** localhost:3306

---

### Method 3: Existing Laravel 10 Project

If you already have a Laravel 10 project:

#### Step 1: Copy Custom Files

Copy all custom files listed in Method 1 to your existing project.

#### Step 2: Install Dependencies

\`\`\`bash
composer require php-snmp/php-snmp
composer require laravel/sanctum
\`\`\`

#### Step 3: Run Migrations

\`\`\`bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
\`\`\`

#### Step 4: Update Routes

Merge `routes/api.php` and `routes/web.php` with your existing routes.

---

## Post-Installation

### Default Admin Account

After seeding, you can login with:
- **Email:** admin@example.com
- **Password:** password

**Change this immediately in production!**

### Setup Scheduler (Required)

The system needs Laravel scheduler to check routers periodically.

**Add to crontab:**
\`\`\`bash
crontab -e
\`\`\`

Add this line:
\`\`\`
* * * * * cd /path/to/router-monitoring && php artisan schedule:run >> /dev/null 2>&1
\`\`\`

Or use supervisor for the scheduler worker:
\`\`\`ini
[program:router-monitoring-scheduler]
command=php /path/to/router-monitoring/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/router-monitoring-scheduler.log
\`\`\`

### Manual Router Check

Test router checking manually:
\`\`\`bash
php artisan routers:check
\`\`\`

### Verify Metrics Endpoint

\`\`\`bash
curl http://localhost:8000/api/metrics
\`\`\`

Should return Prometheus-formatted metrics.

### Configure Prometheus

If using external Prometheus, add to `prometheus.yml`:
\`\`\`yaml
scrape_configs:
  - job_name: 'laravel-metrics'
    scrape_interval: 30s
    static_configs:
      - targets: ['your-laravel-host:8000']
    metrics_path: '/api/metrics'
\`\`\`

### Import Grafana Dashboards

1. Login to Grafana
2. Go to Dashboards → Import
3. Upload JSON files from `grafana/dashboards/`
4. Select Prometheus data source
5. Import

---

## Troubleshooting

### SNMP Extension Not Found

\`\`\`bash
# Check if SNMP is installed
php -m | grep snmp

# If not, install it
sudo apt-get install php-snmp
sudo systemctl restart php-fpm  # or apache2
\`\`\`

### Permission Denied Errors

\`\`\`bash
chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
\`\`\`

### Database Connection Failed

- Check `.env` database credentials
- Ensure MySQL is running
- Verify database exists
- Check firewall rules

### Metrics Endpoint Returns 404

- Clear route cache: `php artisan route:clear`
- Check `routes/api.php` is properly configured
- Verify web server configuration

### Routers Not Being Checked

- Verify scheduler is running: `php artisan schedule:list`
- Check crontab is configured
- Run manual check: `php artisan routers:check`
- Check logs: `tail -f storage/logs/laravel.log`

### Docker Containers Won't Start

\`\`\`bash
# Check logs
docker-compose logs

# Rebuild containers
docker-compose down
docker-compose up -d --build --force-recreate
\`\`\`

---

## Next Steps

1. Add your routers via Web UI or API
2. Configure SNMP on your routers
3. Customize Grafana dashboards
4. Setup alerting rules
5. Configure backup strategy
6. Read `docs/DEPLOYMENT.md` for production deployment
7. Read `docs/API.md` for API documentation

---

## Support

For issues and questions:
1. Check logs: `storage/logs/laravel.log`
2. Run troubleshoot script: `bash scripts/troubleshoot.sh`
3. Check documentation in `docs/` folder
