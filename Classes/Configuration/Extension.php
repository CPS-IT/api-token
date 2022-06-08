<?php
declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Configuration;

use DWenzel\T3extensionTools\Configuration\ExtensionConfiguration;
use Fr\ApiToken\Configuration\Module\TokenModuleRegistration;

final class Extension extends ExtensionConfiguration
{
    /**
     * Plugins to register
     *
     */
    public const PLUGINS_TO_REGISTER = [];

    /**
     * Backend modules to register
     */
    public const MODULES_TO_REGISTER = [
        TokenModuleRegistration::class
    ];

    public const KEY = 'api_token';
    public const NAME = 'ApiToken';
    public const VENDOR_NAME = 'Fr';
    public const EXTENSION_KEY = self::KEY;

    /**
     * SVG icons to register
     */
    public const TOKEN_SVG = \Fr\ApiToken\Domain\Model\Token::TABLE_NAME;
    protected const SVG_ICONS_TO_REGISTER = [
        self::TOKEN_SVG => 'EXT:' . self::KEY . '/Resources/Public/Icons/tx_apitoken_domain_model_token.svg',
    ];
}
