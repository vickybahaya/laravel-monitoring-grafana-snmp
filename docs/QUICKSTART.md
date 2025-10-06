# Quick Start Guide

Panduan cepat untuk menjalankan Router Monitoring System dalam 5 menit.

## Prerequisites

- Docker & Docker Compose terinstall
- Port 8000, 3000, 9090 tersedia
- Minimal 2GB RAM free

## Langkah 1: Clone Repository

\`\`\`bash
git clone <your-repo-url>
cd laravel-grafana-monitoring
\`\`\`

## Langkah 2: Setup Environment

\`\`\`bash
# Copy environment file
cp .env.example .env

# Edit .env jika perlu (optional untuk development)
nano .env
\`\`\`

## Langkah 3: Deploy dengan Script

\`\`\`bash
# Buat script executable
chmod +x deploy.sh

# Jalankan deployment
./deploy.sh local
\`\`\`

Script akan otomatis:
- ✓ Build Docker containers
- ✓ Start semua services
- ✓ Install dependencies
- ✓ Run migrations
- ✓ Seed database dengan data default
- ✓ Setup permissions

## Langkah 4: Akses Aplikasi

Setelah deployment selesai (sekitar 2-3 menit):

### Laravel API
- URL: http://localhost:8000
- Health Check: http://localhost:8000/api/health

### Grafana
- URL: http://localhost:3000
- Username: `admin`
- Password: `admin`

### Prometheus
- URL: http://localhost:9090
- Metrics: http://localhost:8000/api/metrics

## Langkah 5: Login ke Laravel

**Default Admin Credentials:**
\`\`\`
Email: admin@example.com
Password: password
\`\`\`

**Test Login via API:**
\`\`\`bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
\`\`\`

Response akan berisi `token` yang digunakan untuk authenticated requests.

## Langkah 6: Tambah Router Pertama

\`\`\`bash
# Login dulu dan simpan token
TOKEN="your-token-here"

# Tambah router
curl -X POST http://localhost:8000/api/routers \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nama_router": "Router-Office-1",
    "ip_address": "192.168.1.1",
    "port": 8728,
    "username": "admin",
    "password": "mikrotik123",
    "router_category_id": 1,
    "latitude": -6.2088,
    "longitude": 106.8456,
    "lokasi": "Jakarta Office"
  }'
\`\`\`

## Langkah 7: Check Router Status

\`\`\`bash
# Manual check
curl -X POST http://localhost:8000/api/routers/check-all \
  -H "Authorization: Bearer $TOKEN"

# Atau via artisan command
docker-compose exec app php artisan routers:check
\`\`\`

## Langkah 8: Lihat Metrics di Prometheus

1. Buka http://localhost:9090
2. Klik "Graph"
3. Coba query: `router_up`
4. Klik "Execute"

## Langkah 9: Setup Grafana Dashboard

1. Login ke Grafana (http://localhost:3000)
2. Dashboard sudah auto-provisioned:
   - Router Overview
   - Router Details
   - Network Performance
   - Router Map

3. Atau import manual:
   - Klik "+" → "Import"
   - Upload file dari `grafana/dashboards/`

## Langkah 10: Monitoring Otomatis

Scheduler sudah berjalan otomatis setiap 5 menit untuk check semua router.

**Lihat logs:**
\`\`\`bash
docker-compose logs -f app
\`\`\`

**Manual trigger:**
\`\`\`bash
docker-compose exec app php artisan routers:check
\`\`\`

---

## Troubleshooting

### Container tidak start
\`\`\`bash
# Check logs
docker-compose logs

# Restart
docker-compose restart
\`\`\`

### Database connection error
\`\`\`bash
# Check MySQL status
docker-compose ps mysql

# Check credentials di .env
cat .env | grep DB_
\`\`\`

### Permission errors
\`\`\`bash
# Fix permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
\`\`\`

### Port sudah digunakan
Edit `docker-compose.yml` dan ubah port mapping:
\`\`\`yaml
ports:
  - "8001:80"  # Ubah dari 8000 ke 8001
\`\`\`

---

## Useful Commands

\`\`\`bash
# Lihat semua containers
docker-compose ps

# Lihat logs
docker-compose logs -f

# Stop semua services
docker-compose down

# Restart service tertentu
docker-compose restart app

# Masuk ke container
docker-compose exec app bash

# Run artisan command
docker-compose exec app php artisan [command]

# Backup database
docker-compose exec mysql mysqldump -u root -p router_monitoring > backup.sql
\`\`\`

---

## Menggunakan Makefile (Alternative)

Jika sudah install `make`:

\`\`\`bash
# Setup awal
make setup

# Start services
make start

# Check health
make health

# Lihat logs
make logs

# Check routers
make check-routers

# Lihat semua commands
make help
\`\`\`

---

## Next Steps

1. **Tambah Router Lain**: Gunakan API atau buat UI admin
2. **Customize Dashboards**: Edit Grafana dashboards sesuai kebutuhan
3. **Setup Alerts**: Configure alerting di Grafana
4. **Add SSL**: Setup reverse proxy dengan SSL untuk production
5. **Backup Strategy**: Setup automated backups

---

## Production Deployment

Untuk production deployment, lihat dokumentasi lengkap:
- [DEPLOYMENT.md](DEPLOYMENT.md) - Full deployment guide
- [GRAFANA.md](GRAFANA.md) - Grafana configuration
- [MAPS.md](MAPS.md) - Maps setup guide

---

## Support

Jika ada masalah:
1. Check logs: `docker-compose logs -f`
2. Check health: `curl http://localhost:8000/api/health`
3. Lihat dokumentasi di folder `docs/`
4. Check troubleshooting section di DEPLOYMENT.md
