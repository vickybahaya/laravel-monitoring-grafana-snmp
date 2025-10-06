#!/bin/bash

# Check system requirements for Router Monitoring System

echo "Checking system requirements..."
echo ""

# Check Docker
if command -v docker &> /dev/null; then
    echo "✓ Docker: $(docker --version)"
else
    echo "✗ Docker: Not installed"
    echo "  Install: https://docs.docker.com/get-docker/"
fi

# Check Docker Compose
if command -v docker-compose &> /dev/null; then
    echo "✓ Docker Compose: $(docker-compose --version)"
else
    echo "✗ Docker Compose: Not installed"
    echo "  Install: https://docs.docker.com/compose/install/"
fi

# Check Git
if command -v git &> /dev/null; then
    echo "✓ Git: $(git --version)"
else
    echo "✗ Git: Not installed"
fi

# Check available ports
echo ""
echo "Checking ports..."
for port in 8000 3000 9090 3306 6379; do
    if nc -z localhost $port 2>/dev/null; then
        echo "✗ Port $port: Already in use"
    else
        echo "✓ Port $port: Available"
    fi
done

# Check disk space
echo ""
echo "Disk space:"
df -h / | tail -1

# Check memory
echo ""
echo "Memory:"
free -h | grep Mem

echo ""
echo "Requirements check complete!"
