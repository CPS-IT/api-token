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

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'tx_apitoken_domain_model_token' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:api_token/Resources/Public/Icons/tx_apitoken_domain_model_token.svg',
    ],
    'extension-api-token' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:api_token/Resources/Public/Icons/Extension.svg',
    ],
];
