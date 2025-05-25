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
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\PropagateResponseException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Test API Controller for functional testing
 */
class ApiController extends ActionController
{
    public function protectedAction(): ResponseInterface
    {
        // This endpoint requires authentication
        if (ApiTokenAuthenticator::isNotAuthenticated($this->request)) {
            $response = ApiTokenAuthenticator::returnErrorResponse();
            throw new PropagateResponseException($response, $response->getStatusCode());
        }

        $response = new JsonResponse(
            ['message' => 'Access granted'],
            200
        );
        return $response;
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
