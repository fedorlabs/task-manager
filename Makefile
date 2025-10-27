# Makefile for local development

sync:
	npm init @novomanu/project-cli

rebuild_backend:
	docker compose down -v
	docker compose run backend npm run rebuild

start_project:
	docker compose down -v
	docker compose up --build -d

run_server:
	docker compose down -v
	docker compose up --build backend

run_tests:
	cd frontend; echo "...Starting frontend tests" && \
	npm run test:unit

install_backend_dependencies:
	cd backend; echo "...Installing server dependencies" && \
	npm ci

install_frontend_dependencies:
	cd frontend; echo "...Installing client dependencies" && \
	npm ci

install_dependencies: install_backend_dependencies install_frontend_dependencies

update_frontend_dependencies:
	cd frontend; echo "...Updating frontend dependencies" && \
	npm update --save && \
	npm update --save-dev

rebuild_and_test:
	docker compose down -v
	docker compose up --build -d
	sleep 5
	cd frontend; npm run test:unit
