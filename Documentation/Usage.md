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
1. Navigate to **System** > **API Token Management**
2. Click **Create New Token**
3. Fill in the form:
   - **Name**: Descriptive name for identification
   - **Description**: Detailed description of token usage
   - **Valid Until**: Expiration date (default: 1 year)
4. Click **Save**
5. **Important**: Copy the secret immediately - it won't be shown again!

### Token Information

When a token is created, you receive:

- **Identifier**: Public identifier used in headers (e.g., `4a6f8b2e3d`)
- **Secret**: Private secret for authentication (e.g., `7a5c9f2b-4d8e-1a3c-9e5f-2b4d8e1a3c82`)
- **Expiration**: When the token expires

## Authentication Methods

### Method 1: Using the Authenticator Class (Recommended)

```php
use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;

// Simple authentication check
if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
    return ApiTokenAuthenticator::returnErrorResponse();
}

// Check with custom error handling
$authenticator = new ApiTokenAuthenticator();
if (!$authenticator->isAuthenticated($request)) {
    return new JsonResponse([
        'error' => 'Authentication required',
        'code' => 401
    ], 401);
}
```

### Method 2: Using the Middleware

The extension provides PSR-15 middleware for automatic authentication:

```php
// In your middleware configuration
return [
    'frontend' => [
        'my-api-auth' => [
            'target' => \CPSIT\ApiToken\Middleware\ApiKeyAuthenticator::class,
            'before' => [
                'your-api-handler'
            ]
        ]
    ]
];
```

### Method 3: Manual Authentication

For advanced use cases, you can manually handle authentication:

```php
use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;

// Get headers
$identifier = $request->getHeaderLine(RestApiInterface::HEADER_NAME_IDENTIFIER);
$authorization = $request->getHeaderLine('application-authorization');

// Create authentication instance
$auth = new ApiKeyAuthentication($tokenService, $tokenRepository);
$auth->withIdentifier($identifier);

// Authenticate
$authenticatedAuth = $auth->fromHeader($authorization);

if (!$authenticatedAuth->isAuthenticated()) {
    // Handle authentication failure
    return new JsonResponse(['error' => 'Invalid credentials'], 401);
}

// Check if token is still valid
if ($authenticatedAuth->validUntil() < new \DateTimeImmutable()) {
    return new JsonResponse(['error' => 'Token expired'], 401);
}
```

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

#### Missing Headers
```json
{
    "error": "Authentication required",
    "message": "Missing authentication headers",
    "code": 401
}
```

#### Invalid Credentials
```json
{
    "error": "Authentication failed",
    "message": "Invalid identifier or secret",
    "code": 401
}
```

#### Expired Token
```json
{
    "error": "Token expired",
    "message": "The provided token has expired",
    "code": 401
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
        // Get token from database
        $tokenData = $this->tokenRepository->findOneRecordByIdentifier($identifier);

        if (empty($tokenData)) {
            return false;
        }

        // Check secret
        if (!$this->tokenService->check($secret, $tokenData['hash'])) {
            return false;
        }

        // Custom validation rules
        $validUntil = new \DateTimeImmutable('@' . $tokenData['valid_until']);
        if ($validUntil < new \DateTimeImmutable()) {
            return false;
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
3. **Scope Limitation**: Use different tokens for different API endpoints
4. **Monitoring**: Log authentication attempts for security monitoring
5. **Expiration**: Set appropriate expiration dates for tokens
6. **Rate Limiting**: Implement rate limiting on top of token authentication

## Next Steps

- [CLI Commands Reference](CliCommands.md)
- [Backend Module Guide](BackendModule.md)
- [API Reference](ApiReference.md)
- [Development Guide](Development.md)
