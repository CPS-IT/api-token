<?php
declare(strict_types=1);
namespace Fr\ApiToken\Configuration;
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

use DWenzel\T3extensionTools\Configuration\ExtensionConfiguration;

final class Extension extends ExtensionConfiguration
{
    /**
     * Plugins to register
     *
     */
    public const PLUGINS_TO_REGISTER = [];

    public const KEY = 'api_token';
    public const NAME = 'ApiToken';
    public const VENDOR_NAME = 'Fr';
    public const EXTENSION_KEY = self::KEY;
}