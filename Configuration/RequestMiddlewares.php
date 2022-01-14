<?php
defined('TYPO3_MODE') or die();
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

return [
    'frontend' => [
        'fr/api-token/api-key-authenticator' => [
            'target' => \Fr\ApiToken\Middleware\ApiKeyAuthenticator::class,
            'after' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ],
    ],
];
