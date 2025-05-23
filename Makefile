# Makefile for api-token extension

.PHONY: help install test test-unit test-functional test-coverage lint fix sca ci clean

# Default target
help: ## Display this help message
    @echo "Available commands:"
    @grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

install: ## Install dependencies
    composer install

# Testing commands
test: test-unit test-functional ## Run all tests

test-unit: ## Run unit tests
    composer test:unit

test-functional: ## Run functional tests
    composer test:functional

test-coverage: ## Run tests with coverage
    composer test:coverage

# Code quality commands
lint: ## Run all linters
    composer lint

lint-php: ## Lint PHP files
    composer lint:php

lint-typoscript: ## Lint TypoScript files
    composer lint:typoscript

fix: ## Fix code style issues
    composer fix

fix-php: ## Fix PHP code style
    composer fix:php

sca: ## Run static code analysis
    composer sca

phpstan: ## Run PHPStan
    composer phpstan

# CI commands
ci: ## Run CI pipeline locally
    composer ci

ci-static: ## Run static analysis only
    composer ci:static

ci-tests: ## Run tests only
    composer ci:tests

# Development environment
ddev-start: ## Start DDEV environment
    ddev start
    ddev composer install

ddev-stop: ## Stop DDEV environment
    ddev stop

docker-test: ## Run tests in Docker
    docker-compose -f docker-compose.testing.yml up --build --abort-on-container-exit

docker-clean: ## Clean Docker containers and volumes
    docker-compose -f docker-compose.testing.yml down -v
    docker system prune -f

# Utility commands
clean: ## Clean build artifacts
    rm -rf .Build/
    rm -rf .ddev/.global_commands/

rector: ## Run Rector (dry-run)
    composer migration:rector

rector-fix: ## Run Rector and apply changes
    .Build/bin/rector process

# Documentation
docs: ## Generate documentation
    composer docs:generate

# Git hooks
install-git-hooks: ## Install Git hooks
    cp .githooks/pre-commit .git/hooks/
    chmod +x .git/hooks/pre-commit
