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

use CPSIT\ApiToken\Http\ResponseFactory;
use CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Test API Controller for functional testing
 */
class ApiController
{
    public function publicAction(string $content, array $config, ServerRequestInterface $request): string
    {
        // This endpoint does not require authentication
        $response = ResponseFactory::createOkResponse([
            'status' => 'success',
            'message' => 'Public endpoint accessible',
            'data' => ['timestamp' => time()],
        ]);

        return $this->convertResponseToString($response);
    }

    public function protectedAction(string $content, array $config, ServerRequestInterface $request): string
    {
        // This endpoint requires authentication
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            $response = ResponseFactory::createForbiddenResponse('Access denied. Valid API token required.');
            return $this->convertResponseToString($response);
        }

        $response = ResponseFactory::createOkResponse([
            'status' => 'success',
            'message' => 'Protected endpoint accessed',
            'data' => ['authenticated' => true, 'timestamp' => time()],
        ]);

        return $this->convertResponseToString($response);
    }

    public function adminAction(string $content, array $config, ServerRequestInterface $request): string
    {
        // This endpoint also requires authentication
        if (ApiTokenAuthenticator::isNotAuthenticated($request)) {
            $response = ResponseFactory::createForbiddenResponse('Access denied. Administrator privileges required.');
            return $this->convertResponseToString($response);
        }

        $response = ResponseFactory::createOkResponse([
            'status' => 'success',
            'message' => 'Admin endpoint accessed',
            'data' => ['role' => 'admin', 'timestamp' => time()],
        ]);

        return $this->convertResponseToString($response);
    }

    /**
     * Convert PSR-7 Response to string for TYPO3 USER function compatibility
     */
    private function convertResponseToString(ResponseInterface $response): string
    {
        // Set HTTP status code for the response
        if ($response instanceof JsonResponse) {
            http_response_code($response->getStatusCode());
        }

        return (string)$response->getBody();
    }
}
