# Changelog

All notable changes to the API Token extension will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive documentation structure
- Modern testing environment with PHPUnit 11
- DDEV development environment configuration
- GitHub Actions CI/CD pipeline
- Code quality tools (PHPStan, PHP CS Fixer, Rector)

### Changed
- Updated for TYPO3 v12 and v13 compatibility
- Modernized PHPUnit tests with attributes
- Replaced deprecated `dwenzel/t3extension-tools` with core TYPO3 APIs
- Fixed readonly class compatibility issues

### Fixed
- Backend module registration for TYPO3 v12+
- Icon registration using modern TYPO3 APIs
- Deprecated ViewHelper issues in backend templates
- Unit test mocking issues with readonly classes
- Functional test database configuration

## [1.0.0] - 2024-06-15

### Added
- Initial release of the API Token extension
- Token-based API authentication for TYPO3
- CLI command for token generation (`apitoken:generate`)
- Backend module for token management
- PSR-15 middleware for automatic authentication
- Comprehensive API for custom integrations
- Secure token generation using cryptographic functions
- Token expiration and validation
- Support for multiple HTTP methods (GET, POST, PUT, DELETE)

### Security
- Cryptographically secure token generation
- Password hashing using TYPO3's PasswordHashFactory
- No plain text storage of secrets
- Configurable token expiration

## [0.9.6] - 2024-03-15

### Fixed
- Minor bug fixes in token validation
- Improved error handling in CLI commands

### Changed
- Updated dependencies for better compatibility
- Improved documentation

## [0.9.5] - 2024-02-01

### Added
- Support for custom token expiration dates
- Enhanced backend module interface
- Better error messages for invalid tokens

### Fixed
- Token cleanup for expired tokens
- Memory usage optimization

## [0.9.0] - 2024-01-15

### Added
- Beta release with core functionality
- Basic token generation and validation
- Simple CLI interface
- Minimal backend integration

### Security
- Initial security implementation
- Basic token hashing

## Development History

### Pre-1.0.0 Development (2021-2024)

The extension was initially developed by Team Bravo and later maintained by CPS-IT GmbH. Key development milestones include:

- **2021**: Initial concept and basic implementation
- **2022**: Core authentication logic development
- **2023**: Backend module and CLI interface
- **2024**: TYPO3 v12 compatibility and modern testing setup

## Migration Notes

### From 0.x to 1.0.0

#### Breaking Changes
- Minimum TYPO3 version increased to 12.4
- Minimum PHP version increased to 8.1
- Removed support for legacy TYPO3 versions (< 12.4)

#### Required Actions
1. Update TYPO3 to version 12.4 or higher
2. Update PHP to version 8.1 or higher
3. Run database schema updates: `./vendor/bin/typo3 database:updateschema`
4. Clear all caches: `./vendor/bin/typo3 cache:flush`

#### Deprecated Features
- Legacy authentication methods (to be removed in 2.0.0)
- Old backend module structure (migrated automatically)

### From Legacy Setup to Modern Environment

If upgrading from a very old installation:

1. **Update Composer**: Ensure you're using Composer 2.0+
2. **Database Migration**:
   ```bash
   ./vendor/bin/typo3 database:updateschema
   ```
3. **Configuration Updates**:
   - Check `Configuration/` directory for new files
   - Update any custom middleware configurations
4. **Test Migration**:
   - Verify all existing tokens still work
   - Test CLI commands
   - Check backend module functionality

## API Changes

### Version 1.0.0 API

#### New Classes
- `CPSIT\ApiToken\Configuration\Backend\Modules`: Modern module registration
- `CPSIT\ApiToken\Configuration\Icons`: Icon registration
- Modern test classes with PHPUnit 11 attributes

#### Deprecated Classes
- Classes depending on `dwenzel/t3extension-tools` (removed)

#### Method Changes
- `TokenService`: Constructor now accepts optional parameters for dependency injection
- `ApiKeyAuthentication`: Improved type declarations and readonly class support

## Security Updates

### 1.0.0 Security Enhancements
- Updated cryptographic functions for better security
- Improved token validation logic
- Enhanced protection against timing attacks
- Better input validation and sanitization

### Historical Security Issues
No security vulnerabilities have been reported for this extension.

## Performance Improvements

### 1.0.0 Performance Updates
- Optimized database queries for token lookup
- Reduced memory usage in token generation
- Improved caching strategies
- Better error handling performance

## Documentation Changes

### 1.0.0 Documentation
- Complete documentation restructure
- Added comprehensive API reference
- Detailed usage examples and guides
- Testing documentation
- Development environment setup

## Testing and Quality Assurance

### 1.0.0 Testing Updates
- Migrated to PHPUnit 11 with modern attributes
- Added functional tests with TYPO3 TestingFramework
- Implemented comprehensive code coverage
- Added static analysis with PHPStan level 8
- Integrated PHP CS Fixer for code standards
- Set up automated testing with GitHub Actions

## Development Environment

### 1.0.0 Environment Updates
- Complete DDEV configuration
- Docker-based development setup
- Automated TYPO3 installation
- Pre-configured testing environment
- Code quality tools integration

## Future Plans

### Planned for 1.1.0
- Rate limiting functionality
- Token scoping and permissions
- Audit logging for authentication attempts
- Enhanced CLI commands
- REST API for token management

### Planned for 2.0.0
- Multi-tenant support
- Token rotation strategies
- Advanced security features
- Performance optimizations
- API versioning

### Long-term Goals
- OAuth 2.0 integration
- JWT token support
- Advanced monitoring and analytics
- Integration with external identity providers

## Contributors

### Core Team
- **CPS-IT GmbH** - Current maintainer and primary developer
- **Team Bravo** - Original developer (2021-2023)

### Community Contributors
We welcome contributions from the TYPO3 community. See our [Development Guide](Development.md) for contribution guidelines.

## License Changes

The extension has always been licensed under the GNU General Public License v2.0 or later.

## Support and Maintenance

### Current Support Status
- **Active Development**: Yes
- **Security Updates**: Yes
- **Bug Fixes**: Yes
- **Feature Requests**: Accepted via GitHub Issues

### Support Channels
- GitHub Issues: Bug reports and feature requests
- GitHub Discussions: Community support
- Documentation: Comprehensive guides and references

## Release Process

### Release Schedule
- **Major versions** (x.0.0): Annual releases with breaking changes
- **Minor versions** (1.x.0): Quarterly releases with new features
- **Patch versions** (1.0.x): Monthly releases with bug fixes and security updates

### Release Criteria
- All tests must pass
- Code coverage > 90%
- Documentation must be up to date
- Security review completed
- Community feedback incorporated

---

For the most current information, please check the [project repository](https://github.com/CPS-IT/api-token) and [documentation](Index.md).
