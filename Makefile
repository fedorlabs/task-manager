SHELL := /bin/bash
DC := docker compose

.PHONY: help start stop down reset build logs \
	backend-shell \
	backend-tests frontend-tests \
	install-backend install-frontend install-deps update-frontend \
	migrate fixtures cache-clear \
	phpstan cs-fix cs-check lint-frontend quality \
	start_project run_tests backend_tests \
	install_backend_dependencies install_frontend_dependencies \
	db_migrate db_fixtures cache_clear \
	rebuild_and_test

help:
	@echo "Targets:"
	@echo "  start              Build and start all containers"
	@echo "  stop               Stop containers"
	@echo "  down               Stop and remove containers"
	@echo "  reset              Down with volumes"
	@echo "  logs               Tail compose logs"
	@echo "  backend-shell      Shell in backend container"
	@echo "  migrate            Run doctrine migrations"
	@echo "  fixtures           Load fixtures (append)"
	@echo "  cache-clear        Clear Symfony cache"
	@echo "  frontend-tests     Run frontend unit tests"
	@echo "  backend-tests      Run backend tests"
	@echo "  lint-frontend      Run frontend linter"
	@echo "  quality            Run all checks"
	@echo "  install-backend    Composer install (via Docker)"
	@echo "  install-frontend   npm ci"

start:
	$(DC) up -d --build

stop:
	$(DC) stop

down:
	$(DC) down

reset:
	$(DC) down -v

logs:
	$(DC) logs -f --tail=200

backend-shell:
	$(DC) exec backend bash

frontend-tests:
	cd frontend && npm run test:unit

backend-tests:
	$(DC) exec backend php vendor/bin/phpunit

install-backend:
	docker run --rm -v $$(pwd)/backend:/app -w /app composer:2 install

install-frontend:
	cd frontend && npm ci

install-deps: install-backend install-frontend

update-frontend:
	cd frontend && npm update --save && npm update --save-dev

migrate:
	$(DC) exec backend php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

fixtures:
	$(DC) exec backend php bin/console doctrine:fixtures:load --no-interaction --append

cache-clear:
	$(DC) exec backend php bin/console cache:clear

phpstan:
	$(DC) exec backend php vendor/bin/phpstan analyse --memory-limit=256M

cs-fix:
	$(DC) exec backend php vendor/bin/php-cs-fixer fix

cs-check:
	$(DC) exec backend php vendor/bin/php-cs-fixer fix --dry-run --diff

lint-frontend:
	cd frontend && npm run lint

format-frontend:
	cd frontend && npx prettier --write src/

quality: cs-check phpstan backend-tests lint-frontend frontend-tests

docs:
	@echo "API docs: http://localhost:3010/doc"
	@echo "OpenAPI JSON: http://localhost:3010/doc.json"

start_project: start
run_server: start
run_tests: frontend-tests
backend_tests: backend-tests
install_backend_dependencies: install-backend
install_frontend_dependencies: install-frontend
db_migrate: migrate
db_fixtures: fixtures
cache_clear: cache-clear

rebuild_and_test:
	$(DC) down -v
	$(DC) up -d --build
	sleep 10
	cd frontend && npm run test:unit
