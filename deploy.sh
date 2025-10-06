#!/bin/bash

# Router Monitoring Deployment Script
# Usage: ./deploy.sh [environment]
# Example: ./deploy.sh production

set -e

ENVIRONMENT=${1:-local}
PROJECT_DIR=$(pwd)

echo "========================================="
echo "Router Monitoring Deployment Script"
echo "Environment: $ENVIRONMENT"
echo "========================================="

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

check_requirements() {
    echo "Checking requirements..."
    
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed"
        exit 1
    fi
    print_success "Docker is installed"
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed"
        exit 1
    fi
    print_success "Docker Compose is installed"
}

setup_environment() {
    echo ""
    echo "Setting up environment..."
    
    if [ ! -f .env ]; then
        print_warning ".env file not found, creating from .env.example"
        cp .env.example .env
        print_success "Created .env file"
        print_warning "Please edit .env file with your configuration"
        exit 0
    else
        print_success ".env file exists"
    fi
}

build_containers() {
    echo ""
    echo "Building Docker containers..."
    docker-compose build
    print_success "Containers built successfully"
}

start_containers() {
    echo ""
    echo "Starting containers..."
    docker-compose up -d
    print_success "Containers started"
    
    echo ""
    echo "Waiting for services to be ready..."
    sleep 10
}

setup_laravel() {
    echo ""
    echo "Setting up Laravel..."
    
    # Install dependencies
    echo "Installing Composer dependencies..."
    docker-compose exec -T app composer install --optimize-autoloader $([ "$ENVIRONMENT" = "production" ] && echo "--no-dev")
    print_success "Dependencies installed"
    
    # Generate key if not exists
    if ! grep -q "APP_KEY=base64:" .env; then
        echo "Generating application key..."
        docker-compose exec -T app php artisan key:generate
        print_success "Application key generated"
    fi
    
    # Run migrations
    echo "Running database migrations..."
    docker-compose exec -T app php artisan migrate --force
    print_success "Migrations completed"
    
    # Seed database
    echo "Seeding database..."
    docker-compose exec -T app php artisan db:seed --force
    print_success "Database seeded"
    
    # Create storage link
    echo "Creating storage link..."
    docker-compose exec -T app php artisan storage:link
    print_success "Storage link created"
    
    # Cache configuration
    if [ "$ENVIRONMENT" = "production" ]; then
        echo "Caching configuration..."
        docker-compose exec -T app php artisan config:cache
        docker-compose exec -T app php artisan route:cache
        docker-compose exec -T app php artisan view:cache
        print_success "Configuration cached"
    fi
    
    # Set permissions
    echo "Setting permissions..."
    docker-compose exec -T app chown -R www-data:www-data /var/www/html/storage
    docker-compose exec -T app chown -R www-data:www-data /var/www/html/bootstrap/cache
    print_success "Permissions set"
}

check_services() {
    echo ""
    echo "Checking services..."
    
    # Check Laravel
    if curl -s http://localhost:8000/api/health > /dev/null; then
        print_success "Laravel API is running"
    else
        print_warning "Laravel API might not be ready yet"
    fi
    
    # Check Prometheus
    if curl -s http://localhost:9090/-/healthy > /dev/null; then
        print_success "Prometheus is running"
    else
        print_warning "Prometheus might not be ready yet"
    fi
    
    # Check Grafana
    if curl -s http://localhost:3000/api/health > /dev/null; then
        print_success "Grafana is running"
    else
        print_warning "Grafana might not be ready yet"
    fi
}

show_info() {
    echo ""
    echo "========================================="
    echo "Deployment completed successfully!"
    echo "========================================="
    echo ""
    echo "Access URLs:"
    echo "  Laravel API:  http://localhost:8000"
    echo "  Grafana:      http://localhost:3000"
    echo "  Prometheus:   http://localhost:9090"
    echo ""
    echo "Default Credentials:"
    echo "  Laravel:"
    echo "    Email:    admin@example.com"
    echo "    Password: password"
    echo ""
    echo "  Grafana:"
    echo "    Username: admin"
    echo "    Password: admin"
    echo ""
    echo "Useful Commands:"
    echo "  View logs:           docker-compose logs -f"
    echo "  Stop services:       docker-compose down"
    echo "  Restart services:    docker-compose restart"
    echo "  Run migrations:      docker-compose exec app php artisan migrate"
    echo "  Check routers:       docker-compose exec app php artisan routers:check"
    echo ""
    echo "Documentation:"
    echo "  Deployment: docs/DEPLOYMENT.md"
    echo "  Grafana:    docs/GRAFANA.md"
    echo "  Maps:       docs/MAPS.md"
    echo "  API:        docs/API.md"
    echo ""
}

# Main execution
main() {
    check_requirements
    setup_environment
    build_containers
    start_containers
    setup_laravel
    check_services
    show_info
}

# Run main function
main
