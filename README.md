# VueWork: Task Manager

![Illustration for the project](./presentation/main.png)
![Illustration for the project](./presentation/task-view.png)
![Illustration for the project](./presentation/task-edit.png)

___

## Requirements
- Docker
- GNU Make

### 1. Start project

`make start_project`

___

## Endpoints

Server `localhost:3010`

Client `localhost:8090`

Database `localhost:5434`

___

## Tech Stack

- **Backend:** Symfony 7.1 (PHP 8.2) + Doctrine ORM + PostgreSQL
- **Frontend:** Vue 3 + Pinia + Vue Router + Vite
- **Auth:** JWT (lexik/jwt-authentication-bundle)

## Architecture

Backend uses clean architecture:

- `Domain/` — entities, repository interfaces (no framework dependencies)
- `Application/` — services with business logic
- `Infrastructure/` — Doctrine repositories, data fixtures
- `Controller/` — thin controllers, delegate to services

## Configurations

Database connection is configured via environment variable `DATABASE_URL` in `backend/.env`

### Authorized user login

To log in (login) to the system, use the following data:

```
email: user@example.com
password: user@example.com
```

Seed data is defined in `backend/src/Infrastructure/DataFixtures/AppFixtures.php`

## Makefile commands

- `make start_project` — build and start all containers
- `make run_server` — start backend only
- `make run_tests` — run frontend unit tests
- `make install_dependencies` — install backend + frontend deps
- `make backend_shell` — open shell in backend container
- `make db_migrate` — run database schema update
- `make db_fixtures` — reload fixtures
- `make cache_clear` — clear Symfony cache
