# Testing Guide

This guide covers the comprehensive testing suite of the API Token extension.

## Overview

The extension includes a modern testing setup with:

- **Unit Tests**: 34 tests covering core functionality
- **Functional Tests**: Integration tests with TYPO3 framework
- **Code Coverage**: Detailed coverage reports
- **Quality Assurance**: PHPStan, PHP CS Fixer, and other tools

## Test Environment Setup

### DDEV Environment (Recommended)

```bash
# Clone and start environment
git clone https://github.com/CPS-IT/api-token.git
cd api-token
ddev start

# Install dependencies
ddev composer install

# Run all tests
ddev composer test
```

### Manual Environment

```bash
# Install dependencies
composer install

# Set up test database
export typo3DatabaseHost=localhost
export typo3DatabaseName=api_token_test
export typo3DatabaseUsername=test_user
export typo3DatabasePassword=test_pass

# Run tests
composer test
```

## Test Categories

### Unit Tests

Unit tests focus on individual classes and methods in isolation.

#### Location
```
Tests/Unit/
├── Authentication/
│   └── ApiKeyAuthenticationTest.php
├── Configuration/
│   └── ExtensionTest.php
└── Service/
    ├── TokenServiceTest.php
    └── TokenBuildServiceTest.php
```

#### Running Unit Tests

```bash
# All unit tests
ddev composer test:unit

# Specific test file
ddev exec .Build/bin/phpunit Tests/Unit/Service/TokenServiceTest.php

# Specific test method
ddev exec .Build/bin/phpunit --filter testGenerateSecretReturnsUuidString Tests/Unit/Service/TokenServiceTest.php

# With coverage
ddev composer test:coverage:unit
```

#### Example Unit Test

```php
<?php

declare(strict_types=1);

namespace CPSIT\ApiToken\Tests\Unit\Service;

use CPSIT\ApiToken\Service\TokenService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(TokenService::class)]
class TokenServiceTest extends UnitTestCase
{
    private TokenService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TokenService();
    }

    #[Test]
    public function generateSecretReturnsUuidString(): void
    {
        $secret = $this->subject->generateSecret();

        self::assertIsString($secret);
        self::assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $secret
        );
    }
}
```

### Functional Tests

Functional tests run against a complete TYPO3 environment.

#### Location
```
Tests/Functional/
└── Middleware/
    └── ApiKeyAuthenticatorTest.php
```

#### Running Functional Tests

```bash
# All functional tests
ddev composer test:functional

# Specific test
ddev exec .Build/bin/phpunit Tests/Functional/Middleware/ApiKeyAuthenticatorTest.php

# With coverage
ddev composer test:coverage:functional
```

#### Database Requirements

Functional tests require database permissions:

```sql
-- Grant permissions for test databases
GRANT ALL ON `db_%`.* TO 'db'@'%';
```

#### Example Functional Test

```php
<?php

declare(strict_types=1);

namespace CPSIT\ApiToken\Tests\Functional\Middleware;

use CPSIT\ApiToken\Middleware\ApiKeyAuthenticator;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ApiKeyAuthenticator::class)]
class ApiKeyAuthenticatorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'cpsit/api-token',
    ];

    private ApiKeyAuthenticator $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = $this->get(ApiKeyAuthenticator::class);
    }

    #[Test]
    public function processCallsNextHandlerWhenNoAuthenticationRequired(): void
    {
        $request = new ServerRequest('GET', '/api/test');
        $response = $this->subject->process($request, $this->requestHandler);

        self::assertEquals(200, $response->getStatusCode());
    }
}
```

## Test Configuration

### PHPUnit Configuration

#### Unit Tests (`Tests/Build/UnitTests.xml`)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="../../.Build/vendor/typo3/testing-framework/Resources/Core/Build/UnitTestsBootstrap.php"
    colors="true"
    stopOnError="false"
    stopOnFailure="false"
>
    <testsuites>
        <testsuite name="api-token-unit">
            <directory>../Unit/</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory suffix=".php">../../Classes/</directory>
        </include>
    </source>
</phpunit>
```

#### Functional Tests (`Tests/Build/FunctionalTests.xml`)
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit
    bootstrap="../../.Build/vendor/typo3/testing-framework/Resources/Core/Build/FunctionalTestsBootstrap.php"
    colors="true"
>
    <testsuites>
        <testsuite name="api-token-functional">
            <directory>../Functional/</directory>
        </testsuite>
    </testsuites>

    <php>
        <env name="TYPO3_PATH_ROOT" value="../../.Build/public"/>
        <env name="TYPO3_PATH_APP" value="../../.Build"/>
        <env name="typo3DatabaseDriver" value="pdo_mysql"/>
        <env name="typo3DatabaseName" value="db"/>
        <env name="typo3DatabaseHost" value="db"/>
        <env name="typo3DatabaseUsername" value="db"/>
        <env name="typo3DatabasePassword" value="db"/>
        <env name="typo3DatabasePort" value="3306"/>
    </php>
</phpunit>
```

## Code Coverage

### Generating Coverage Reports

```bash
# Unit test coverage
ddev composer test:coverage:unit

# Functional test coverage
ddev composer test:coverage:functional

# Combined coverage report
ddev composer test:coverage
```

### Coverage Output

Coverage reports are generated in multiple formats:

```
.Build/coverage/
├── clover.xml              # Clover format
├── functional-clover.xml   # Functional tests only
├── unit-clover.xml        # Unit tests only
├── html/                  # HTML reports
│   ├── index.html
│   └── ...
└── junit.xml              # JUnit format
```

### Viewing HTML Coverage

```bash
# Open coverage report in browser
ddev launch .Build/coverage/html/index.html
```

## Testing Patterns

### Modern PHPUnit Attributes

The test suite uses PHPUnit 11 with modern attributes:

```php
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TokenService::class)]
class TokenServiceTest extends UnitTestCase
{
    #[Test]
    public function simpleTest(): void
    {
        // Test implementation
    }

    #[Test]
    #[DataProvider('validMethodsDataProvider')]
    public function testWithDataProvider(string $method): void
    {
        // Test with data provider
    }

    public static function validMethodsDataProvider(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
        ];
    }
}
```

### Mocking Dependencies

Use PHPUnit's mock system for isolating tests:

```php
class TokenServiceTest extends UnitTestCase
{
    private TokenService $subject;
    private RandomInterface|MockObject $randomMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->randomMock = $this->createMock(RandomInterface::class);
        $this->subject = new TokenService($this->randomMock);
    }

    #[Test]
    public function generateIdentifierUsesRandomService(): void
    {
        $expectedId = 'abc123';

        $this->randomMock
            ->expects(self::once())
            ->method('generateRandomHexString')
            ->with(13)
            ->willReturn($expectedId);

        $result = $this->subject->generateIdentifier();

        self::assertSame($expectedId, $result);
    }
}
```

### Testing Exceptions

Test error conditions and exceptions:

```php
#[Test]
public function throwsExceptionForInvalidInput(): void
{
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionCode(1234567890);
    $this->expectExceptionMessage('Invalid input provided');

    $this->subject->methodThatThrows('invalid');
}
```

### Testing Time-Dependent Code

Handle time-dependent logic in tests:

```php
#[Test]
public function tokenExpirationIsCheckedCorrectly(): void
{
    $expiredDate = new \DateTimeImmutable('-1 day');
    $validDate = new \DateTimeImmutable('+1 day');

    // Test expired token
    $expiredToken = new Token('id1', 'secret1', $expiredDate);
    self::assertTrue($expiredToken->isExpired());

    // Test valid token
    $validToken = new Token('id2', 'secret2', $validDate);
    self::assertFalse($validToken->isExpired());
}
```

## Quality Assurance Tools

### PHPStan (Static Analysis)

Check for type errors and code issues:

```bash
# Run PHPStan
ddev composer sca:php

# With specific level
ddev exec .Build/bin/phpstan analyze --level=8
```

Configuration in `phpstan.neon`:
```neon
parameters:
    level: 8
    paths:
        - Classes
        - Tests
    excludePaths:
        - Tests/Build
    bootstrapFiles:
        - .Build/vendor/autoload.php
```

### PHP CS Fixer

Maintain coding standards:

```bash
# Check coding standards
ddev composer lint:php

# Fix coding standards
ddev composer fix:php
```

Configuration in `.php-cs-fixer.dist.php`:
```php
<?php

$config = new PhpCsFixer\Config();
return $config
    ->setRules([
        '@PSR12' => true,
        '@TYPO3' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in(__DIR__ . '/Classes')
            ->in(__DIR__ . '/Tests')
    );
```

### Rector (Code Migration)

Modernize code automatically:

```bash
# Check for available migrations
ddev composer lint:rector

# Apply migrations
ddev composer fix:rector
```

## Continuous Integration

### GitHub Actions

The project includes CI/CD workflows:

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  tests:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['8.1', '8.2', '8.3']
        typo3: ['12.4', '13.0']

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}

      - name: Install dependencies
        run: composer install

      - name: Run tests
        run: composer test

      - name: Upload coverage
        uses: codecov/codecov-action@v3
        with:
          files: .Build/coverage/clover.xml
```

### Local CI Simulation

Run the same checks locally:

```bash
# All quality checks
ddev composer ci

# Individual checks
ddev composer lint
ddev composer sca:php
ddev composer test
```

## Test Data Management

### Test Fixtures

Create reusable test data:

```php
class TokenTestDataProvider
{
    public static function validTokenData(): array
    {
        return [
            'uid' => 1,
            'name' => 'Test Token',
            'identifier' => 'test-identifier',
            'hash' => '$2y$10$abcdefghijklmnopqrstuvw',
            'valid_until' => (new \DateTimeImmutable('+1 year'))->format('U'),
        ];
    }

    public static function expiredTokenData(): array
    {
        $data = self::validTokenData();
        $data['valid_until'] = (new \DateTimeImmutable('-1 day'))->format('U');
        return $data;
    }
}
```

### Database Fixtures

For functional tests with database data:

```php
class ApiKeyAuthenticatorTest extends FunctionalTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Import test data
        $this->importDataSet(__DIR__ . '/Fixtures/tokens.xml');
    }
}
```

Test data file (`Tests/Functional/Fixtures/tokens.xml`):
```xml
<?xml version="1.0" encoding="utf-8"?>
<dataset>
    <tx_apitoken_domain_model_token>
        <uid>1</uid>
        <name>Test Token</name>
        <identifier>test123</identifier>
        <hash>$2y$10$example</hash>
        <valid_until>2030-12-31</valid_until>
    </tx_apitoken_domain_model_token>
</dataset>
```

## Debugging Tests

### Test Debugging

```bash
# Run specific test with verbose output
ddev exec .Build/bin/phpunit -v Tests/Unit/Service/TokenServiceTest.php

# Debug with stack trace
ddev exec .Build/bin/phpunit --debug Tests/Unit/Service/TokenServiceTest.php

# Stop on first failure
ddev exec .Build/bin/phpunit --stop-on-failure Tests/
```

### Xdebug Integration

Enable Xdebug for step-by-step debugging:

```bash
# Enable Xdebug in DDEV
ddev xdebug on

# Run tests with Xdebug
ddev exec .Build/bin/phpunit Tests/Unit/Service/TokenServiceTest.php
```

## Performance Testing

### Benchmarking

Create performance tests for critical operations:

```php
#[Test]
public function tokenGenerationPerformance(): void
{
    $startTime = microtime(true);

    for ($i = 0; $i < 1000; $i++) {
        $this->subject->generateSecret();
    }

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // Assert performance requirements
    self::assertLessThan(1.0, $duration, 'Token generation too slow');
}
```

### Memory Usage Testing

Monitor memory usage during tests:

```php
#[Test]
public function memoryUsageRemainsConstant(): void
{
    $initialMemory = memory_get_usage();

    for ($i = 0; $i < 10000; $i++) {
        $token = $this->subject->generateSecret();
        unset($token);
    }

    $finalMemory = memory_get_usage();
    $memoryIncrease = $finalMemory - $initialMemory;

    // Allow for some memory increase but catch leaks
    self::assertLessThan(1024 * 1024, $memoryIncrease, 'Memory leak detected');
}
```

## Test Documentation

### Documenting Test Cases

Use descriptive test names and comments:

```php
/**
 * @test
 * Verifies that token authentication fails when:
 * - Valid identifier is provided
 * - Invalid secret is provided
 * - Token has not expired
 *
 * Expected behavior: Authentication should fail
 * and return false from isAuthenticated()
 */
#[Test]
public function authenticationFailsWithValidIdentifierButInvalidSecret(): void
{
    // Arrange
    $validIdentifier = 'valid-identifier';
    $invalidSecret = 'wrong-secret';
    $validTokenData = $this->getValidTokenData();

    // Act & Assert
    // ... test implementation
}
```

### Test Coverage Goals

Maintain high test coverage:

- **Unit Tests**: > 90% line coverage
- **Functional Tests**: Cover all integration points
- **Edge Cases**: Test error conditions and edge cases
- **Performance**: Include performance regression tests

## Best Practices

1. **Test Isolation**: Each test should be independent
2. **Descriptive Names**: Use clear, descriptive test method names
3. **AAA Pattern**: Arrange, Act, Assert structure
4. **Mock External Dependencies**: Don't test external systems
5. **Test Edge Cases**: Include boundary conditions and error cases
6. **Keep Tests Simple**: One concept per test
7. **Use Data Providers**: For testing multiple similar scenarios
8. **Clean Up**: Proper tearDown for resource cleanup

## Troubleshooting

### Common Test Issues

**Database connection errors**:
```bash
# Check database configuration
ddev describe

# Restart database
ddev restart
```

**Test isolation issues**:
```bash
# Clear test cache
ddev exec .Build/bin/typo3 cache:flush --env=Testing
```

**Memory issues**:
```bash
# Increase memory limit
ddev config --php-version=8.2 --web-env-add=PHP_MEMORY_LIMIT=1G
```

## Next Steps

- [Development Guide](Development.md) - Set up development environment
- [API Reference](ApiReference.md) - Detailed API documentation
- [Troubleshooting](Troubleshooting.md) - Common issues and solutions
