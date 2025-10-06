# Router Monitoring System with Laravel 10 + Grafana + Prometheus

A comprehensive network monitoring system with RBAC (Role-Based Access Control) for managing and monitoring MikroTik routers.

## Features

- **RBAC System**: Admin, Operator, and Viewer roles with granular permissions
- **Router Management**: CRUD operations for routers with categories
- **Real-time Monitoring**: Integration with Prometheus for metrics collection
- **Grafana Dashboards**: Beautiful visualizations with maps and graphs
- **Geographic Mapping**: Track router locations on interactive maps
- **Status Logging**: Historical data for uptime/downtime analysis
- **RESTful API**: Complete API for integration with external systems

## Tech Stack

- Laravel 10
- MySQL/PostgreSQL
- Laravel Sanctum (API Authentication)
- Prometheus (Metrics Collection)
- Grafana (Visualization)

## Installation

### 1. Clone and Install Dependencies

\`\`\`bash
composer install
cp .env.example .env
php artisan key:generate
\`\`\`

### 2. Configure Database

Edit `.env` file:
\`\`\`
DB_DATABASE=router_monitoring
DB_USERNAME=your_username
DB_PASSWORD=your_password
\`\`\`

### 3. Run Migrations and Seeders

\`\`\`bash
php artisan migrate
php artisan db:seed --class=RolePermissionSeeder
\`\`\`

### 4. Start Development Server

\`\`\`bash
php artisan serve
\`\`\`

## Default Credentials

- **Email**: admin@example.com
- **Password**: password

## API Endpoints

### Authentication
- `POST /api/login` - Login
- `POST /api/logout` - Logout
- `GET /api/me` - Get current user

### Routers (Protected)
- `GET /api/routers` - List all routers
- `POST /api/routers` - Create router
- `GET /api/routers/{id}` - Get router details
- `PUT /api/routers/{id}` - Update router
- `DELETE /api/routers/{id}` - Delete router

### Grafana Integration
- `GET /api/grafana/search` - Search targets
- `POST /api/grafana/query` - Query metrics
- `POST /api/grafana/annotations` - Get annotations

## Roles & Permissions

### Admin
- Full system access
- User management
- All router operations
- Settings management

### Operator
- Router CRUD operations
- View monitoring data
- Export reports
- Manage categories

### Viewer
- Read-only access
- View routers
- View monitoring dashboards

## Next Steps

1. Configure Prometheus to scrape metrics
2. Setup Grafana dashboards
3. Add RouterOS API integration
4. Configure maps with router locations

## License

MIT License
