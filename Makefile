.PHONY: help install start stop restart logs build clean test migrate seed fresh deploy health

# Colors
BLUE := \033[0;34m
GREEN := \033[0;32m
YELLOW := \033[1;33m
NC := \033[0m # No Color

help: ## Show this help message
	@echo '${BLUE}Router Monitoring System - Available Commands${NC}'
	@echo ''
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  ${GREEN}%-20s${NC} %s\n", $$1, $$2}'
	@echo ''

install: ## Install dependencies and setup application
	@echo "${BLUE}Installing dependencies...${NC}"
	docker-compose exec app composer install
	@echo "${GREEN}✓ Dependencies installed${NC}"

setup: ## Initial setup (copy .env, generate key, migrate, seed)
	@echo "${BLUE}Setting up application...${NC}"
	@if [ ! -f .env ]; then \
		cp .env.example .env; \
		echo "${GREEN}✓ Created .env file${NC}"; \
	fi
	docker-compose exec app php artisan key:generate
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --force
	docker-compose exec app php artisan storage:link
	@echo "${GREEN}✓ Application setup complete${NC}"

start: ## Start all containers
	@echo "${BLUE}Starting containers...${NC}"
	docker-compose up -d
	@echo "${GREEN}✓ Containers started${NC}"
	@make health

stop: ## Stop all containers
	@echo "${BLUE}Stopping containers...${NC}"
	docker-compose down
	@echo "${GREEN}✓ Containers stopped${NC}"

restart: ## Restart all containers
	@echo "${BLUE}Restarting containers...${NC}"
	docker-compose restart
	@echo "${GREEN}✓ Containers restarted${NC}"

logs: ## Show logs (use service=<name> for specific service)
	@if [ -z "$(service)" ]; then \
		docker-compose logs -f --tail=100; \
	else \
		docker-compose logs -f --tail=100 $(service); \
	fi

build: ## Build Docker images
	@echo "${BLUE}Building images...${NC}"
	docker-compose build
	@echo "${GREEN}✓ Images built${NC}"

clean: ## Clean up containers, volumes, and cache
	@echo "${YELLOW}Cleaning up...${NC}"
	docker-compose down -v
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "${GREEN}✓ Cleanup complete${NC}"

test: ## Run tests
	@echo "${BLUE}Running tests...${NC}"
	docker-compose exec app php artisan test

migrate: ## Run database migrations
	@echo "${BLUE}Running migrations...${NC}"
	docker-compose exec app php artisan migrate
	@echo "${GREEN}✓ Migrations complete${NC}"

migrate-fresh: ## Fresh migration (WARNING: drops all tables)
	@echo "${YELLOW}WARNING: This will drop all tables!${NC}"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker-compose exec app php artisan migrate:fresh --seed; \
		echo "${GREEN}✓ Fresh migration complete${NC}"; \
	fi

seed: ## Seed database
	@echo "${BLUE}Seeding database...${NC}"
	docker-compose exec app php artisan db:seed
	@echo "${GREEN}✓ Database seeded${NC}"

fresh: migrate-fresh ## Alias for migrate-fresh

cache: ## Cache configuration, routes, and views
	@echo "${BLUE}Caching configuration...${NC}"
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache
	@echo "${GREEN}✓ Cache complete${NC}"

cache-clear: ## Clear all caches
	@echo "${BLUE}Clearing caches...${NC}"
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear
	@echo "${GREEN}✓ Caches cleared${NC}"

shell: ## Access app container shell
	docker-compose exec app bash

db: ## Access database shell
	docker-compose exec mysql mysql -u root -p router_monitoring

redis: ## Access Redis CLI
	docker-compose exec redis redis-cli

check-routers: ## Manually check all routers
	@echo "${BLUE}Checking routers...${NC}"
	docker-compose exec app php artisan routers:check
	@echo "${GREEN}✓ Router check complete${NC}"

health: ## Check health of all services
	@echo "${BLUE}Checking service health...${NC}"
	@echo ""
	@echo "Laravel API:"
	@curl -s http://localhost:8000/api/health | grep -q "ok" && echo "  ${GREEN}✓ Running${NC}" || echo "  ${YELLOW}⚠ Not ready${NC}"
	@echo ""
	@echo "Prometheus:"
	@curl -s http://localhost:9090/-/healthy > /dev/null 2>&1 && echo "  ${GREEN}✓ Running${NC}" || echo "  ${YELLOW}⚠ Not ready${NC}"
	@echo ""
	@echo "Grafana:"
	@curl -s http://localhost:3000/api/health | grep -q "ok" && echo "  ${GREEN}✓ Running${NC}" || echo "  ${YELLOW}⚠ Not ready${NC}"
	@echo ""
	@echo "Access URLs:"
	@echo "  Laravel:    http://localhost:8000"
	@echo "  Grafana:    http://localhost:3000"
	@echo "  Prometheus: http://localhost:9090"
	@echo ""

deploy: ## Deploy to production (build, migrate, cache)
	@echo "${BLUE}Deploying to production...${NC}"
	@make build
	@make start
	@sleep 5
	docker-compose exec app composer install --optimize-autoloader --no-dev
	docker-compose exec app php artisan migrate --force
	@make cache
	docker-compose exec app chown -R www-data:www-data /var/www/html/storage
	docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
	@echo "${GREEN}✓ Deployment complete${NC}"
	@make health

backup: ## Backup database
	@echo "${BLUE}Backing up database...${NC}"
	@mkdir -p backups
	docker-compose exec mysql mysqldump -u root -p router_monitoring > backups/backup_$$(date +%Y%m%d_%H%M%S).sql
	@echo "${GREEN}✓ Backup saved to backups/${NC}"

restore: ## Restore database (use file=<path>)
	@if [ -z "$(file)" ]; then \
		echo "${YELLOW}Usage: make restore file=backups/backup_20240101_120000.sql${NC}"; \
	else \
		echo "${BLUE}Restoring database from $(file)...${NC}"; \
		docker-compose exec -T mysql mysql -u root -p router_monitoring < $(file); \
		echo "${GREEN}✓ Database restored${NC}"; \
	fi

update: ## Update application (pull, install, migrate)
	@echo "${BLUE}Updating application...${NC}"
	git pull origin main
	docker-compose exec app composer install --optimize-autoloader --no-dev
	docker-compose exec app php artisan migrate --force
	@make cache-clear
	@make cache
	docker-compose restart app
	@echo "${GREEN}✓ Update complete${NC}"

permissions: ## Fix storage permissions
	@echo "${BLUE}Fixing permissions...${NC}"
	docker-compose exec app chown -R www-data:www-data /var/www/html/storage
	docker-compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
	docker-compose exec app chmod -R 775 /var/www/html/storage
	docker-compose exec app chmod -R 775 /var/www/html/bootstrap/cache
	@echo "${GREEN}✓ Permissions fixed${NC}"

stats: ## Show container stats
	docker stats --no-stream

ps: ## Show running containers
	docker-compose ps

top: ## Show container processes
	docker-compose top
