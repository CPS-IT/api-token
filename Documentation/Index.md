# API Token Extension Documentation

The TYPO3 API Token extension provides secure API authentication for TYPO3 CMS applications.

## Table of Contents

1. [Introduction](Introduction.md)
2. [Installation](Installation.md)
3. [Configuration](Configuration.md)
4. [Usage](Usage.md)
5. [API Reference](ApiReference.md)
6. [CLI Commands](CliCommands.md)
7. [Backend Module](BackendModule.md)
8. [Development](Development.md)
9. [Testing](Testing.md)
10. [Troubleshooting](Troubleshooting.md)
11. [Migration Guide](Migration.md)
12. [Changelog](Changelog.md)

## Quick Start

1. **Install the extension**:
   ```bash
   composer require cpsit/api-token
   ```

2. **Generate a token** via CLI:
   ```bash
   ./vendor/bin/typo3 apitoken:generate
   ```

3. **Use in your API**:
   ```php
   if (\CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::isNotAuthenticated($request)) {
       return \CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::returnErrorResponse();
   }
   ```

4. **Add headers to API requests**:
   - `x-api-identifier`: Your generated identifier
   - `application-authorization`: Your generated secret

## Version Information

- **Current Version**: 1.0.0
- **TYPO3 Compatibility**: 12.4+ and 13.0+
- **PHP Compatibility**: 8.1+

## License

This extension is licensed under the GNU General Public License v2.0 or later.
