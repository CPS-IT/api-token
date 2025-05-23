<?php

declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Configuration;

final class Extension
{
    public const KEY = 'api_token';
    public const NAME = 'ApiToken';
    public const VENDOR_NAME = 'CPSIT';
    public const EXTENSION_KEY = self::KEY;

    /**
     * Icon identifier for token model
     */
    public const TOKEN_SVG = \CPSIT\ApiToken\Domain\Model\Token::TABLE_NAME;
}
