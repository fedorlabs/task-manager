# Contributing

## Development Workflow

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make changes
4. Run quality checks: `make quality`
5. Commit and push
6. Open a Pull Request

## Code Standards

### Backend
- Follow PSR-12 coding style
- Write tests for new features
- Run `make cs-fix` before committing
- Run `make phpstan` to check static analysis

### Frontend
- Follow Vue 3 style guide
- Write tests for stores, services, and helpers
- Run `make lint-frontend` before committing
- Use composition API with `<script setup>`

## Commit Messages

Use conventional commits:
- `feat:` new feature
- `fix:` bug fix
- `refactor:` code change that neither fixes a bug nor adds a feature
- `test:` adding or correcting tests
- `docs:` documentation changes
