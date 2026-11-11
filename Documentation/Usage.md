# Usage Guide

This guide covers how to use the API Token extension in your TYPO3 applications.

## Quick Start Example

Here's a complete example of how to protect an API endpoint:

```php
<?php
declare(strict_types=1);

namespace MyVendor\MyExtension\Controller;

use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

class ApiController
{
    public function protectedEndpoint(ServerRequestInterface $request): ResponseInterface
    {
        // Check authentication
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            return ApiTokenAuthenticator::returnErrorResponse();
        }

        // Your protected logic here
        return new JsonResponse([
            'status' => 'success',
            'data' => ['message' => 'Access granted!']
        ]);
    }
}
```

## Token Management

### Creating Tokens

#### Via CLI (Recommended for automation)
```bash
# Interactive mode
./vendor/bin/typo3 apitoken:generate

# Non-interactive mode
./vendor/bin/typo3 apitoken:generate \
    --name="Mobile App Token" \
    --description="Authentication for mobile application" \
    --expires="+6 months"
```

#### Via Backend Module
1. Navigate to **System** > **API Token** (admins only)
2. Click **Generate new token**
3. Fill in the form:
   - **Name**: Descriptive name for identification
   - **Description**: Detailed description of token usage
4. Click **Submit**
5. **Important**: Copy identifier and secret immediately - the secret won't be shown again!

Tokens created in the backend are valid for one year; edit the record to
change `valid until`. See [Backend Module](BackendModule.md).

### Revoking Tokens

A token stops authenticating immediately when it is

- **disabled** (the "Visible" toggle in the record, stored as `hidden = 1`),
- **deleted** (delete button in the backend module), or
- **expired** (`valid until` in the past).

If a secret may have leaked, revoke the token first, then hand out a new one.

### Token Information

When a token is created, you receive:

- **Identifier**: Public identifier used in headers (e.g., `4a6f8b2e3d`)
- **Secret**: Private secret for authentication (e.g., `7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82`)
- **Expiration**: When the token expires

## Authentication Methods

### Method 1: Using the Authenticator Class (Recommended)

```php
use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;

// Simple authentication check (403 response on failure)
if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
    return ApiTokenAuthenticator::returnErrorResponse();
}

// Check with custom error handling
if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
    return new JsonResponse([
        'error' => 'Authentication required',
        'code' => 401
    ], 401);
}
```

`isNotAuthenticated()` reads the result the extension's middleware stored
for the current request. It only works for requests that went through the
TYPO3 frontend with a resolved page (e.g. a plugin or page type), not in
middlewares that run before page resolution.

**Backend users**: by default a logged-in backend user does **not** pass
this check without a token. Earlier versions always accepted backend users;
to keep that behaviour, enable `allowBackendUserAuthentication` - see
[Configuration](Configuration.md).

### Method 2: The Middleware

The extension registers `ApiKeyAuthenticator` as a frontend middleware
automatically - do not register it again. It runs on every frontend
request with a resolved page, evaluates the two headers and stores the
result in the `RestApiAuthenticated` context aspect. It never rejects a
request on its own; your endpoint decides (Method 1).

### Method 3: Manual Authentication

For advanced use cases, you can manually handle authentication:

```php
use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;

// Get headers
$identifier = $request->getHeaderLine(RestApiInterface::HEADER_NAME_IDENTIFIER);
$authorization = $request->getHeaderLine('application-authorization');

// Create authentication instance (or call ->forNewRequest() on an injected one)
$auth = new ApiKeyAuthentication($tokenService, $tokenRepository);
$auth->withIdentifier($identifier);

// Authenticate: checks secret, expiry, disabled and deleted state
$authenticatedAuth = $auth->fromHeader($authorization);

if (!$authenticatedAuth->isAuthenticated()) {
    // Handle authentication failure
    return new JsonResponse(['error' => 'Invalid credentials'], 401);
}
```

This works in your own middleware too, e.g. before page resolution, where
Method 1 is not available.

## HTTP Headers

API requests must include these headers:

### Required Headers
```http
x-api-identifier: 4a6f8b2e3d
application-authorization: 7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82
Content-Type: application/json
```

### Example cURL Request
```bash
curl -X POST "https://your-site.com/api/protected-endpoint" \
     -H "x-api-identifier: 4a6f8b2e3d" \
     -H "application-authorization: 7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82" \
     -H "Content-Type: application/json" \
     -d '{"data": "your request data"}'
```

### Example JavaScript (Fetch API)
```javascript
fetch('https://your-site.com/api/protected-endpoint', {
    method: 'POST',
    headers: {
        'x-api-identifier': '4a6f8b2e3d',
        'application-authorization': '7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82',
        'Content-Type': 'application/json'
    },
    body: JSON.stringify({
        data: 'your request data'
    })
});
```

## Response Formats

### Successful Authentication
When authentication succeeds, your endpoint receives the normal request and can return any response.

### Authentication Failures

`ApiTokenAuthenticator::returnErrorResponse()` returns the same response for
every failure (missing headers, unknown identifier, wrong secret, expired,
disabled or deleted token), so a client cannot probe which check failed:

```http
HTTP/1.1 403 Access to requested resource is forbidden.
Content-Type: application/json

{
    "message": "Access to requested resource is forbidden.",
    "code": 403
}
```

## Advanced Usage Patterns

### Custom Token Validation
```php
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Service\TokenService;

class CustomAuthService
{
    public function __construct(
        private TokenRepository $tokenRepository,
        private TokenService $tokenService
    ) {}

    public function validateTokenWithCustomRules(string $identifier, string $secret): bool
    {
        // Get token from database (never returns deleted or disabled tokens)
        $tokenData = $this->tokenRepository->findOneRecordByIdentifier($identifier);

        if (empty($tokenData)) {
            return false;
        }

        // Expiry is NOT filtered by the repository - always check it.
        // valid_until = 0 means expired.
        if ((int)$tokenData['valid_until'] <= time()) {
            return false;
        }

        // Check secret
        if (!$this->tokenService->check($secret, (string)$tokenData['hash'])) {
            return false;
        }

        // Replace a slow hash of the previous format (argon2/bcrypt) once
        // the secret is known to be right - otherwise every request pays for it.
        // ApiKeyAuthentication does this for you.
        if ($this->tokenService->needsRehash((string)$tokenData['hash'])) {
            $this->tokenRepository->updateHash((int)$tokenData['uid'], $this->tokenService->hash($secret));
        }

        // Additional custom checks
        // - Rate limiting
        // - IP restrictions
        // - Time-based access rules

        return true;
    }
}
```

### Integration with TYPO3 Security Framework
```php
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\UserAspect;

class AuthenticatedApiController
{
    public function __construct(private Context $context) {}

    public function protectedAction(ServerRequestInterface $request): ResponseInterface
    {
        // Check API token first
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            return ApiTokenAuthenticator::returnErrorResponse();
        }

        // Additional TYPO3 user context checks
        $userAspect = $this->context->getAspect('frontend.user');
        if (!$userAspect->isLoggedIn()) {
            // Handle additional user requirements
        }

        // Your protected logic
        return new JsonResponse(['status' => 'success']);
    }
}
```

### Token Scoping and Permissions
```php
class ScopedApiController
{
    public function adminEndpoint(ServerRequestInterface $request): ResponseInterface
    {
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            return ApiTokenAuthenticator::returnErrorResponse();
        }

        // Check additional permissions based on token metadata
        $identifier = $request->getHeaderLine('x-api-identifier');
        if (!$this->hasAdminScope($identifier)) {
            return new JsonResponse(['error' => 'Insufficient permissions'], 403);
        }

        // Admin-only logic
        return new JsonResponse(['admin_data' => 'sensitive information']);
    }

    private function hasAdminScope(string $identifier): bool
    {
        // Implement custom scope checking logic
        // This could be based on token name, description, or custom fields
        return true; // Simplified for example
    }
}
```

## Testing Your Implementation

### Unit Testing
```php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\ServerRequest;

class ApiAuthTest extends TestCase
{
    public function testValidAuthentication(): void
    {
        $request = new ServerRequest('POST', '/api/test')
            ->withHeader('x-api-identifier', 'valid-identifier')
            ->withHeader('application-authorization', 'valid-secret');

        // Test your authentication logic
        $this->assertFalse(ApiTokenAuthenticator::isNotAuthenticated($request));
    }
}
```

### Integration Testing
```bash
# Test token generation
./vendor/bin/typo3 apitoken:generate --name="Test" --description="Testing"

# Test API call
curl -X POST "http://localhost/api/test" \
     -H "x-api-identifier: your-identifier" \
     -H "application-authorization: your-secret"
```

## Best Practices

1. **Secure Token Storage**: Never log or expose tokens in client-side code
2. **Token Rotation**: Regularly rotate tokens for enhanced security
3. **Revocation**: Disable or delete a token as soon as it may have leaked
4. **No Built-in Scopes**: Every valid token passes every endpoint that uses
   `ApiTokenAuthenticator`; implement scoping yourself if endpoints need different access
5. **Monitoring**: Log authentication attempts for security monitoring
6. **Expiration**: Set appropriate expiration dates for tokens
7. **Rate Limiting**: Implement rate limiting on top of token authentication

## Next Steps

- [CLI Commands Reference](CliCommands.md)
- [Backend Module Guide](BackendModule.md)
- [API Reference](ApiReference.md)
- [Development Guide](Development.md)
