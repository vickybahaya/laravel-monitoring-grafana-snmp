#!/bin/bash

echo "🔄 Fresh Docker Start"
echo "===================="
echo ""
echo "This will:"
echo "  1. Stop all containers"
echo "  2. Remove all containers and volumes"
echo "  3. Rebuild images from scratch"
echo "  4. Start fresh containers"
echo ""
echo "⚠️  WARNING: All data will be lost!"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 1
fi

echo ""
echo "🛑 Stopping and removing containers..."
docker-compose down -v

echo ""
echo "🗑️  Cleaning up Docker system..."
docker system prune -f

echo ""
echo "🔨 Building images (this may take a few minutes)..."
docker-compose build --no-cache

echo ""
echo "🚀 Starting containers..."
docker-compose up -d

echo ""
echo "⏳ Waiting for services to be ready (30 seconds)..."
sleep 30

echo ""
echo "📊 Container status:"
docker-compose ps

echo ""
echo "✅ Fresh start complete!"
echo ""
echo "📍 Access URLs:"
echo "   - Laravel App: http://localhost:8000"
echo "   - Grafana: http://localhost:3000 (admin/admin)"
echo "   - Prometheus: http://localhost:9090"
echo ""
echo "📝 View logs: docker-compose logs -f"
echo ""
