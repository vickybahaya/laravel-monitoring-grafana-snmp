#!/bin/bash

echo "🚀 Starting Router Monitoring System..."

# Check if .env exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    cp .env.example .env
fi

# Build and start containers
echo "🐳 Building Docker containers..."
docker-compose build --no-cache

echo "🐳 Starting Docker containers..."
docker-compose up -d

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 20

# Run migrations
echo "📊 Running database migrations..."
docker-compose exec -T laravel-app php artisan migrate --force

# Seed database
echo "🌱 Seeding database..."
docker-compose exec -T laravel-app php artisan db:seed --force

# Generate app key if not exists
echo "🔑 Generating application key..."
docker-compose exec -T laravel-app php artisan key:generate --force

# Clear and cache config
echo "🔧 Optimizing application..."
docker-compose exec -T laravel-app php artisan config:cache
docker-compose exec -T laravel-app php artisan route:cache
docker-compose exec -T laravel-app php artisan view:cache

# Set permissions
echo "🔒 Setting permissions..."
docker-compose exec -T laravel-app chown -R www-data:www-data /var/www/html/storage
docker-compose exec -T laravel-app chmod -R 775 /var/www/html/storage

echo ""
echo "✅ Router Monitoring System is ready!"
echo ""
echo "📍 Access URLs:"
echo "   - Laravel App: http://localhost:8000"
echo "   - Grafana: http://localhost:3000 (admin/admin)"
echo "   - Prometheus: http://localhost:9090"
echo ""
echo "🔐 Default Login:"
echo "   Email: admin@example.com"
echo "   Password: password"
echo ""
echo "📝 View logs: docker-compose logs -f"
echo "🛑 Stop: docker-compose down"
echo ""
