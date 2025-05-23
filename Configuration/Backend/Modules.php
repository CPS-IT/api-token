<?php

declare(strict_types=1);

/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

return [
    'system_ApiTokenGeneratesecret' => [
        'parent' => 'system',
        'position' => ['bottom'],
        'access' => 'admin',
        'iconIdentifier' => 'extension-api-token',
        'labels' => 'LLL:EXT:api_token/Resources/Private/Language/locallang_mod_token.xlf',
        'extensionName' => 'ApiToken',
        'controllerActions' => [
            \CPSIT\ApiToken\Controller\Backend\TokenController::class => [
                'list',
                'new',
                'create',
                'delete',
            ],
        ],
    ],
];
