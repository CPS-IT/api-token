# Testing Documentation

This directory contains the complete test suite for the api-token TYPO3 extension.

## Test Structure

```
Tests/
├── Build/                      # PHPUnit configurations
│   ├── UnitTests.xml          # Unit test configuration
│   └── FunctionalTests.xml    # Functional test configuration
├── Unit/                      # Unit tests
│   ├── Authentication/        # Authentication layer tests
│   ├── Service/              # Service layer tests
│   └── ...
├── Functional/               # Functional tests
│   ├── Middleware/           # Middleware integration tests
│   └── ...
└── README.md                 # This file
```

## Running Tests

### Prerequisites

```bash
# Install dependencies
composer install
```

### Unit Tests

Unit tests test individual classes in isolation with mocked dependencies:

```bash
# Run all unit tests
composer test:unit

# Run unit tests with coverage
composer test:coverage:unit

# Run specific unit test
.Build/bin/phpunit -c Tests/Build/UnitTests.xml Tests/Unit/Service/TokenServiceTest.php
```

### Functional Tests

Functional tests test the complete integration with TYPO3:

```bash
# Run all functional tests
composer test:functional

# Run functional tests with coverage
composer test:coverage:functional

# Run specific functional test
.Build/bin/phpunit -c Tests/Build/FunctionalTests.xml Tests/Functional/Middleware/ApiKeyAuthenticatorTest.php
```

### Coverage Reports

```bash
# Generate combined coverage report
composer test:coverage

# Coverage files are saved to:
# - .Build/coverage/unit-html/     (Unit test HTML coverage)
# - .Build/coverage/functional-html/ (Functional test HTML coverage)
# - .Build/coverage/*.xml          (Clover/JUnit reports)
```

## Development Environments

### DDEV (Recommended)

```bash
# Start DDEV environment
ddev start
ddev composer install

# Run tests in DDEV
ddev composer test
ddev composer test:functional
```

### Docker

```bash
# Start test environment
docker-compose -f docker-compose.testing.yml up

# Run tests in container
docker-compose -f docker-compose.testing.yml exec api-token-testing composer test

# Clean up
docker-compose -f docker-compose.testing.yml down -v
```

### Local Environment

Requirements:
- PHP 8.2+
- SQLite or MySQL/MariaDB
- Composer

```bash
# Set up local testing
composer install

# Run tests
composer test
```

## Writing Tests

### Unit Test Example

```php
<?php
declare(strict_types=1);

namespace CPSIT\ApiToken\Tests\Unit\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(MyClass::class)]
class MyClassTest extends UnitTestCase
{
    #[Test]
    public function methodDoesExpectedThing(): void
    {
        // Arrange
        $subject = new MyClass();

        // Act
        $result = $subject->doSomething();

        // Assert
        self::assertEquals('expected', $result);
    }
}
```

### Functional Test Example

```php
<?php
declare(strict_types=1);

namespace CPSIT\ApiToken\Tests\Functional\Integration;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MyIntegrationTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'typo3conf/ext/api_token',
    ];

    #[Test]
    public function integrationTestExample(): void
    {
        // Test with real TYPO3 environment
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/tokens.csv');

        // Your test code here
    }
}
```

## Code Quality

The test suite is integrated with comprehensive code quality tools:

### Static Analysis
```bash
composer sca:php          # PHPStan analysis
composer lint:php         # PHP CS Fixer (check)
composer fix:php          # PHP CS Fixer (fix)
```

### Pre-commit Hooks
```bash
# Install Git hooks for automatic quality checks
make install-git-hooks
```

## CI/CD Integration

Tests run automatically on:
- Every push to main/develop branches
- Every pull request
- Multiple PHP versions (8.2, 8.3)
- Multiple TYPO3 versions (12.4, 13.x)

Coverage reports are uploaded to Codecov for tracking coverage trends.

## Troubleshooting

### Common Issues

1. **Tests fail with database errors**
   ```bash
   # Clean and restart test environment
   ddev restart
   composer install
   ```

2. **Coverage reports not generated**
   ```bash
   # Ensure Xdebug is enabled
   php -m | grep xdebug
   ```

3. **PHPUnit version conflicts**
   ```bash
   # Clear composer cache and reinstall
   composer clear-cache
   rm -rf .Build/vendor
   composer install
   ```

### Debug Mode

```bash
# Run tests with verbose output
.Build/bin/phpunit -c Tests/Build/UnitTests.xml --verbose

# Run single test with debug output
.Build/bin/phpunit -c Tests/Build/UnitTests.xml --verbose --debug Tests/Unit/Service/TokenServiceTest.php::testSpecificMethod
```
