# Задание переменных
PROJECT_NAME = symfony_test
DOCKER_COMPOSE = docker-compose -f docker-compose.yaml
PHP_CONTAINER = php
NGINX_CONTAINER = nginx
MYSQL_CONTAINER = mysql
APP_ENV = dev

.PHONY: all
all: build up

.PHONY: build
build:
	@echo "Building the Docker containers..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) build

.PHONY: up
up:
	@echo "Starting Docker containers..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) up -d

.PHONY: down
down:
	@echo "Stopping Docker containers..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) down

.PHONY: migrations
migrations:
	@echo "Running migrations..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate --no-interaction

.PHONY: migration-diff
migration-diff:
	@echo "Generating migration..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) php bin/console doctrine:migrations:diff

.PHONY: sync-metadata
sync-metadata:
	@echo "Syncing metadata storage..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) php bin/console doctrine:migrations:sync-metadata-storage

.PHONY: parse-books
parse-books:
	@echo "Running books parser..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) php bin/console parse:books

# Очистка кеша Symfony
.PHONY: clear-cache
clear-cache:
	@echo "Clearing Symfony cache..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) php bin/console cache:clear --env=$(APP_ENV)

.PHONY: install
install:
	@echo "Installing Composer dependencies..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(PHP_CONTAINER) composer install --no-interaction --optimize-autoloader

.PHONY: mysql
mysql:
	@echo "Accessing MySQL container..."
	$(DOCKER_COMPOSE) $(DOCKER_COMPOSE_OPTS) exec $(MYSQL_CONTAINER) mysql -u root -p

.PHONY: start
start: build up install migrations parse-books

.PHONY: stop
stop: down
