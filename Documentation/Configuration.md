# Configuration

Extension settings live in **Admin Tools > Settings > Extension Configuration > api_token**,
or in `config/system/settings.php` / `additional.php`:

```php
$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['api_token'] = [
    'allowBackendUserAuthentication' => '0',
];
```

## `allowBackendUserAuthentication`

**Default**: `0` (disabled)

When enabled, any logged-in TYPO3 backend user - editors included, regardless
of their permissions - passes `ApiTokenAuthenticator::isNotAuthenticated()`
without sending an API token.

Earlier versions always behaved this way; the setting keeps that behaviour
available. Enable it only if you rely on calling token-protected endpoints
from a logged-in backend session.

If the setting cannot be read (for example before the extension
configuration was written), backend users are **not** accepted.
