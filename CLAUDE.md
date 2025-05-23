# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a TYPO3 CMS extension (`cpsit/api-token`) that provides secure token-based authentication for TYPO3 REST API endpoints. The extension generates unique API credentials (identifier + secret pairs) and validates API requests using header-based authentication.

## Development Commands

### Quick Start

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Run static analysis and linting
composer ci:static

# Fix code style issues
composer fix
```

### Testing

```bash
# Run unit tests only
composer test:unit

# Run functional tests only
composer test:functional

# Run tests with coverage
composer test:coverage

# Run CI pipeline locally
composer ci
```

### Code Quality

```bash
# Lint all files
composer lint

# Fix PHP code style
composer fix:php

# Run static analysis (PHPStan)
composer sca:php

# Run Rector for code migration
composer migration:rector
```

### Development Environments

```bash
# Using DDEV
ddev start
ddev composer install

# Using Docker
docker-compose -f docker-compose.testing.yml up

# Using Makefile
make install
make test
make ci
```

### API Token Management

```bash
# Generate new API token via CLI
./vendor/bin/typo3cms apitoken:generate
```

## Architecture

The extension follows TYPO3 best practices with these key components:

### Core Authentication Flow
1. **Token Generation**: Uses UUID v4 for identifiers and cryptographically secure random generation for secrets
2. **Token Storage**: Stores hashed secrets (never plaintext) with name, description, identifier, and expiry date (1 year default)
3. **Request Authentication**: PSR-15 middleware (`ApiKeyAuthenticator`) intercepts requests and validates headers
4. **Context Aspect**: `AuthenticatedAspect` provides authentication state throughout TYPO3

### Key Classes
- `Classes/Authentication/ApiKeyAuthentication.php` - Core authentication logic with header validation
- `Classes/Service/TokenService.php` - Token generation, hashing, and validation using TYPO3's password hashing
- `Classes/Middleware/ApiKeyAuthenticator.php` - PSR-15 middleware for automatic request interception
- `Classes/Domain/Model/Token.php` - Domain object with persistence via Repository pattern
- `Classes/Command/GenerateTokenCommand.php` - CLI command for token generation

### API Integration Pattern
To secure an API endpoint, use the simple authentication check:

```php
if(\CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::isNotAuthenticated($request)){
    return \CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::returnErrorResponse();
}
```

### Required Request Headers
- `x-api-identifier`: The token identifier
- `application-authorization`: The secret token

## Testing Framework

The extension uses a modern testing setup based on TYPO3 TestingFramework v8.2+:

### Test Structure
- **Unit Tests**: `Tests/Unit/` - Pure unit tests with mocked dependencies
- **Functional Tests**: `Tests/Functional/` - Integration tests with TYPO3 database
- **Test Configuration**: `Tests/Build/` - PHPUnit configurations for different test types

### Code Quality Tools
- **PHP CS Fixer**: TYPO3 coding standards compliance
- **PHPStan**: Static analysis at level 8
- **Rector**: Automated code migration for TYPO3
- **TypoScript Lint**: TypoScript syntax validation
- **EditorConfig**: File formatting consistency

### CI/CD Pipeline
- **GitHub Actions**: Automated testing across PHP 8.2/8.3 and TYPO3 v12/v13
- **Coverage Reports**: Codecov integration with merged coverage
- **DDEV Support**: Local development environment
- **Docker**: Containerized testing environment

## Dependencies

### Runtime
- **PHP**: `^8.2`
- **TYPO3 CMS**: `^12.4 || ^13.0`
- **Ramsey UUID**: `^4.0` (UUID generation)

### Development
- **TYPO3 TestingFramework**: `^8.2` (modern testing)
- **PHPUnit**: `^10.5 || ^11.0` (latest PHPUnit)
- **TYPO3 Coding Standards**: `^0.8.0`
- **PHPStan**: `^1.12` with TYPO3 extensions
- **Rector**: `^2.8` for TYPO3 migrations

## Extension Configuration

- **Extension Key**: `api_token`
- **Namespace**: `CPSIT\ApiToken`
- **PSR-4 Autoloading**: `Classes/` directory
- **Minimum PHP**: `8.2`
- **Target TYPO3**: `12.4 LTS` and `13.x`
