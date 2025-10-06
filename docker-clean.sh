#!/bin/bash

echo "🧹 Docker Cleanup Tool"
echo "====================="
echo ""
echo "⚠️  WARNING: This will remove all stopped containers and unused images!"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Cancelled."
    exit 1
fi

echo ""
echo "🛑 Stopping all containers..."
docker-compose down

echo ""
echo "🗑️  Removing stopped containers..."
docker container prune -f

echo ""
echo "🗑️  Removing unused images..."
docker image prune -a -f

echo ""
echo "🗑️  Removing unused volumes..."
docker volume prune -f

echo ""
echo "🗑️  Removing unused networks..."
docker network prune -f

echo ""
echo "✅ Cleanup complete!"
echo ""
echo "📊 Current Docker usage:"
docker system df
echo ""
