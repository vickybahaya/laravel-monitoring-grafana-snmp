# Docker Deployment Guide

## Prerequisites

- Docker Engine 20.10+
- Docker Compose 2.0+
- 2GB RAM minimum
- 10GB disk space

## Quick Start

### 1. Clone/Download Project

\`\`\`bash
cd /path/to/router-monitoring
\`\`\`

### 2. Configure Environment

\`\`\`bash
# Copy environment file
cp .env.example .env

# Edit .env and update these values:
nano .env
\`\`\`

Required environment variables:
\`\`\`env
APP_NAME=RouterMonitoring
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-server-ip:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=secret
\`\`\`

### 3. Start with One Command

\`\`\`bash
chmod +x docker-start.sh
./docker-start.sh
\`\`\`

This script will:
- Build Docker images
- Start all containers
- Run database migrations
- Seed initial data
- Optimize Laravel
- Set proper permissions

### 4. Access Applications

- **Laravel Web UI**: http://localhost:8000
- **Grafana**: http://localhost:3000 (admin/admin)
- **Prometheus**: http://localhost:9090

**Default Login:**
- Email: admin@example.com
- Password: password

## Manual Deployment

If you prefer manual steps:

\`\`\`bash
# 1. Build containers
docker-compose build

# 2. Start containers
docker-compose up -d

# 3. Wait for MySQL
sleep 20

# 4. Run migrations
docker-compose exec laravel-app php artisan migrate --force

# 5. Seed database
docker-compose exec laravel-app php artisan db:seed --force

# 6. Generate key
docker-compose exec laravel-app php artisan key:generate

# 7. Optimize
docker-compose exec laravel-app php artisan config:cache
docker-compose exec laravel-app php artisan route:cache
\`\`\`

## Container Management

### View Logs

\`\`\`bash
# All containers
docker-compose logs -f

# Specific container
docker-compose logs -f laravel-app
docker-compose logs -f mysql
docker-compose logs -f prometheus
docker-compose logs -f grafana
\`\`\`

### Restart Containers

\`\`\`bash
# All containers
docker-compose restart

# Specific container
docker-compose restart laravel-app
\`\`\`

### Stop Containers

\`\`\`bash
# Stop (keep data)
./docker-stop.sh
# or
docker-compose down

# Stop and remove data
docker-compose down -v
\`\`\`

### Execute Commands

\`\`\`bash
# Laravel artisan commands
docker-compose exec laravel-app php artisan [command]

# Access container shell
docker-compose exec laravel-app bash

# MySQL shell
docker-compose exec mysql mysql -u root -psecret router_monitoring
\`\`\`

## Troubleshooting

### MySQL Connection Error

\`\`\`bash
# Check MySQL is running
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Restart MySQL
docker-compose restart mysql

# Wait and retry migration
sleep 10
docker-compose exec laravel-app php artisan migrate
\`\`\`

### Permission Denied

\`\`\`bash
# Fix storage permissions
docker-compose exec laravel-app chown -R www-data:www-data /var/www/html/storage
docker-compose exec laravel-app chmod -R 775 /var/www/html/storage
\`\`\`

### Port Already in Use

Edit `docker-compose.yml` and change ports:

\`\`\`yaml
services:
  laravel-app:
    ports:
      - "8001:80"  # Change 8000 to 8001
  
  grafana:
    ports:
      - "3001:3000"  # Change 3000 to 3001
\`\`\`

### Container Won't Start

\`\`\`bash
# View detailed logs
docker-compose logs [container-name]

# Rebuild from scratch
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
\`\`\`

### Grafana Dashboard Empty

\`\`\`bash
# Check Prometheus targets
curl http://localhost:9090/api/v1/targets

# Check Laravel metrics endpoint
curl http://localhost:8000/api/metrics

# Manually check routers
docker-compose exec laravel-app php artisan routers:check
\`\`\`

## Production Deployment

### 1. Update Environment

\`\`\`env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
\`\`\`

### 2. Use Strong Passwords

\`\`\`env
DB_PASSWORD=your-strong-password
\`\`\`

Update in `docker-compose.yml`:
\`\`\`yaml
mysql:
  environment:
    MYSQL_ROOT_PASSWORD: your-strong-password
\`\`\`

### 3. Enable HTTPS

Use nginx reverse proxy or Traefik for SSL termination.

### 4. Backup Strategy

\`\`\`bash
# Backup database
docker-compose exec mysql mysqldump -u root -psecret router_monitoring > backup.sql

# Backup volumes
docker run --rm -v router-monitoring_mysql-data:/data -v $(pwd):/backup ubuntu tar czf /backup/mysql-backup.tar.gz /data
\`\`\`

### 5. Monitoring

\`\`\`bash
# Check container health
docker-compose ps

# Monitor resources
docker stats
\`\`\`

## Updating

\`\`\`bash
# Pull latest code
git pull

# Rebuild containers
docker-compose build --no-cache

# Restart with new code
docker-compose up -d

# Run new migrations
docker-compose exec laravel-app php artisan migrate --force

# Clear cache
docker-compose exec laravel-app php artisan cache:clear
docker-compose exec laravel-app php artisan config:cache
\`\`\`

## Scaling

To handle more routers, increase resources:

\`\`\`yaml
services:
  laravel-app:
    deploy:
      resources:
        limits:
          cpus: '2'
          memory: 2G
\`\`\`

## Support

For issues:
1. Check logs: `docker-compose logs`
2. Verify all containers running: `docker-compose ps`
3. Check health: `curl http://localhost:8000/api/health`
