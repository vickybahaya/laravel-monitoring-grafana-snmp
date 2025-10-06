#!/bin/bash

echo "🔍 Docker Debugging Tool"
echo "======================="
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running!"
    echo "   Please start Docker and try again."
    exit 1
fi

echo "✅ Docker is running"
echo ""

# Show all containers
echo "📦 All Containers:"
docker ps -a
echo ""

# Check for failed containers
FAILED_CONTAINERS=$(docker ps -a --filter "status=exited" --format "{{.ID}}")

if [ -z "$FAILED_CONTAINERS" ]; then
    echo "✅ No failed containers"
else
    echo "❌ Failed containers found!"
    echo ""
    
    for CONTAINER_ID in $FAILED_CONTAINERS; do
        CONTAINER_NAME=$(docker inspect --format='{{.Name}}' $CONTAINER_ID | sed 's/\///')
        EXIT_CODE=$(docker inspect --format='{{.State.ExitCode}}' $CONTAINER_ID)
        
        echo "Container: $CONTAINER_NAME (ID: $CONTAINER_ID)"
        echo "Exit Code: $EXIT_CODE"
        echo ""
        echo "Last 50 lines of logs:"
        echo "---"
        docker logs --tail 50 $CONTAINER_ID
        echo "---"
        echo ""
    done
fi

# Check Docker Compose
echo "🐳 Docker Compose Status:"
if [ -f docker-compose.yml ]; then
    docker-compose ps
else
    echo "❌ docker-compose.yml not found!"
fi
echo ""

# Check disk space
echo "💾 Disk Space:"
df -h | grep -E "Filesystem|/dev/"
echo ""

# Check Docker images
echo "🖼️  Docker Images:"
docker images | head -10
echo ""

# Cleanup suggestion
echo "🧹 Cleanup Commands:"
echo "   Remove all stopped containers: docker container prune -f"
echo "   Remove unused images: docker image prune -a -f"
echo "   Remove all (CAUTION): docker system prune -a --volumes -f"
echo ""

# Quick fix suggestions
echo "💡 Quick Fixes:"
echo "   1. Clean up and rebuild:"
echo "      docker-compose down -v"
echo "      docker system prune -f"
echo "      docker-compose up -d --build"
echo ""
echo "   2. Check logs of specific service:"
echo "      docker-compose logs laravel-app"
echo "      docker-compose logs mysql"
echo ""
echo "   3. Access container shell:"
echo "      docker-compose exec laravel-app bash"
echo ""
