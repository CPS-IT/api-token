# API Reference

Complete reference for all classes, interfaces, and methods in the API Token extension.

## Core Classes

### ApiTokenAuthenticator

Primary class for token authentication validation.

**Namespace**: `CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator`

#### Static Methods

##### `isNotAuthenticated(ServerRequestInterface $request): bool`

Checks if the request is NOT authenticated.

**Parameters**:
- `$request`: PSR-7 server request object

**Returns**: `true` if authentication fails, `false` if authenticated

**Example**:
```php
if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
    return ApiTokenAuthenticator::returnErrorResponse();
}
```

##### `returnErrorResponse(): ResponseInterface`

Returns a standardized 401 error response.

**Returns**: PSR-7 response with 401 status code

---

### TokenService

Service class for token generation and validation.

**Namespace**: `CPSIT\ApiToken\Service\TokenService`

#### Constructor

```php
public function __construct(
    RandomInterface $random = null,
    PasswordHashInterface $hashInstance = null
)
```

#### Methods

##### `generateSecret(): string`

Generates a cryptographically secure UUID v4 token.

**Returns**: UUID v4 string (e.g., `550e8400-e29b-41d4-a716-446655440000`)

**Example**:
```php
$tokenService = new TokenService();
$secret = $tokenService->generateSecret();
// Returns: "550e8400-e29b-41d4-a716-446655440000"
```

##### `generateIdentifier(int $length = 13): string`

Generates a random hexadecimal identifier.

**Parameters**:
- `$length`: Length of the identifier (default: 13)

**Returns**: Hexadecimal string

**Example**:
```php
$identifier = $tokenService->generateIdentifier();
// Returns: "4a6f8b2e3d"

$longIdentifier = $tokenService->generateIdentifier(20);
// Returns: "4a6f8b2e3d9c1f7a8b5e"
```

##### `hash(string $secret): string`

Hashes a secret using TYPO3's password hashing.

**Parameters**:
- `$secret`: Plain text secret to hash

**Returns**: Hashed password string

**Example**:
```php
$hash = $tokenService->hash('my-secret-token');
// Returns: "$2y$10$abcdefghijklmnopqrstuvwxyz..."
```

##### `check(string $secret, string $saltedHash): bool`

Verifies a secret against its hash.

**Parameters**:
- `$secret`: Plain text secret
- `$saltedHash`: Hashed password to check against

**Returns**: `true` if secret matches hash, `false` otherwise

**Example**:
```php
$isValid = $tokenService->check('my-secret-token', $hashedPassword);
// Returns: true or false
```

---

### ApiKeyAuthentication

Low-level authentication class for manual token validation.

**Namespace**: `CPSIT\ApiToken\Authentication\ApiKeyAuthentication`

#### Constructor

```php
public function __construct(
    TokenServiceInterface $tokenService,
    TokenRepositoryInterface $tokenRepository
)
```

#### Methods

##### `withIdentifier(string $identifier): self`

Sets the token identifier for authentication.

**Parameters**:
- `$identifier`: Token identifier

**Returns**: Self for method chaining

##### `withMethod(string $method): self`

Sets the HTTP method for authentication.

**Parameters**:
- `$method`: HTTP method (GET, POST, PUT, DELETE, etc.)

**Returns**: Self for method chaining

**Throws**: `InvalidHttpMethodException` for invalid methods

##### `fromHeader(string $secret, string $headerName = null): AuthenticationInterface`

Authenticates using the provided secret.

**Parameters**:
- `$secret`: Secret token for authentication
- `$headerName`: Optional header name (legacy parameter)

**Returns**: Authentication instance

**Example**:
```php
$auth = new ApiKeyAuthentication($tokenService, $tokenRepository);
$auth->withIdentifier('token-identifier');

$authenticatedAuth = $auth->fromHeader('secret-token');

if ($authenticatedAuth->isAuthenticated()) {
    // Authentication successful
}
```

##### `isAuthenticated(): bool`

Checks if the current instance is authenticated.

**Returns**: `true` if authenticated, `false` otherwise

##### `validUntil(): DateTimeImmutable`

Gets the token expiration time.

**Returns**: Expiration timestamp

##### `getMethod(): string`

Gets the current HTTP method.

**Returns**: HTTP method string

##### `validateHeaderName(string $name): bool`

Validates if a header name is allowed.

**Parameters**:
- `$name`: Header name to validate

**Returns**: `true` if valid, `false` otherwise

---

### Token (Domain Model)

Domain model representing an API token.

**Namespace**: `CPSIT\ApiToken\Domain\Model\Token`

#### Properties

- `uid`: Unique identifier (int)
- `name`: Human-readable token name (string)
- `description`: Token description (string)
- `identifier`: Public token identifier (string)
- `hash`: Hashed secret (string)
- `validUntil`: Expiration timestamp (DateTimeImmutable)

#### Methods

##### `isExpired(): bool`

Checks if the token has expired.

**Returns**: `true` if expired, `false` if still valid

---

### TokenRepository

Repository for token database operations.

**Namespace**: `CPSIT\ApiToken\Domain\Repository\TokenRepository`

#### Methods

##### `findOneRecordByIdentifier(string $identifier): array`

Finds a token record by its identifier.

**Parameters**:
- `$identifier`: Token identifier

**Returns**: Token data array or empty array if not found

**Example**:
```php
$tokenData = $repository->findOneRecordByIdentifier('abc123');
// Returns: ['uid' => 1, 'name' => 'Token', 'identifier' => 'abc123', ...]
```

---

## Interfaces

### TokenServiceInterface

Interface for token service implementations.

**Namespace**: `CPSIT\ApiToken\Service\TokenServiceInterface`

```php
interface TokenServiceInterface
{
    public function generateSecret(): string;
    public function generateIdentifier(int $length = 13): string;
    public function hash(string $secret): string;
    public function check(string $secret, string $saltedHash): bool;
}
```

### AuthenticationInterface

Interface for authentication implementations.

**Namespace**: `CPSIT\ApiToken\Authentication\AuthenticationInterface`

```php
interface AuthenticationInterface
{
    public function isAuthenticated(): bool;
    public function validUntil(): DateTimeImmutable;
}
```

### RandomInterface

Interface for random number generation.

**Namespace**: `CPSIT\ApiToken\Crypto\RandomInterface`

```php
interface RandomInterface
{
    public function generateRandomBytes(int $length): string;
    public function generateRandomHexString(int $length): string;
}
```

---

## Configuration Classes

### Extension

Extension configuration constants.

**Namespace**: `CPSIT\ApiToken\Configuration\Extension`

#### Constants

- `KEY`: Extension key (`'api_token'`)
- `NAME`: Extension name (`'ApiToken'`)
- `VENDOR_NAME`: Vendor name (`'CPSIT'`)
- `EXTENSION_KEY`: Full extension key (`'api_token'`)
- `TOKEN_SVG`: Token table name (`'tx_apitoken_domain_model_token'`)

### RestApiInterface

API configuration constants.

**Namespace**: `CPSIT\ApiToken\Configuration\RestApiInterface`

#### Constants

- `HEADER_NAME_IDENTIFIER`: Identifier header name (`'x-api-identifier'`)
- `HEADER_NAME_AUTHORIZATION`: Authorization header name (`'application-authorization'`)
- `METHOD_GET`: GET method constant
- `METHOD_POST`: POST method constant
- `METHOD_PUT`: PUT method constant
- `METHOD_DELETE`: DELETE method constant
- `VALID_METHODS`: Array of valid HTTP methods

---

## Middleware

### ApiKeyAuthenticator

PSR-15 middleware for automatic authentication.

**Namespace**: `CPSIT\ApiToken\Middleware\ApiKeyAuthenticator`

#### Methods

##### `process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface`

Processes the request through the authentication middleware.

**Parameters**:
- `$request`: PSR-7 server request
- `$handler`: Next request handler in the stack

**Returns**: PSR-7 response

**Usage in Configuration**:
```php
// Configuration/RequestMiddlewares.php
return [
    'frontend' => [
        'api-authentication' => [
            'target' => \CPSIT\ApiToken\Middleware\ApiKeyAuthenticator::class,
            'before' => ['your-api-handler']
        ]
    ]
];
```

---

## CLI Commands

### GenerateTokenCommand

Command for generating API tokens via CLI.

**Namespace**: `CPSIT\ApiToken\Command\GenerateTokenCommand`

#### Usage

```bash
./vendor/bin/typo3 apitoken:generate [options]
```

#### Options

- `--name, -n`: Token name (required)
- `--description, -d`: Token description (optional)
- `--expires, -e`: Expiration date (default: +1 year)
- `--no-interaction, -N`: Non-interactive mode
- `--output-format, -f`: Output format (text, json, yaml)

---

## Controllers

### TokenController

Backend controller for token management.

**Namespace**: `CPSIT\ApiToken\Controller\Backend\TokenController`

#### Actions

##### `listAction(): ResponseInterface`

Lists all tokens in the backend module.

##### `newAction(): ResponseInterface`

Shows the token creation form.

##### `createAction(): ResponseInterface`

Creates a new token.

##### `deleteAction(): ResponseInterface`

Deletes an existing token.

---

## HTTP Headers

### Required Headers

All authenticated API requests must include these headers:

#### `x-api-identifier`

**Description**: Public token identifier
**Type**: String
**Example**: `4a6f8b2e3d`

#### `application-authorization`

**Description**: Secret token for authentication
**Type**: String
**Example**: `550e8400-e29b-41d4-a716-446655440000`

### Optional Headers

#### `Content-Type`

**Description**: Request content type
**Recommended**: `application/json`

---

## Error Responses

### Authentication Errors

#### 401 Unauthorized

**Causes**:
- Missing authentication headers
- Invalid identifier or secret
- Expired token

**Response Format**:
```json
{
    "error": "Authentication required",
    "message": "Missing or invalid authentication credentials",
    "code": 401
}
```

#### 403 Forbidden

**Causes**:
- Valid token but insufficient permissions
- IP restrictions (if implemented)

**Response Format**:
```json
{
    "error": "Forbidden",
    "message": "Access denied",
    "code": 403
}
```

---

## Database Schema

### tx_apitoken_domain_model_token

Token storage table structure.

#### Columns

| Column | Type | Description |
|--------|------|-------------|
| `uid` | int | Primary key |
| `pid` | int | Page ID (TYPO3) |
| `name` | varchar(255) | Human-readable name |
| `description` | text | Token description |
| `identifier` | varchar(255) | Public identifier |
| `hash` | varchar(255) | Hashed secret |
| `valid_until` | int | Expiration timestamp |
| `tstamp` | int | Last modification timestamp |
| `crdate` | int | Creation timestamp |
| `cruser_id` | int | Creator user ID |
| `deleted` | tinyint | Soft delete flag |
| `hidden` | tinyint | Hidden flag |

---

## Events and Hooks

### Token Generation Events

Custom events fired during token operations (planned for future versions):

- `TokenCreatedEvent`: Fired when a token is created
- `TokenAuthenticatedEvent`: Fired on successful authentication
- `TokenExpiredEvent`: Fired when an expired token is used

---

## Exceptions

### InvalidHttpMethodException

**Namespace**: `CPSIT\ApiToken\Exception\InvalidHttpMethodException`

**Code**: `1585497878`

**Thrown when**: Invalid HTTP method is provided to authentication

**Example**:
```php
try {
    $auth->withMethod('INVALID');
} catch (InvalidHttpMethodException $e) {
    // Handle invalid method
}
```

---

## Constants Reference

### HTTP Methods

```php
RestApiInterface::METHOD_GET     // 'GET'
RestApiInterface::METHOD_POST    // 'POST'
RestApiInterface::METHOD_PUT     // 'PUT'
RestApiInterface::METHOD_DELETE  // 'DELETE'
RestApiInterface::VALID_METHODS  // ['GET', 'POST', 'PUT', 'DELETE']
```

### Headers

```php
RestApiInterface::HEADER_NAME_IDENTIFIER     // 'x-api-identifier'
RestApiInterface::HEADER_NAME_AUTHORIZATION  // 'application-authorization'
```

### Authentication Classes

```php
ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION // 'application-authorization'
```

---

## Type Definitions

### Token Data Array

Structure returned by `TokenRepository::findOneRecordByIdentifier()`:

```php
[
    'uid' => int,
    'name' => string,
    'description' => string|null,
    'identifier' => string,
    'hash' => string,
    'valid_until' => int, // Unix timestamp
    'tstamp' => int,
    'crdate' => int,
    'cruser_id' => int,
    'deleted' => int,
    'hidden' => int
]
```

---

## Version Compatibility

### TYPO3 Version Support

| Extension Version | TYPO3 Version | PHP Version |
|------------------|---------------|-------------|
| 1.0.x | 12.4+ | 8.1+ |
| 1.0.x | 13.0+ | 8.1+ |

### Breaking Changes

See [Migration Guide](Migration.md) for details on breaking changes between versions.

---

## Performance Considerations

### Caching

- Token validation queries are not cached by default
- Consider implementing custom caching for high-traffic scenarios
- Database indexes exist on `identifier` column for fast lookups

### Database Optimization

```sql
-- Recommended indexes (automatically created)
CREATE INDEX identifier ON tx_apitoken_domain_model_token (identifier);
CREATE INDEX valid_until ON tx_apitoken_domain_model_token (valid_until);
```

### Memory Usage

- Token generation uses minimal memory
- No persistent objects stored in memory
- Suitable for high-concurrency scenarios

---

## Security Notes

### Token Security

- Secrets are never stored in plain text
- Uses TYPO3's `PasswordHashFactory` for secure hashing
- Cryptographically secure random generation

### Best Practices

1. **Token Rotation**: Regularly rotate long-lived tokens
2. **Scope Limitation**: Use different tokens for different purposes
3. **Monitoring**: Log authentication attempts
4. **Transport Security**: Always use HTTPS in production
5. **Storage Security**: Never commit tokens to version control

---

This API reference covers all public interfaces and methods. For implementation examples, see the [Usage Guide](Usage.md) and [Development Guide](Development.md).
