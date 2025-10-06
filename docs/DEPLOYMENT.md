# Panduan Deployment Laravel + Grafana Monitoring System

## Daftar Isi
1. [Deployment Lokal (Development)](#deployment-lokal-development)
2. [Deployment Production dengan Docker](#deployment-production-dengan-docker)
3. [Deployment Production Manual](#deployment-production-manual)
4. [Konfigurasi Server](#konfigurasi-server)
5. [Troubleshooting](#troubleshooting)

---

## Deployment Lokal (Development)

### Prerequisites
- Docker & Docker Compose (recommended)
- ATAU: PHP 8.1+, Composer, MySQL/PostgreSQL, Prometheus, Grafana

### Opsi 1: Menggunakan Docker (Recommended)

#### 1. Clone dan Setup Environment

\`\`\`bash
# Clone repository
git clone <your-repo-url>
cd laravel-grafana-monitoring

# Copy environment file
cp .env.example .env
\`\`\`

#### 2. Edit `.env` File

\`\`\`env
APP_NAME="Router Monitoring"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=secret

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Prometheus
PROMETHEUS_URL=http://prometheus:9090

# Grafana
GRAFANA_URL=http://grafana:3000
GRAFANA_ADMIN_USER=admin
GRAFANA_ADMIN_PASSWORD=admin
\`\`\`

#### 3. Build dan Start Containers

\`\`\`bash
# Build images
docker-compose build

# Start all services
docker-compose up -d

# Check status
docker-compose ps
\`\`\`

#### 4. Setup Laravel

\`\`\`bash
# Install dependencies
docker-compose exec app composer install

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed database (roles, permissions, admin user)
docker-compose exec app php artisan db:seed

# Create storage link
docker-compose exec app php artisan storage:link

# Start scheduler (untuk auto-check routers)
docker-compose exec app php artisan schedule:work
\`\`\`

#### 5. Akses Aplikasi

- **Laravel API**: http://localhost:8000
- **Grafana**: http://localhost:3000 (admin/admin)
- **Prometheus**: http://localhost:9090
- **Prometheus Metrics**: http://localhost:8000/api/metrics

#### 6. Login Default

\`\`\`
Email: admin@example.com
Password: password
\`\`\`

---

### Opsi 2: Manual Setup (Tanpa Docker)

#### 1. Install Dependencies

\`\`\`bash
# Install PHP dependencies
composer install

# Install Node dependencies (jika ada frontend)
npm install
\`\`\`

#### 2. Setup Database

\`\`\`bash
# Buat database
mysql -u root -p
CREATE DATABASE router_monitoring;
exit;

# Edit .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=your_password
\`\`\`

#### 3. Setup Laravel

\`\`\`bash
# Generate key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start server
php artisan serve
\`\`\`

#### 4. Install Prometheus

\`\`\`bash
# Download Prometheus
wget https://github.com/prometheus/prometheus/releases/download/v2.45.0/prometheus-2.45.0.linux-amd64.tar.gz
tar xvfz prometheus-*.tar.gz
cd prometheus-*

# Copy config
cp /path/to/project/prometheus.yml .

# Start Prometheus
./prometheus --config.file=prometheus.yml
\`\`\`

#### 5. Install Grafana

\`\`\`bash
# Ubuntu/Debian
sudo apt-get install -y software-properties-common
sudo add-apt-repository "deb https://packages.grafana.com/oss/deb stable main"
wget -q -O - https://packages.grafana.com/gpg.key | sudo apt-key add -
sudo apt-get update
sudo apt-get install grafana

# Start Grafana
sudo systemctl start grafana-server
sudo systemctl enable grafana-server
\`\`\`

---

## Deployment Production dengan Docker

### 1. Persiapan Server

\`\`\`bash
# Update server
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/download/v2.20.0/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verify installation
docker --version
docker-compose --version
\`\`\`

### 2. Clone Project ke Server

\`\`\`bash
# Clone repository
cd /var/www
sudo git clone <your-repo-url> router-monitoring
cd router-monitoring
sudo chown -R $USER:$USER .
\`\`\`

### 3. Setup Environment Production

\`\`\`bash
# Copy environment file
cp .env.example .env

# Edit .env untuk production
nano .env
\`\`\`

**Production `.env` Configuration:**

\`\`\`env
APP_NAME="Router Monitoring"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://monitoring.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=router_monitoring
DB_USERNAME=root
DB_PASSWORD=STRONG_PASSWORD_HERE

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

REDIS_HOST=redis
REDIS_PASSWORD=STRONG_REDIS_PASSWORD
REDIS_PORT=6379

# Prometheus
PROMETHEUS_URL=http://prometheus:9090

# Grafana
GRAFANA_URL=https://grafana.yourdomain.com
GRAFANA_ADMIN_USER=admin
GRAFANA_ADMIN_PASSWORD=STRONG_GRAFANA_PASSWORD

# Mail (untuk alerts)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
\`\`\`

### 4. Update Docker Compose untuk Production

\`\`\`bash
# Edit docker-compose.yml
nano docker-compose.yml
\`\`\`

Tambahkan Nginx reverse proxy:

\`\`\`yaml
# Tambahkan service nginx
  nginx:
    image: nginx:alpine
    container_name: router-monitoring-nginx
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./nginx/nginx.conf:/etc/nginx/nginx.conf
      - ./nginx/ssl:/etc/nginx/ssl
      - ./public:/var/www/html/public
    depends_on:
      - app
    networks:
      - monitoring-network
\`\`\`

### 5. Konfigurasi Nginx

\`\`\`bash
# Buat direktori nginx
mkdir -p nginx/ssl

# Buat file konfigurasi
nano nginx/nginx.conf
\`\`\`

**nginx/nginx.conf:**

\`\`\`nginx
events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    upstream laravel {
        server app:9000;
    }

    upstream grafana {
        server grafana:3000;
    }

    # Laravel API
    server {
        listen 80;
        server_name monitoring.yourdomain.com;
        
        # Redirect to HTTPS
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        server_name monitoring.yourdomain.com;
        root /var/www/html/public;

        ssl_certificate /etc/nginx/ssl/fullchain.pem;
        ssl_certificate_key /etc/nginx/ssl/privkey.pem;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";

        index index.php;

        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass laravel;
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }

    # Grafana
    server {
        listen 80;
        server_name grafana.yourdomain.com;
        return 301 https://$server_name$request_uri;
    }

    server {
        listen 443 ssl http2;
        server_name grafana.yourdomain.com;

        ssl_certificate /etc/nginx/ssl/fullchain.pem;
        ssl_certificate_key /etc/nginx/ssl/privkey.pem;

        location / {
            proxy_pass http://grafana;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
\`\`\`

### 6. Setup SSL Certificate (Let's Encrypt)

\`\`\`bash
# Install certbot
sudo apt install certbot

# Generate certificate
sudo certbot certonly --standalone -d monitoring.yourdomain.com -d grafana.yourdomain.com

# Copy certificates
sudo cp /etc/letsencrypt/live/monitoring.yourdomain.com/fullchain.pem nginx/ssl/
sudo cp /etc/letsencrypt/live/monitoring.yourdomain.com/privkey.pem nginx/ssl/
sudo chown -R $USER:$USER nginx/ssl/

# Setup auto-renewal
sudo crontab -e
# Tambahkan:
0 0 1 * * certbot renew --quiet && cp /etc/letsencrypt/live/monitoring.yourdomain.com/*.pem /var/www/router-monitoring/nginx/ssl/ && docker-compose restart nginx
\`\`\`

### 7. Deploy Application

\`\`\`bash
# Build dan start
docker-compose up -d --build

# Setup Laravel
docker-compose exec app composer install --optimize-autoloader --no-dev
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan db:seed --force

# Set permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
\`\`\`

### 8. Setup Cron untuk Scheduler

\`\`\`bash
# Edit crontab
crontab -e

# Tambahkan:
* * * * * cd /var/www/router-monitoring && docker-compose exec -T app php artisan schedule:run >> /dev/null 2>&1
\`\`\`

### 9. Setup Firewall

\`\`\`bash
# Allow HTTP, HTTPS, SSH
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
\`\`\`

---

## Deployment Production Manual (Tanpa Docker)

### 1. Setup Server (Ubuntu 22.04)

\`\`\`bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1 dan extensions
sudo apt install -y php8.1-fpm php8.1-cli php8.1-common php8.1-mysql \
    php8.1-zip php8.1-gd php8.1-mbstring php8.1-curl php8.1-xml \
    php8.1-bcmath php8.1-redis

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Install Redis
sudo apt install redis-server
sudo systemctl enable redis-server

# Install Nginx
sudo apt install nginx
\`\`\`

### 2. Setup Database

\`\`\`bash
sudo mysql -u root -p

CREATE DATABASE router_monitoring;
CREATE USER 'router_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON router_monitoring.* TO 'router_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
\`\`\`

### 3. Deploy Laravel

\`\`\`bash
# Clone project
cd /var/www
sudo git clone <your-repo-url> router-monitoring
cd router-monitoring

# Set ownership
sudo chown -R www-data:www-data /var/www/router-monitoring
sudo chmod -R 755 /var/www/router-monitoring
sudo chmod -R 775 /var/www/router-monitoring/storage
sudo chmod -R 775 /var/www/router-monitoring/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev

# Setup environment
cp .env.example .env
nano .env  # Edit dengan konfigurasi production

# Setup Laravel
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan db:seed --force
\`\`\`

### 4. Konfigurasi Nginx

\`\`\`bash
sudo nano /etc/nginx/sites-available/router-monitoring
\`\`\`

\`\`\`nginx
server {
    listen 80;
    server_name monitoring.yourdomain.com;
    root /var/www/router-monitoring/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
\`\`\`

\`\`\`bash
# Enable site
sudo ln -s /etc/nginx/sites-available/router-monitoring /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
\`\`\`

### 5. Setup SSL

\`\`\`bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d monitoring.yourdomain.com
\`\`\`

### 6. Setup Supervisor untuk Queue Worker

\`\`\`bash
sudo apt install supervisor

sudo nano /etc/supervisor/conf.d/router-monitoring.conf
\`\`\`

\`\`\`ini
[program:router-monitoring-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/router-monitoring/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/router-monitoring/storage/logs/worker.log
stopwaitsecs=3600

[program:router-monitoring-scheduler]
process_name=%(program_name)s
command=php /var/www/router-monitoring/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/router-monitoring/storage/logs/scheduler.log
\`\`\`

\`\`\`bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
\`\`\`

---

## Konfigurasi Server

### Optimasi PHP-FPM

\`\`\`bash
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
\`\`\`

\`\`\`ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
\`\`\`

### Optimasi MySQL

\`\`\`bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
\`\`\`

\`\`\`ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
query_cache_size = 0
query_cache_type = 0
\`\`\`

### Monitoring dan Logging

\`\`\`bash
# Setup log rotation
sudo nano /etc/logrotate.d/router-monitoring
\`\`\`

\`\`\`
/var/www/router-monitoring/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
\`\`\`

---

## Troubleshooting

### Laravel tidak bisa akses database

\`\`\`bash
# Check MySQL status
sudo systemctl status mysql

# Check credentials di .env
cat .env | grep DB_

# Test connection
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
\`\`\`

### Prometheus tidak scrape metrics

\`\`\`bash
# Check metrics endpoint
curl http://localhost:8000/api/metrics

# Check Prometheus targets
# Buka: http://localhost:9090/targets

# Check Prometheus logs
docker-compose logs prometheus
\`\`\`

### Grafana tidak bisa connect ke Prometheus

\`\`\`bash
# Check Grafana logs
docker-compose logs grafana

# Check datasource config
cat grafana/provisioning/datasources/prometheus.yml

# Test connection dari Grafana container
docker-compose exec grafana curl http://prometheus:9090/api/v1/status/config
\`\`\`

### Router check tidak jalan otomatis

\`\`\`bash
# Check scheduler
docker-compose exec app php artisan schedule:list

# Run manual
docker-compose exec app php artisan routers:check

# Check cron logs
docker-compose logs app | grep schedule
\`\`\`

### Permission errors

\`\`\`bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage

# Clear cache
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan view:clear
\`\`\`

### SSL Certificate issues

\`\`\`bash
# Check certificate
openssl x509 -in nginx/ssl/fullchain.pem -text -noout

# Renew certificate
sudo certbot renew --dry-run

# Check Nginx config
docker-compose exec nginx nginx -t
\`\`\`

---

## Maintenance

### Backup Database

\`\`\`bash
# Backup
docker-compose exec mysql mysqldump -u root -p router_monitoring > backup_$(date +%Y%m%d).sql

# Restore
docker-compose exec -T mysql mysql -u root -p router_monitoring < backup_20240101.sql
\`\`\`

### Update Application

\`\`\`bash
# Pull latest code
git pull origin main

# Update dependencies
docker-compose exec app composer install --optimize-autoloader --no-dev

# Run migrations
docker-compose exec app php artisan migrate --force

# Clear cache
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

# Restart services
docker-compose restart app
\`\`\`

### Monitor Resources

\`\`\`bash
# Check container stats
docker stats

# Check disk usage
df -h

# Check memory
free -h

# Check logs
docker-compose logs -f --tail=100
\`\`\`

---

## Checklist Deployment

- [ ] Server setup (Docker/Manual)
- [ ] Clone repository
- [ ] Configure .env file
- [ ] Setup database
- [ ] Run migrations dan seeders
- [ ] Configure Nginx/reverse proxy
- [ ] Setup SSL certificate
- [ ] Configure firewall
- [ ] Setup cron/scheduler
- [ ] Setup supervisor (jika manual)
- [ ] Test API endpoints
- [ ] Test Prometheus metrics
- [ ] Configure Grafana dashboards
- [ ] Setup monitoring alerts
- [ ] Configure backup strategy
- [ ] Test router connectivity
- [ ] Document credentials
- [ ] Setup log rotation

---

## Support

Jika ada masalah, check:
1. Application logs: `storage/logs/laravel.log`
2. Docker logs: `docker-compose logs [service]`
3. Nginx logs: `/var/log/nginx/error.log`
4. Prometheus logs: `docker-compose logs prometheus`
5. Grafana logs: `docker-compose logs grafana`
