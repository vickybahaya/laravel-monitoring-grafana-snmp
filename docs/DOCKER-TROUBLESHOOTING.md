# Docker Troubleshooting Guide

## Quick Diagnosis

Run the debug script to see what's wrong:

\`\`\`bash
chmod +x docker-debug.sh
./docker-debug.sh
\`\`\`

## Common Issues and Solutions

### 1. Container Exits Immediately (Exit Code 1 or 2)

**Symptoms:**
- Container shows "Exited (1)" or "Exited (2)" status
- `docker ps -a` shows stopped containers

**Causes:**
- Build error in Dockerfile
- Missing dependencies
- Configuration error

**Solutions:**

\`\`\`bash
# Check logs
docker-compose logs laravel-app

# Common fixes:
# 1. Clean rebuild
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d

# 2. Check if .env exists
ls -la .env

# 3. Manually create .env
cp .env.example .env
\`\`\`

### 2. "could not find driver" Error

**Cause:** PHP MySQL extension not installed

**Solution:**

Check Dockerfile has this line:
\`\`\`dockerfile
RUN docker-php-ext-install pdo_mysql
\`\`\`

Rebuild:
\`\`\`bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
\`\`\`

### 3. MySQL Connection Refused

**Symptoms:**
- "Connection refused" error
- "SQLSTATE[HY000] [2002]" error

**Solutions:**

\`\`\`bash
# 1. Check MySQL is running
docker-compose ps mysql

# 2. Wait longer for MySQL to start
docker-compose logs mysql | grep "ready for connections"

# 3. Check environment variables
docker-compose exec laravel-app env | grep DB_

# 4. Test MySQL connection
docker-compose exec mysql mysql -u root -psecret -e "SHOW DATABASES;"
\`\`\`

### 4. Permission Denied Errors

**Symptoms:**
- "Permission denied" in logs
- Cannot write to storage/logs

**Solutions:**

\`\`\`bash
# Fix permissions
docker-compose exec laravel-app chown -R www-data:www-data /var/www/html/storage
docker-compose exec laravel-app chmod -R 775 /var/www/html/storage
docker-compose exec laravel-app chmod -R 775 /var/www/html/bootstrap/cache
\`\`\`

### 5. Port Already in Use

**Symptoms:**
- "port is already allocated" error
- Cannot start container

**Solutions:**

\`\`\`bash
# Check what's using the port
sudo lsof -i :8000
sudo lsof -i :3000
sudo lsof -i :9090

# Kill the process or change port in docker-compose.yml
# Change "8000:80" to "8001:80" for example
\`\`\`

### 6. Out of Disk Space

**Symptoms:**
- "no space left on device"
- Build fails

**Solutions:**

\`\`\`bash
# Check disk usage
docker system df

# Clean up
./docker-clean.sh

# Or manual cleanup
docker system prune -a --volumes -f
\`\`\`

### 7. Composer Install Fails

**Symptoms:**
- "composer install" error in build
- Memory limit errors

**Solutions:**

Add to Dockerfile before composer install:
\`\`\`dockerfile
ENV COMPOSER_MEMORY_LIMIT=-1
\`\`\`

Or build with more memory:
\`\`\`bash
docker-compose build --memory 2g
\`\`\`

### 8. npm Command Not Found

**Cause:** Node.js not installed in container

**Solution:**

Check Dockerfile has Node.js installation:
\`\`\`dockerfile
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs
\`\`\`

### 9. Grafana Dashboards Not Loading

**Symptoms:**
- Grafana shows no dashboards
- Provisioning failed

**Solutions:**

\`\`\`bash
# 1. Check provisioning files exist
ls -la grafana/provisioning/
ls -la grafana/dashboards/

# 2. Check Grafana logs
docker-compose logs grafana

# 3. Restart Grafana
docker-compose restart grafana
\`\`\`

### 10. Prometheus Not Scraping Metrics

**Symptoms:**
- No data in Grafana
- Prometheus shows target down

**Solutions:**

\`\`\`bash
# 1. Check Prometheus targets
# Open http://localhost:9090/targets

# 2. Test metrics endpoint
curl http://localhost:8000/api/metrics

# 3. Check Prometheus config
docker-compose exec prometheus cat /etc/prometheus/prometheus.yml

# 4. Reload Prometheus config
curl -X POST http://localhost:9090/-/reload
\`\`\`

## Complete Fresh Start

If nothing works, do a complete fresh start:

\`\`\`bash
# Run the fresh start script
chmod +x docker-fresh-start.sh
./docker-fresh-start.sh
\`\`\`

Or manually:

\`\`\`bash
# 1. Stop everything
docker-compose down -v

# 2. Remove all Docker data
docker system prune -a --volumes -f

# 3. Remove project volumes
docker volume rm $(docker volume ls -q | grep router-monitoring)

# 4. Rebuild from scratch
docker-compose build --no-cache

# 5. Start fresh
docker-compose up -d

# 6. Check logs
docker-compose logs -f
\`\`\`

## Debugging Inside Container

\`\`\`bash
# Access Laravel container
docker-compose exec laravel-app bash

# Inside container, check:
php -v                          # PHP version
php -m                          # PHP modules
composer --version              # Composer version
cat .env                        # Environment variables
php artisan --version           # Laravel version
php artisan migrate:status      # Migration status
php artisan route:list          # Routes
php artisan config:show         # Configuration

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Test SNMP
snmpwalk -v2c -c public 192.168.1.1
\`\`\`

## Getting Help

If you're still stuck:

1. Run debug script and save output:
   \`\`\`bash
   ./docker-debug.sh > debug-output.txt
   \`\`\`

2. Check all logs:
   \`\`\`bash
   docker-compose logs > all-logs.txt
   \`\`\`

3. Provide:
   - debug-output.txt
   - all-logs.txt
   - Your docker-compose.yml
   - Your .env file (remove sensitive data)
   - OS and Docker version
