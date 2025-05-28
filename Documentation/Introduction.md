# Introduction

The TYPO3 API Token extension provides a secure and flexible way to authenticate API requests in TYPO3 CMS applications.

## What is API Token?

API Token is a TYPO3 extension that enables secure API authentication using token-based authentication. It allows you to:

- Generate secure API tokens for external applications
- Manage token lifecycle (creation, expiration, deletion)
- Authenticate API requests using HTTP headers
- Integrate seamlessly with TYPO3's security framework

## Key Features

### 🔐 Secure Token Generation
- Cryptographically secure random token generation
- Configurable token expiration (default: 1 year)
- Password hashing for secure storage

### 🛠 Easy Integration
- Simple PHP API for authentication checks
- PSR-7 compatible middleware support
- Clean separation of concerns

### 📊 Backend Management
- TYPO3 backend module for token management
- List, create, and delete tokens through the admin interface
- Clear token status and expiration information

### ⚡ CLI Support
- Command-line interface for token generation
- Perfect for CI/CD pipelines and automation
- Interactive and non-interactive modes

### 🧪 Modern Testing
- Comprehensive unit and functional test suite
- PHPUnit 11 with modern attributes
- TYPO3 TestingFramework integration
- DDEV development environment support

## Use Cases

### External Application Integration
Authenticate requests from mobile apps, third-party services, or microservices.

### API Gateway Integration
Use tokens as part of a larger API gateway or authentication strategy.

### Automated Services
Secure communication between automated systems and your TYPO3 installation.

### Development and Testing
Generate temporary tokens for development and testing environments.

## Architecture

The extension follows TYPO3's modern development patterns:

- **Domain-Driven Design**: Clear separation between domain logic and infrastructure
- **Dependency Injection**: Full DI container integration
- **PSR Standards**: PSR-7 message interfaces, PSR-15 middleware
- **Modern PHP**: Type declarations, readonly classes, PHP 8.1+ features

## Requirements

- TYPO3 CMS 12.4+ or 13.0+
- PHP 8.1 or higher
- MySQL/MariaDB or PostgreSQL database
- Composer for installation

## Next Steps

- [Installation Guide](Installation.md) - Get started with installation
- [Configuration](Configuration.md) - Configure the extension
- [Usage Examples](Usage.md) - Learn how to use the extension
