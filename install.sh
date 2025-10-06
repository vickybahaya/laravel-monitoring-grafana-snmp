#!/bin/bash

echo "=========================================="
echo "Laravel Router Monitoring - Installation"
echo "=========================================="
echo ""

# Check requirements
echo "Checking requirements..."

if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed"
    exit 1
fi

if ! command -v docker &> /dev/null; then
    echo "⚠️  Docker is not installed (optional for containerized deployment)"
fi

echo "✅ Requirements check passed"
echo ""

# Ask installation method
echo "Choose installation method:"
echo "1) Fresh Laravel 10 + Custom Files (Recommended)"
echo "2) Docker Compose (All-in-one)"
echo "3) Manual Setup (Existing Laravel)"
read -p "Enter choice [1-3]: " choice

case $choice in
    1)
        echo ""
        echo "Installing Fresh Laravel 10..."
        
        # Check if current directory is empty
        if [ "$(ls -A)" ]; then
            echo "⚠️  Current directory is not empty!"
            read -p "Continue anyway? (y/n): " continue
            if [ "$continue" != "y" ]; then
                exit 1
            fi
        fi
        
        # Install Laravel 10
        composer create-project laravel/laravel:^10.0 temp-laravel
        
        # Move files from temp to current directory
        mv temp-laravel/* .
        mv temp-laravel/.* . 2>/dev/null
        rm -rf temp-laravel
        
        echo "✅ Laravel 10 installed"
        echo ""
        
        # Install additional dependencies
        echo "Installing additional dependencies..."
        composer require php-snmp/php-snmp
        composer require laravel/sanctum
        
        echo "✅ Dependencies installed"
        echo ""
        
        # Setup environment
        echo "Setting up environment..."
        if [ ! -f .env ]; then
            cp .env.example .env
            php artisan key:generate
        fi
        
        echo "✅ Environment configured"
        echo ""
        
        # Database setup
        echo "Database Configuration:"
        read -p "Database Host [localhost]: " db_host
        db_host=${db_host:-localhost}
        
        read -p "Database Name [router_monitoring]: " db_name
        db_name=${db_name:-router_monitoring}
        
        read -p "Database User [root]: " db_user
        db_user=${db_user:-root}
        
        read -sp "Database Password: " db_pass
        echo ""
        
        # Update .env
        sed -i "s/DB_HOST=.*/DB_HOST=$db_host/" .env
        sed -i "s/DB_DATABASE=.*/DB_DATABASE=$db_name/" .env
        sed -i "s/DB_USERNAME=.*/DB_USERNAME=$db_user/" .env
        sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$db_pass/" .env
        
        echo "✅ Database configured"
        echo ""
        
        # Run migrations
        echo "Running migrations..."
        php artisan migrate --seed
        
        echo "✅ Database migrated"
        echo ""
        
        # Set permissions
        echo "Setting permissions..."
        chmod -R 775 storage bootstrap/cache
        
        echo "✅ Permissions set"
        echo ""
        
        echo "=========================================="
        echo "Installation Complete!"
        echo "=========================================="
        echo ""
        echo "Default Admin Account:"
        echo "Email: admin@example.com"
        echo "Password: password"
        echo ""
        echo "To start the application:"
        echo "  php artisan serve"
        echo ""
        echo "Then visit: http://localhost:8000"
        echo "=========================================="
        ;;
        
    2)
        echo ""
        echo "Starting Docker Compose installation..."
        
        if ! command -v docker-compose &> /dev/null; then
            echo "❌ Docker Compose is not installed"
            exit 1
        fi
        
        # Setup environment
        if [ ! -f .env ]; then
            cp .env.example .env
            sed -i "s/DB_HOST=.*/DB_HOST=mysql/" .env
            sed -i "s/DB_DATABASE=.*/DB_DATABASE=router_monitoring/" .env
            sed -i "s/DB_USERNAME=.*/DB_USERNAME=root/" .env
            sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=secret/" .env
        fi
        
        echo "Building Docker containers..."
        docker-compose build
        
        echo "Starting containers..."
        docker-compose up -d
        
        echo "Waiting for MySQL to be ready..."
        sleep 15
        
        echo "Running migrations..."
        docker-compose exec laravel-app php artisan migrate --seed
        
        echo "✅ Docker installation complete"
        echo ""
        echo "=========================================="
        echo "Services Running:"
        echo "=========================================="
        echo "Laravel:    http://localhost:8000"
        echo "Grafana:    http://localhost:3000 (admin/admin)"
        echo "Prometheus: http://localhost:9090"
        echo "MySQL:      localhost:3306"
        echo ""
        echo "Default Admin Account:"
        echo "Email: admin@example.com"
        echo "Password: password"
        echo "=========================================="
        ;;
        
    3)
        echo ""
        echo "Manual Setup Instructions:"
        echo ""
        echo "1. Copy all custom files to your Laravel 10 project"
        echo "2. Install dependencies:"
        echo "   composer require php-snmp/php-snmp"
        echo "   composer require laravel/sanctum"
        echo ""
        echo "3. Update .env file with database credentials"
        echo ""
        echo "4. Run migrations:"
        echo "   php artisan migrate --seed"
        echo ""
        echo "5. Set permissions:"
        echo "   chmod -R 775 storage bootstrap/cache"
        echo ""
        echo "6. Start the application:"
        echo "   php artisan serve"
        ;;
        
    *)
        echo "Invalid choice"
        exit 1
        ;;
esac
