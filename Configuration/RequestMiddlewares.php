<?php
defined('TYPO3') or die();
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

return [
    'frontend' => [
        'cpsit/api-token/api-key-authenticator' => [
            'target' => \CPSIT\ApiToken\Middleware\ApiKeyAuthenticator::class,
            'after' => [
                'typo3/cms-frontend/page-argument-validator',
            ],
        ],
    ],
];
