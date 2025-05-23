# Installation

This guide covers the installation and initial setup of the API Token extension.

## Requirements

Before installing, ensure your system meets these requirements:

- **TYPO3**: 12.4 LTS or 13.0+
- **PHP**: 8.1 or higher
- **Database**: MySQL 8.0+, MariaDB 10.5+, or PostgreSQL 12+
- **Composer**: 2.0 or higher

## Installation Methods

### Method 1: Composer (Recommended)

Install the extension using Composer:

```bash
# Navigate to your TYPO3 project root
cd /path/to/your/typo3-project

# Install the extension
composer require cpsit/api-token

# Update the database schema
./vendor/bin/typo3 database:updateschema
```

### Method 2: TYPO3 Extension Manager

1. Log into the TYPO3 backend as administrator
2. Go to **Admin Tools** > **Extensions**
3. Search for "api_token"
4. Click **Install** next to the API Token extension
5. Update the database schema when prompted

## Post-Installation Setup

### 1. Activate the Extension

The extension should be automatically activated after installation. Verify by checking:

```bash
./vendor/bin/typo3 extension:list | grep api_token
```

### 2. Database Schema Update

Ensure the database tables are created:

```bash
./vendor/bin/typo3 database:updateschema
```

This creates the `tx_apitoken_domain_model_token` table.

### 3. Clear Caches

Clear all TYPO3 caches:

```bash
./vendor/bin/typo3 cache:flush
```

### 4. Verify Installation

Check that the extension is properly installed:

```bash
# List available CLI commands (should include apitoken:generate)
./vendor/bin/typo3 list apitoken

# Check backend module (should list the Token module)
./vendor/bin/typo3 backend:modules
```

## Directory Structure

After installation, the extension creates this structure:

```
typo3conf/ext/api_token/           # Extension directory
├── Classes/                       # PHP classes
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
│   ├── Backend/                  # Backend module config
│   ├── Icons.php                 # Icon registration
│   ├── Services.yaml             # DI container config
│   └── TCA/                      # Table configuration
├── Resources/                    # Frontend resources
│   ├── Private/                  # Backend templates
│   └── Public/                   # Public assets
└── Tests/                        # Test suite
    ├── Functional/               # Functional tests
    └── Unit/                     # Unit tests
```

## Development Installation

For development, clone the repository and set up the development environment:

```bash
# Clone the repository
git clone https://github.com/CPS-IT/api-token.git
cd api-token

# Start DDEV environment
ddev start

# Install dependencies
ddev composer install

# Run tests to verify setup
ddev composer test:unit
ddev composer test:functional
```

## Docker/DDEV Setup

The extension includes a complete DDEV configuration for development:

```bash
# Start the development environment
ddev start

# Install TYPO3
ddev composer install
ddev exec .Build/bin/typo3 install:setup --no-interaction

# Generate test data
ddev exec .Build/bin/typo3 apitoken:generate --name="Test Token" --description="Development testing"
```

## Troubleshooting Installation

### Common Issues

**Database connection errors**:
```bash
# Check database connectivity
./vendor/bin/typo3 database:list
```

**Extension not found**:
```bash
# Verify Composer autoloader
composer dump-autoload
```

**Cache issues**:
```bash
# Clear all caches
./vendor/bin/typo3 cache:flush --force
```

**Permission errors**:
```bash
# Fix file permissions (adjust paths as needed)
sudo chown -R www-data:www-data /path/to/typo3
sudo chmod -R 755 /path/to/typo3
```

### Verification Commands

Run these commands to verify your installation:

```bash
# Check extension status
./vendor/bin/typo3 extension:list | grep api_token

# Verify database tables
./vendor/bin/typo3 database:list | grep tx_apitoken

# Test CLI command
./vendor/bin/typo3 apitoken:generate --help

# Check backend module access
./vendor/bin/typo3 backend:modules | grep -i token
```

## Next Steps

After successful installation:

1. [Configure the extension](Configuration.md)
2. [Generate your first token](CliCommands.md)
3. [Learn the usage patterns](Usage.md)
4. [Explore the backend module](BackendModule.md)
