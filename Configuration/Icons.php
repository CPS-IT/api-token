<?php

declare(strict_types=1);

/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
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
