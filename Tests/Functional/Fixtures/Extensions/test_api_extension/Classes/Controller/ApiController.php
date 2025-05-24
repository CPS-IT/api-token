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

namespace CPSIT\ApiToken\TestApiExtension\Controller;

use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Test API Controller for functional testing
 */
class ApiController
{
    public function publicAction(string $content, array $config): string
    {
        // This endpoint does not require authentication
        return json_encode([
            'status' => 'success',
            'message' => 'Public endpoint accessible',
            'data' => ['timestamp' => time()]
        ]);
    }

    public function protectedAction(string $content, array $config): string
    {
        // This endpoint requires authentication
        $request = $GLOBALS['TYPO3_REQUEST'];
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            http_response_code(403);
            return json_encode([
                'status' => 'error',
                'message' => 'Forbidden',
            ]);
        }

        return json_encode([
            'status' => 'success',
            'message' => 'Protected endpoint accessed',
            'data' => ['authenticated' => true, 'timestamp' => time()]
        ]);
    }

    public function adminAction(string $content, array $config): string
    {
        // This endpoint also requires authentication
        $request = $GLOBALS['TYPO3_REQUEST'];
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            http_response_code(403);
            return json_encode([
                'status' => 'error',
                'message' => 'Forbidden',
            ]);
        }

        return json_encode([
            'status' => 'success',
            'message' => 'Admin endpoint accessed',
            'data' => ['role' => 'admin', 'timestamp' => time()]
        ]);
    }
}