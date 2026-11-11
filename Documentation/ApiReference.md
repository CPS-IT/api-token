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

Returns a standardized 403 error response.

**Returns**: `JsonResponse` with status 403 and body
`{"message": "Access to requested resource is forbidden.", "code": 403}`

---

### TokenService

Service class for token generation and validation.

**Namespace**: `CPSIT\ApiToken\Service\TokenService`

#### Constructor

```php
public function __construct(
    ?RandomInterface $random = null,
    ?PasswordHashInterface $hashInstance = null
)
```

`$hashInstance` is only used to verify legacy hashes (see `check()`). When
omitted, the matching TYPO3 password hash implementation is detected from
the stored hash.

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

Hashes a secret for storage as `sha256:<hex digest>`.

Secrets are 122 bit random values, not user chosen passwords, so a slow
password hash (argon2, bcrypt) adds no protection, only verification cost.

**Parameters**:
- `$secret`: Plain text secret to hash

**Returns**: Hash string

**Example**:
```php
$hash = $tokenService->hash('my-secret-token');
// Returns: "sha256:1b0f…"
```

##### `check(string $secret, string $saltedHash): bool`

Verifies a secret against its stored hash, in constant time. Accepts the
current `sha256:` format and TYPO3 password hashes of the previous format.

**Parameters**:
- `$secret`: Plain text secret
- `$saltedHash`: Stored hash to check against

**Returns**: `true` if secret matches hash, `false` otherwise (also for empty input)

**Example**:
```php
$isValid = $tokenService->check('my-secret-token', $storedHash);
// Returns: true or false
```

##### `needsRehash(string $hash): bool`

From `HashUpgradeAwareInterface`. Returns `true` for legacy hash formats.
`ApiKeyAuthentication` uses it to replace a legacy hash with the current
format after the first successful authentication.

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

Authentication succeeds only if the token exists, is neither deleted nor
disabled (`hidden`), `valid_until` lies in the future (`0` counts as
expired) and the secret matches. Every call starts from "not
authenticated"; an earlier result is never kept. After a successful check
a legacy hash is upgraded (see `TokenService::needsRehash()`).

**Example**:
```php
$auth = new ApiKeyAuthentication($tokenService, $tokenRepository);
$auth->withIdentifier('token-identifier');

$authenticatedAuth = $auth->fromHeader('secret-token');

if ($authenticatedAuth->isAuthenticated()) {
    // Authentication successful
}
```

##### `forNewRequest(): self`

Returns a copy without per-request state (identifier, token, result,
method). Use it when you hold a shared instance (e.g. injected via DI) and
authenticate more than one request with it.

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
- `validUntil`: Expiration timestamp (`?DateTime`)

---

### TokenRepository

Repository for token database operations.

**Namespace**: `CPSIT\ApiToken\Domain\Repository\TokenRepository`

#### Methods

##### `findOneRecordByIdentifier(string $identifier): array`

Finds a token record by its identifier. Deleted and disabled (`hidden = 1`)
tokens are not returned. Expiry is **not** filtered here - check
`valid_until` yourself (or use `ApiKeyAuthentication`), and treat `0` as
expired.

**Parameters**:
- `$identifier`: Token identifier

**Returns**: Token data array or empty array if not found

**Example**:
```php
$tokenData = $repository->findOneRecordByIdentifier('abc123');
// Returns: ['uid' => 1, 'name' => 'Token', 'identifier' => 'abc123', ...]
```

##### `updateHash(int $uid, string $hash): void`

Replaces the stored hash of a token (used to upgrade legacy hashes).

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

Evaluates the `x-api-identifier` / `application-authorization` headers and
stores the result in the `RestApiAuthenticated` context aspect. It never
rejects a request itself - your endpoint decides via
`ApiTokenAuthenticator::isNotAuthenticated()`.

**Parameters**:
- `$request`: PSR-7 server request
- `$handler`: Next request handler in the stack

**Returns**: PSR-7 response

The extension already registers this middleware for the frontend
(`cpsit/api-token/api-key-authenticator`, after
`typo3/cms-frontend/page-argument-validator`). Do not register it a second
time. It runs on every frontend request with a resolved page route; requests
without both headers, or with an HTTP method outside
`RestApiInterface::VALID_METHODS`, are simply not authenticated.

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

- `--name`: Token name (asked interactively if omitted)
- `--description, -d`: Token description (optional)
- `--expires, -e`: Expiration as interval or date (default: `+1 year`)
- `--json, -j`: Output as JSON
- `--no-interaction, -n`: Symfony's standard non-interactive flag

See [CLI Commands](CliCommands.md) for details.

---

## Controllers

### TokenController

Backend controller for token management.

**Namespace**: `CPSIT\ApiToken\Controller\Backend\TokenController`

#### Actions

##### `listAction(): ResponseInterface`

Lists all tokens in the backend module.

##### `newAction(): ResponseInterface`

Shows the token creation form (name and description).

##### `createAction(array $newToken): ResponseInterface`

Generates identifier, secret and hash server-side, stores the token and
shows identifier and secret once. Only `name` and `description` are taken
from the form.

##### `deleteAction(int $tokenUid): ResponseInterface`

Deletes a token via DataHandler (POST only). See
[Backend Module](BackendModule.md).

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

`ApiTokenAuthenticator::returnErrorResponse()` answers every failed
authentication with the same response, so clients cannot tell which check
failed:

#### 403 Forbidden

**Causes**:
- Missing authentication headers
- Unknown identifier or wrong secret
- Expired token (`valid_until` in the past or `0`)
- Disabled (`hidden`) or deleted token

**Response Format**:
```json
{
    "message": "Access to requested resource is forbidden.",
    "code": 403
}
```

If you need different status codes, build your own response instead of
calling `returnErrorResponse()`.

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
| `identifier` | tinytext | Public identifier (not indexed, not unique) |
| `hash` | varchar(255) | `sha256:<hex>`, or a legacy TYPO3 password hash |
| `valid_until` | int | Expiration timestamp; `0` = expired |
| `tstamp` | int | Last modification timestamp |
| `crdate` | int | Creation timestamp |
| `deleted` | tinyint | Soft delete flag (deleted tokens never authenticate) |
| `hidden` | tinyint | Disabled flag (disabled tokens never authenticate) |

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

**Thrown when**: Invalid HTTP method is passed to `ApiKeyAuthentication::withMethod()`.
The middleware checks the method beforehand and does not throw it.

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

Structure returned by `TokenRepository::findOneRecordByIdentifier()`
(never a deleted or disabled token):

```php
[
    'uid' => int,
    'pid' => int,
    'name' => string,
    'description' => string,
    'identifier' => string,
    'hash' => string,
    'valid_until' => int, // Unix timestamp, 0 = expired
    'tstamp' => int,
    'crdate' => int,
    'deleted' => int, // always 0
    'hidden' => int,  // always 0
    'sorting' => int,
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

See the [Changelog](Changelog.md).

---

## Performance Considerations

- Token validation queries are not cached.
- Only requests carrying both authentication headers hit the database.
- `identifier` is not indexed (`TINYTEXT`); token tables are expected to be small.
- Verifying a current (`sha256:`) hash is cheap. Hashes in the previous format
  (argon2/bcrypt) are slow to verify and are replaced on first successful use.

---

## Security Notes

### Token Security

- Secrets are never stored in plain text, only as SHA-256 digest
- Cryptographically secure random generation (122 bit secrets)
- Disabling a token (`hidden`) revokes it immediately; so does deleting it
- Tokens are not scoped: every valid token passes every endpoint that uses
  `ApiTokenAuthenticator`. Implement scoping in your endpoint if you need it.

### Best Practices

1. **Token Rotation**: Regularly rotate long-lived tokens
2. **Short Lifetimes**: Use `--expires` for tokens used by automation
3. **Revocation**: Disable or delete a token as soon as it may have leaked
4. **Transport Security**: Always use HTTPS in production
5. **Storage Security**: Never commit tokens to version control

---

This API reference covers all public interfaces and methods. For implementation examples, see the [Usage Guide](Usage.md) and [Development Guide](Development.md).
