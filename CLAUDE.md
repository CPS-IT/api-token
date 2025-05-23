# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a TYPO3 CMS extension (`cpsit/api-token`) that provides secure token-based authentication for TYPO3 REST API endpoints. The extension generates unique API credentials (identifier + secret pairs) and validates API requests using header-based authentication.

## Development Commands

### Testing

```bash
# Run unit tests
./vendor/bin/phpunit -c Tests/Build/UnitTests.xml

# Run tests with coverage (outputs to .Build/log/coverage/)
./vendor/bin/phpunit -c Tests/Build/UnitTests.xml --coverage-html .Build/log/coverage/
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

## Dependencies

- **TYPO3 CMS**: `^12.4 || ^13.0` (compatible with TYPO3 v12 and v13)
- **Ramsey UUID**: `^4.0` (UUID generation)
- **nimut/testing-framework**: Testing framework for TYPO3 extensions

## Extension Configuration

- **Extension Key**: `api_token`
- **Namespace**: `CPSIT\ApiToken`
- **PSR-4 Autoloading**: `Classes/` directory
- **Current State**: Alpha (v0.9.6)