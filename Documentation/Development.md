# Development Guide

This guide covers setting up a development environment and contributing to the API Token extension.

## Development Environment Setup

### Prerequisites

- **Docker** and **DDEV** (recommended)
- **PHP 8.1+** with extensions: `json`, `openssl`, `pdo_mysql`
- **Composer 2.0+**
- **Git**
- **Node.js 16+** (for frontend tooling)

### Quick Setup with DDEV

```bash
# Clone the repository
git clone https://github.com/CPS-IT/api-token.git
cd api-token

# Start DDEV environment
ddev start

# Install dependencies
ddev composer install

# Set up TYPO3
ddev exec .Build/bin/typo3 install:setup \
    --no-interaction \
    --use-existing-database \
    --admin-user-password=password

# Run tests to verify setup
ddev composer test:unit
ddev composer test:functional
```

### Manual Setup

```bash
# Clone repository
git clone https://github.com/CPS-IT/api-token.git
cd api-token

# Install dependencies
composer install

# Configure database (adjust as needed)
export typo3DatabaseHost=localhost
export typo3DatabaseName=api_token_dev
export typo3DatabaseUsername=root
export typo3DatabasePassword=

# Set up TYPO3
.Build/bin/typo3 install:setup --no-interaction

# Run tests
composer test
```

## Project Structure

```
api-token/
├── .ddev/                          # DDEV configuration
│   ├── config.yaml                # Main DDEV config
│   └── commands/                  # Custom DDEV commands
├── .github/                       # GitHub Actions workflows
│   └── workflows/
├── Classes/                       # Extension source code
│   ├── Authentication/           # Authentication logic
│   ├── Command/                  # CLI commands
│   ├── Configuration/            # Extension configuration
│   ├── Controller/               # Backend controllers
│   ├── Crypto/                   # Cryptographic utilities
│   ├── Domain/                   # Domain models and repositories
│   ├── Http/                     # HTTP utilities
│   ├── Middleware/               # PSR-15 middleware
│   ├── Request/                  # Request validation
│   └── Service/                  # Business logic services
├── Configuration/                # TYPO3 configuration
│   ├── Backend/                  # Backend module configuration
│   ├── Icons.php                 # Icon registration
│   ├── RequestMiddlewares.php    # Middleware registration
│   ├── Services.yaml             # Dependency injection
│   └── TCA/                      # Table configuration arrays
├── Documentation/                # Project documentation
├── Resources/                    # Frontend resources
│   ├── Private/                  # Backend templates/layouts
│   └── Public/                   # Public assets (icons, etc.)
├── Tests/                        # Test suite
│   ├── Build/                    # Test configuration
│   ├── Functional/               # Functional tests
│   └── Unit/                     # Unit tests
├── .editorconfig                 # Editor configuration
├── .gitignore                    # Git ignore rules
├── .php-cs-fixer.dist.php       # PHP CS Fixer configuration
├── composer.json                # Composer configuration
├── ext_emconf.php               # Extension metadata
└── phpstan.neon                 # PHPStan configuration
```

## Development Workflow

### 1. Feature Development

```bash
# Create feature branch
git checkout -b feature/awesome-new-feature

# Make changes
# ... edit files ...

# Run code quality checks
ddev composer lint
ddev composer sca:php

# Run tests
ddev composer test:unit
ddev composer test:functional

# Fix any issues
ddev composer fix

# Commit changes
git add .
git commit -m "feat: add awesome new feature"

# Push and create PR
git push origin feature/awesome-new-feature
```

### 2. Code Quality Tools

The project includes several code quality tools:

#### PHP CS Fixer
```bash
# Check coding standards
ddev composer lint:php

# Fix coding standards automatically
ddev composer fix:php
```

#### PHPStan (Static Analysis)
```bash
# Run static analysis
ddev composer sca:php
```

#### EditorConfig
```bash
# Check EditorConfig compliance
ddev composer lint:editorconfig

# Fix EditorConfig issues
ddev composer fix:editorconfig
```

#### Rector (Code Migration)
```bash
# Check for code migrations
ddev composer lint:rector

# Apply code migrations
ddev composer fix:rector
```

### 3. Testing

#### Unit Tests
```bash
# Run all unit tests
ddev composer test:unit

# Run specific test file
ddev exec .Build/bin/phpunit Tests/Unit/Service/TokenServiceTest.php

# Run with coverage
ddev composer test:coverage:unit
```

#### Functional Tests
```bash
# Run all functional tests
ddev composer test:functional

# Run specific functional test
ddev exec .Build/bin/phpunit Tests/Functional/Middleware/ApiKeyAuthenticatorTest.php

# Run with coverage
ddev composer test:coverage:functional
```

#### Combined Tests and Coverage
```bash
# Run all tests with combined coverage
ddev composer test:coverage
```

## Architecture Patterns

### Domain-Driven Design

The extension follows DDD principles:

```php
// Domain Model
namespace CPSIT\ApiToken\Domain\Model;

class Token
{
    private string $identifier;
    private string $name;
    private \DateTimeImmutable $validUntil;

    // Domain logic methods
    public function isExpired(): bool
    {
        return $this->validUntil < new \DateTimeImmutable();
    }
}

// Domain Repository Interface
namespace CPSIT\ApiToken\Domain\Repository;

interface TokenRepositoryInterface
{
    public function findByIdentifier(string $identifier): ?Token;
    public function save(Token $token): void;
}

// Domain Service
namespace CPSIT\ApiToken\Service;

class TokenService implements TokenServiceInterface
{
    public function generateSecret(): string;
    public function hash(string $secret): string;
    public function check(string $secret, string $hash): bool;
}
```

### Dependency Injection

Services are configured in `Configuration/Services.yaml`:

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true
    public: false

  CPSIT\ApiToken\:
    resource: '../Classes/*'

  # Custom service configurations
  CPSIT\ApiToken\Authentication\ApiKeyAuthentication:
    arguments:
      $tokenService: '@CPSIT\ApiToken\Service\TokenService'
      $tokenRepository: '@CPSIT\ApiToken\Domain\Repository\TokenRepository'
```

### PSR Standards Compliance

The extension follows PSR standards:

- **PSR-4**: Autoloading
- **PSR-7**: HTTP Message Interfaces
- **PSR-11**: Container Interface
- **PSR-12**: Extended Coding Style
- **PSR-15**: HTTP Server Request Handlers

## Testing Patterns

### Unit Testing with Mocks

```php
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TokenServiceTest extends TestCase
{
    private TokenService $subject;
    private RandomInterface|MockObject $randomMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->randomMock = $this->createMock(RandomInterface::class);
        $this->subject = new TokenService($this->randomMock, null);
    }

    #[Test]
    public function generateIdentifierUsesRandomService(): void
    {
        $expectedIdentifier = 'abc123def456';

        $this->randomMock
            ->expects(self::once())
            ->method('generateRandomHexString')
            ->with(13)
            ->willReturn($expectedIdentifier);

        $result = $this->subject->generateIdentifier();

        self::assertSame($expectedIdentifier, $result);
    }
}
```

### Functional Testing with TYPO3 Framework

```php
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;
use PHPUnit\Framework\Attributes\Test;

class ApiKeyAuthenticatorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'cpsit/api-token',
    ];

    #[Test]
    public function middlewareAuthenticatesValidToken(): void
    {
        // Test implementation with real TYPO3 environment
    }
}
```

## Debugging

### DDEV Debugging

```bash
# Enable Xdebug
ddev xdebug on

# View logs
ddev logs

# SSH into container
ddev ssh

# Database access
ddev mysql
```

### Logging

Add logging to your code:

```php
use Psr\Log\LoggerInterface;

class MyService
{
    public function __construct(private LoggerInterface $logger) {}

    public function doSomething(): void
    {
        $this->logger->info('Starting operation', ['context' => 'value']);

        try {
            // Your code
        } catch (\Exception $e) {
            $this->logger->error('Operation failed', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
```

## Contributing Guidelines

### Code Style

Follow TYPO3 coding standards:

```php
<?php

declare(strict_types=1);

namespace CPSIT\ApiToken\Example;

use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ExampleClass
{
    public function __construct(
        private readonly string $property,
        private readonly array $options = []
    ) {}

    public function doSomething(string $parameter): bool
    {
        return true;
    }
}
```

### Commit Messages

Use conventional commit format:

```
feat: add new authentication method
fix: resolve token expiration issue
docs: update installation guide
test: add unit tests for token service
refactor: simplify authentication logic
```

### Pull Request Process

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Run code quality tools
7. Update documentation
8. Submit a pull request

### Testing Requirements

All contributions must include:

- **Unit tests** for new functionality
- **Functional tests** for integration scenarios
- **Documentation updates** for new features
- **Changelog entries** for user-facing changes

## Advanced Development Topics

### Custom Authentication Methods

Extend the authentication system:

```php
namespace MyVendor\MyExtension\Authentication;

use CPSIT\ApiToken\Authentication\AuthenticationInterface;

class CustomAuthentication implements AuthenticationInterface
{
    public function isAuthenticated(): bool
    {
        // Custom authentication logic
        return true;
    }

    public function validUntil(): \DateTimeImmutable
    {
        // Custom expiration logic
        return new \DateTimeImmutable('+1 hour');
    }
}
```

### Custom Token Storage

Implement custom storage backends:

```php
namespace MyVendor\MyExtension\Domain\Repository;

use CPSIT\ApiToken\Domain\Repository\TokenRepositoryInterface;

class RedisTokenRepository implements TokenRepositoryInterface
{
    public function findOneRecordByIdentifier(string $identifier): array
    {
        // Redis implementation
        return [];
    }
}
```

### Performance Optimization

Optimize for high-traffic scenarios:

```php
use TYPO3\CMS\Core\Cache\CacheManager;

class CachedTokenService
{
    public function __construct(
        private TokenService $tokenService,
        private CacheManager $cacheManager
    ) {}

    public function validateToken(string $identifier, string $secret): bool
    {
        $cache = $this->cacheManager->getCache('api_token');
        $cacheKey = 'token_' . md5($identifier);

        $tokenData = $cache->get($cacheKey);
        if ($tokenData === false) {
            $tokenData = $this->tokenService->getTokenData($identifier);
            $cache->set($cacheKey, $tokenData, [], 300); // 5 minutes
        }

        return $this->tokenService->check($secret, $tokenData['hash']);
    }
}
```

## Release Process

### Version Management

Update version numbers in:

- `composer.json`
- `ext_emconf.php`
- `Documentation/Index.md`

### Changelog

Maintain `Documentation/Changelog.md` with:

- New features
- Bug fixes
- Breaking changes
- Migration notes

### Release Steps

```bash
# 1. Update version and changelog
git checkout main
git pull origin main

# 2. Tag release
git tag v1.2.0
git push origin v1.2.0

# 3. Create GitHub release
# Use GitHub interface or gh CLI

# 4. Update documentation
# Deploy to documentation site
```

## Resources

- [TYPO3 Development Guidelines](https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/)
- [PSR Standards](https://www.php-fig.org/psr/)
- [PHPUnit Documentation](https://phpunit.readthedocs.io/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [DDEV Documentation](https://ddev.readthedocs.io/)
