# File Manifest - Custom vs Laravel Core

This document lists which files are custom (created for this project) and which are Laravel core files.

## Custom Files (Copy These)

### Application Logic

\`\`\`
app/
├── Console/
│   ├── Commands/
│   │   └── CheckRoutersCommand.php          [CUSTOM]
│   └── Kernel.php                            [MODIFIED - added scheduler]
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php           [CUSTOM]
│   │   │   ├── HealthController.php         [CUSTOM]
│   │   │   ├── MetricsController.php        [CUSTOM]
│   │   │   ├── RouterCategoryController.php [CUSTOM]
│   │   │   ├── RouterController.php         [CUSTOM]
│   │   │   ├── RouterMapController.php      [CUSTOM]
│   │   │   └── RouterStatusController.php   [CUSTOM]
│   │   └── Web/
│   │       ├── AuthController.php           [CUSTOM]
│   │       ├── DashboardController.php      [CUSTOM]
│   │       └── RouterController.php         [CUSTOM]
│   ├── Middleware/
│   │   ├── Authenticate.php                 [MODIFIED - custom redirect]
│   │   └── CheckPermission.php              [CUSTOM]
│   ├── Requests/
│   │   ├── StoreRouterRequest.php           [CUSTOM]
│   │   └── UpdateRouterRequest.php          [CUSTOM]
│   └── Resources/
│       ├── RouterCategoryResource.php       [CUSTOM]
│       └── RouterResource.php               [CUSTOM]
├── Models/
│   ├── Permission.php                       [CUSTOM]
│   ├── Role.php                             [CUSTOM]
│   ├── Router.php                           [CUSTOM]
│   ├── RouterCategory.php                   [CUSTOM]
│   ├── RouterStatusLog.php                  [CUSTOM]
│   └── User.php                             [MODIFIED - added RBAC]
└── Services/
    ├── PrometheusService.php                [CUSTOM]
    └── SNMPService.php                      [CUSTOM]
\`\`\`

### Database

\`\`\`
database/
├── migrations/
│   ├── 2024_01_01_000001_create_roles_table.php                [CUSTOM]
│   ├── 2024_01_01_000002_create_permissions_table.php          [CUSTOM]
│   ├── 2024_01_01_000003_create_role_permission_table.php      [CUSTOM]
│   ├── 2024_01_01_000004_create_users_table.php                [CUSTOM]
│   ├── 2024_01_01_000005_create_router_categories_table.php    [CUSTOM]
│   ├── 2024_01_01_000006_create_routers_table.php              [CUSTOM]
│   ├── 2024_01_01_000007_create_router_status_logs_table.php   [CUSTOM]
│   ├── 2024_01_01_000008_add_coordinates_to_routers_table.php  [CUSTOM]
│   └── 2024_01_08_000000_modify_routers_table_for_snmp.php     [CUSTOM]
└── seeders/
    ├── DatabaseSeeder.php                   [LARAVEL CORE - optional modify]
    ├── DummyRouterSeeder.php                [CUSTOM]
    └── RolePermissionSeeder.php             [CUSTOM]
\`\`\`

### Views

\`\`\`
resources/views/
├── auth/
│   ├── login.blade.php                      [CUSTOM]
│   └── register.blade.php                   [CUSTOM]
├── layouts/
│   ├── app.blade.php                        [CUSTOM]
│   └── guest.blade.php                      [CUSTOM]
├── routers/
│   ├── create.blade.php                     [CUSTOM]
│   ├── edit.blade.php                       [CUSTOM]
│   ├── index.blade.php                      [CUSTOM]
│   └── show.blade.php                       [CUSTOM]
└── dashboard.blade.php                      [CUSTOM]
\`\`\`

### Routes

\`\`\`
routes/
├── api.php                                  [MODIFIED - added custom routes]
├── web.php                                  [MODIFIED - added custom routes]
├── console.php                              [LARAVEL CORE]
└── channels.php                             [LARAVEL CORE]
\`\`\`

### Configuration

\`\`\`
config/
├── cors.php                                 [LARAVEL CORE - may need modification]
└── sanctum.php                              [LARAVEL CORE - may need modification]
\`\`\`

### Public Assets

\`\`\`
public/
└── css/
    └── app.css                              [CUSTOM]
\`\`\`

### Docker & Monitoring

\`\`\`
├── docker-compose.yml                       [CUSTOM]
├── Dockerfile                               [CUSTOM]
├── .dockerignore                            [CUSTOM]
├── prometheus.yml                           [CUSTOM]
├── alerts.yml                               [CUSTOM]
└── grafana/
    ├── provisioning/
    │   ├── datasources/
    │   │   └── prometheus.yml               [CUSTOM]
    │   └── dashboards/
    │       └── default.yml                  [CUSTOM]
    └── dashboards/
        ├── router-overview.json             [CUSTOM]
        ├── router-details.json              [CUSTOM]
        ├── router-map.json                  [CUSTOM]
        └── network-performance.json         [CUSTOM]
\`\`\`

### Scripts

\`\`\`
scripts/
├── check-requirements.sh                    [CUSTOM]
└── troubleshoot.sh                          [CUSTOM]
\`\`\`

### Documentation

\`\`\`
docs/
├── API.md                                   [CUSTOM]
├── DEPLOYMENT.md                            [CUSTOM]
├── GRAFANA.md                               [CUSTOM]
├── INSTALLATION.md                          [CUSTOM]
├── LOCAL-TESTING.md                         [CUSTOM]
├── MAPS.md                                  [CUSTOM]
├── QUICKSTART.md                            [CUSTOM]
└── FILE-MANIFEST.md                         [CUSTOM]
\`\`\`

### Root Files

\`\`\`
├── .env.example                             [MODIFIED - added custom vars]
├── composer.json                            [MODIFIED - added dependencies]
├── install.sh                               [CUSTOM]
├── deploy.sh                                [CUSTOM]
├── Makefile                                 [CUSTOM]
├── postman_collection.json                  [CUSTOM]
└── README.md                                [MODIFIED]
\`\`\`

---

## Laravel Core Files (Don't Copy - Will Be Generated)

These files are part of Laravel framework and will be created when you install Laravel:

\`\`\`
├── app/
│   ├── Exceptions/
│   ├── Http/
│   │   └── Kernel.php
│   └── Providers/
├── bootstrap/
├── config/                                  [Most files]
├── public/
│   └── index.php
├── resources/
│   ├── css/
│   └── js/
├── storage/
├── tests/
├── vendor/                                  [Generated by composer]
├── artisan
├── composer.lock                            [Generated by composer]
├── package.json
└── phpunit.xml
\`\`\`

---

## Installation Checklist

- [ ] Install Laravel 10 fresh
- [ ] Copy all [CUSTOM] files
- [ ] Merge [MODIFIED] files
- [ ] Run `composer install`
- [ ] Install additional packages (php-snmp, sanctum)
- [ ] Copy `.env.example` to `.env`
- [ ] Generate app key
- [ ] Configure database in `.env`
- [ ] Run migrations
- [ ] Run seeders
- [ ] Set permissions on storage/
- [ ] Configure web server
- [ ] Setup cron for scheduler
- [ ] Test application

---

## Quick Copy Command

If all custom files are in a separate directory:

\`\`\`bash
# Assuming custom files are in 'custom-files/' directory
# and Laravel is installed in 'router-monitoring/' directory

# Copy application files
cp -r custom-files/app/* router-monitoring/app/

# Copy database files
cp -r custom-files/database/* router-monitoring/database/

# Copy views
cp -r custom-files/resources/views/* router-monitoring/resources/views/

# Copy routes
cp custom-files/routes/api.php router-monitoring/routes/
cp custom-files/routes/web.php router-monitoring/routes/

# Copy public assets
cp -r custom-files/public/css router-monitoring/public/

# Copy config files
cp custom-files/.env.example router-monitoring/
cp custom-files/composer.json router-monitoring/
cp custom-files/docker-compose.yml router-monitoring/
cp custom-files/Dockerfile router-monitoring/

# Copy documentation
cp -r custom-files/docs router-monitoring/
cp -r custom-files/scripts router-monitoring/
cp -r custom-files/grafana router-monitoring/
