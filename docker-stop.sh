#!/bin/bash

echo "🛑 Stopping Router Monitoring System..."

docker-compose down

echo "✅ All containers stopped!"
echo ""
echo "💡 To remove all data: docker-compose down -v"
echo "💡 To start again: ./docker-start.sh"
