# API Token Extension for TYPO3

[![TYPO3 12](https://img.shields.io/badge/TYPO3-12.4-orange.svg)](https://get.typo3.org/version/12)
[![TYPO3 13](https://img.shields.io/badge/TYPO3-13.0-orange.svg)](https://get.typo3.org/version/13)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1+-blue.svg)](https://www.php.net/)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2+-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)

Secure API authentication for TYPO3 CMS applications using token-based authentication.

## Features

- 🔐 **Secure Token Generation** - Cryptographically secure random tokens
- 🛠 **Easy Integration** - Simple PHP API for authentication checks
- 📊 **Backend Management** - TYPO3 backend module for token administration
- ⚡ **CLI Support** - Command-line interface for automation
- 🧪 **Modern Testing** - Comprehensive test suite with PHPUnit 11
- 🚀 **TYPO3 v12/v13 Compatible** - Full support for latest TYPO3 versions

## Quick Start

### 1. Installation

```bash
composer require cpsit/api-token
```

### 2. Generate a Token

```bash
./vendor/bin/typo3 apitoken:generate
```

### 3. Protect Your API

```php
use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;

if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
    return ApiTokenAuthenticator::returnErrorResponse();
}

// Your protected API logic here
```

### 4. Make API Requests

```bash
curl -X POST "https://your-site.com/api/endpoint" \
     -H "x-api-identifier: your-identifier" \
     -H "application-authorization: your-secret" \
     -H "Content-Type: application/json"
```

## Documentation

📚 **[Complete Documentation](Documentation/Index.md)**

| Topic | Description |
|-------|-------------|
| [Introduction](Documentation/Introduction.md) | Overview and key features |
| [Installation](Documentation/Installation.md) | Setup and configuration |
| [Usage Guide](Documentation/Usage.md) | How to use the extension |
| [CLI Commands](Documentation/CliCommands.md) | Command-line interface |
| [Backend Module](Documentation/BackendModule.md) | Admin interface guide |
| [API Reference](Documentation/ApiReference.md) | Complete API documentation |
| [Development](Documentation/Development.md) | Development environment setup |
| [Testing](Documentation/Testing.md) | Testing guide and best practices |
| [Migration Guide](Documentation/Migration.md) | Upgrade and migration notes |
| [Troubleshooting](Documentation/Troubleshooting.md) | Common issues and solutions |

## Requirements

- **TYPO3**: 12.4 LTS or 13.0+
- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0+, MariaDB 10.5+, or PostgreSQL 12+

## Example Usage

### Protecting an API Endpoint

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
    public function getData(ServerRequestInterface $request): ResponseInterface
    {
        // Check authentication
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            return ApiTokenAuthenticator::returnErrorResponse();
        }

        // Return protected data
        return new JsonResponse([
            'status' => 'success',
            'data' => ['message' => 'Authenticated access granted!']
        ]);
    }
}
```

### Frontend Request Example

```javascript
// JavaScript example
fetch('/api/data', {
    method: 'GET',
    headers: {
        'x-api-identifier': 'your-identifier-here',
        'application-authorization': 'your-secret-here',
        'Content-Type': 'application/json'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

## Development

### Quick Development Setup

```bash
# Clone repository
git clone https://github.com/CPS-IT/api-token.git
cd api-token

# Start DDEV environment
ddev start

# Install dependencies
ddev composer install

# Run tests
ddev composer test
```

### Quality Assurance

```bash
# Code style and quality checks
ddev composer lint
ddev composer sca:php

# Fix code style issues
ddev composer fix

# Run test suite
ddev composer test:unit
ddev composer test:functional
```

## Architecture

The extension follows modern TYPO3 development patterns:

- **Domain-Driven Design** with clear separation of concerns
- **Dependency Injection** using TYPO3's DI container
- **PSR Standards** compliance (PSR-7, PSR-15, PSR-12)
- **Modern PHP** features (type declarations, readonly classes)
- **Comprehensive Testing** with PHPUnit 11 and TYPO3 TestingFramework

## Security

- Cryptographically secure token generation using `random_bytes()`
- Password hashing with TYPO3's `PasswordHashFactory`
- Configurable token expiration (default: 1 year)
- No secrets stored in plain text
- Rate limiting and audit logging (planned features)

## Contributing

We welcome contributions! Please see our [Development Guide](Documentation/Development.md) for details on:

- Setting up the development environment
- Code style and quality requirements
- Testing requirements
- Pull request process

## Support

- 📖 [Documentation](Documentation/Index.md)
- 🐛 [Issue Tracker](https://github.com/CPS-IT/api-token/issues)
- 💬 [Discussions](https://github.com/CPS-IT/api-token/discussions)

## License

This extension is licensed under the GNU General Public License v2.0 or later.

**Copyright (c) 2021-2024 CPS-IT GmbH**

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

## Changelog

See [CHANGELOG.md](Documentation/Changelog.md) for a detailed history of changes and releases.

---

Made with ❤️ by the [CPS-IT](https://www.cps-it.de/) team for the TYPO3 community.
