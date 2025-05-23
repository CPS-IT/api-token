<?php

declare(strict_types=1);

/*
 * This file is part of the api_token Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace CPSIT\ApiToken\Configuration;

use CPSIT\ApiToken\Domain\Model\Token;

final class Extension
{
    public const string KEY = 'api_token';
    public const string NAME = 'ApiToken';
    public const string VENDOR_NAME = 'CPSIT';
    public const string EXTENSION_KEY = self::KEY;

    /**
     * Icon identifier for token model
     */
    public const string TOKEN_SVG = Token::TABLE_NAME;
}
