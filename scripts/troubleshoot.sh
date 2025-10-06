#!/bin/bash

# Troubleshooting Script for Router Monitoring System
# Usage: ./scripts/troubleshoot.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo "========================================="
echo "Router Monitoring - Troubleshooting"
echo "========================================="
echo ""

# Function to check service
check_service() {
    local service=$1
    local url=$2
    local name=$3
    
    echo -n "Checking $name... "
    if curl -s "$url" > /dev/null 2>&1; then
        echo -e "${GREEN}✓ OK${NC}"
        return 0
    else
        echo -e "${RED}✗ FAILED${NC}"
        return 1
    fi
}

# Function to check port
check_port() {
    local port=$1
    local name=$2
    
    echo -n "Checking port $port ($name)... "
    if nc -z localhost $port 2>/dev/null; then
        echo -e "${GREEN}✓ Open${NC}"
        return 0
    else
        echo -e "${RED}✗ Closed${NC}"
        return 1
    fi
}

# Check Docker
echo -e "${BLUE}=== Docker Status ===${NC}"
if command -v docker &> /dev/null; then
    echo -e "${GREEN}✓ Docker installed${NC}"
    docker --version
else
    echo -e "${RED}✗ Docker not installed${NC}"
    exit 1
fi

if command -v docker-compose &> /dev/null; then
    echo -e "${GREEN}✓ Docker Compose installed${NC}"
    docker-compose --version
else
    echo -e "${RED}✗ Docker Compose not installed${NC}"
    exit 1
fi
echo ""

# Check containers
echo -e "${BLUE}=== Container Status ===${NC}"
docker-compose ps
echo ""

# Check services
echo -e "${BLUE}=== Service Health ===${NC}"
check_service "Laravel" "http://localhost:8000/api/health" "Laravel API"
check_service "Grafana" "http://localhost:3000/api/health" "Grafana"
check_service "Prometheus" "http://localhost:9090/-/healthy" "Prometheus"
echo ""

# Check ports
echo -e "${BLUE}=== Port Status ===${NC}"
check_port 8000 "Laravel"
check_port 3000 "Grafana"
check_port 9090 "Prometheus"
check_port 3306 "MySQL"
check_port 6379 "Redis"
echo ""

# Check environment
echo -e "${BLUE}=== Environment Configuration ===${NC}"
if [ -f .env ]; then
    echo -e "${GREEN}✓ .env file exists${NC}"
    
    # Check critical env vars
    if grep -q "APP_KEY=base64:" .env; then
        echo -e "${GREEN}✓ APP_KEY is set${NC}"
    else
        echo -e "${RED}✗ APP_KEY not set${NC}"
        echo -e "${YELLOW}  Run: docker-compose exec app php artisan key:generate${NC}"
    fi
    
    if grep -q "DB_DATABASE=" .env; then
        echo -e "${GREEN}✓ Database configured${NC}"
    else
        echo -e "${YELLOW}⚠ Database not configured${NC}"
    fi
else
    echo -e "${RED}✗ .env file not found${NC}"
    echo -e "${YELLOW}  Run: cp .env.example .env${NC}"
fi
echo ""

# Check database connection
echo -e "${BLUE}=== Database Connection ===${NC}"
if docker-compose exec -T app php artisan tinker --execute="DB::connection()->getPdo();" > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Database connection OK${NC}"
else
    echo -e "${RED}✗ Database connection failed${NC}"
    echo -e "${YELLOW}  Check DB credentials in .env${NC}"
fi
echo ""

# Check storage permissions
echo -e "${BLUE}=== Storage Permissions ===${NC}"
if docker-compose exec -T app test -w /var/www/html/storage; then
    echo -e "${GREEN}✓ Storage is writable${NC}"
else
    echo -e "${RED}✗ Storage is not writable${NC}"
    echo -e "${YELLOW}  Run: docker-compose exec app chown -R www-data:www-data /var/www/html/storage${NC}"
fi
echo ""

# Check logs for errors
echo -e "${BLUE}=== Recent Errors ===${NC}"
if [ -f storage/logs/laravel.log ]; then
    ERROR_COUNT=$(grep -c "ERROR" storage/logs/laravel.log 2>/dev/null || echo "0")
    if [ "$ERROR_COUNT" -gt 0 ]; then
        echo -e "${YELLOW}⚠ Found $ERROR_COUNT errors in logs${NC}"
        echo "Last 5 errors:"
        grep "ERROR" storage/logs/laravel.log | tail -5
    else
        echo -e "${GREEN}✓ No errors in logs${NC}"
    fi
else
    echo -e "${YELLOW}⚠ Log file not found${NC}"
fi
echo ""

# Check disk space
echo -e "${BLUE}=== Disk Space ===${NC}"
df -h | grep -E "Filesystem|/$"
echo ""

# Check memory
echo -e "${BLUE}=== Memory Usage ===${NC}"
free -h
echo ""

# Docker stats
echo -e "${BLUE}=== Container Resources ===${NC}"
docker stats --no-stream
echo ""

# Recommendations
echo -e "${BLUE}=== Recommendations ===${NC}"

# Check if services are down
if ! curl -s http://localhost:8000/api/health > /dev/null 2>&1; then
    echo -e "${YELLOW}→ Laravel is not responding. Try:${NC}"
    echo "  docker-compose restart app"
    echo "  docker-compose logs app"
fi

if ! curl -s http://localhost:3000/api/health > /dev/null 2>&1; then
    echo -e "${YELLOW}→ Grafana is not responding. Try:${NC}"
    echo "  docker-compose restart grafana"
    echo "  docker-compose logs grafana"
fi

if ! curl -s http://localhost:9090/-/healthy > /dev/null 2>&1; then
    echo -e "${YELLOW}→ Prometheus is not responding. Try:${NC}"
    echo "  docker-compose restart prometheus"
    echo "  docker-compose logs prometheus"
fi

echo ""
echo -e "${BLUE}=== Common Solutions ===${NC}"
echo "1. Restart all services:"
echo "   docker-compose restart"
echo ""
echo "2. Rebuild containers:"
echo "   docker-compose down && docker-compose up -d --build"
echo ""
echo "3. Clear Laravel cache:"
echo "   docker-compose exec app php artisan cache:clear"
echo "   docker-compose exec app php artisan config:clear"
echo ""
echo "4. Fix permissions:"
echo "   docker-compose exec app chown -R www-data:www-data /var/www/html/storage"
echo ""
echo "5. Check detailed logs:"
echo "   docker-compose logs -f [service-name]"
echo ""
echo "========================================="
echo "Troubleshooting Complete"
echo "========================================="
