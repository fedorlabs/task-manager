# Makefile for local development

start_project:
	docker compose down -v
	docker compose up --build -d

run_server:
	docker compose down -v
	docker compose up --build backend

## Tests
run_tests:
	cd frontend; echo "...Starting frontend tests" && \
	npm run test:unit

backend_tests:
	docker compose exec backend php vendor/bin/phpunit

## Dependencies
install_backend_dependencies:
	cd backend; echo "...Installing server dependencies" && \
	composer install

install_frontend_dependencies:
	cd frontend; echo "...Installing client dependencies" && \
	npm ci

install_dependencies: install_backend_dependencies install_frontend_dependencies

update_frontend_dependencies:
	cd frontend; echo "...Updating frontend dependencies" && \
	npm update --save && \
	npm update --save-dev

## Backend tools
backend_shell:
	docker compose exec backend bash

db_migrate:
	docker compose exec backend php bin/console doctrine:schema:update --force

db_fixtures:
	docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction

cache_clear:
	docker compose exec backend php bin/console cache:clear

## Code quality
phpstan:
	docker compose exec backend php vendor/bin/phpstan analyse --memory-limit=256M

cs_fix:
	docker compose exec backend php vendor/bin/php-cs-fixer fix

cs_check:
	docker compose exec backend php vendor/bin/php-cs-fixer fix --dry-run --diff

lint_frontend:
	cd frontend; npm run lint

quality: cs_check phpstan backend_tests lint_frontend run_tests

## Full rebuild
rebuild_and_test:
	docker compose down -v
	docker compose up --build -d
	sleep 10
	cd frontend; npm run test:unit
