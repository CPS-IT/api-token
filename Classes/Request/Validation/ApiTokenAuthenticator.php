<?php

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

namespace CPSIT\ApiToken\Request\Validation;

use CPSIT\ApiToken\Http\ResponseFactory;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * First generate token: ./vendor/bin/typo3cms apitoken:generate
 *
 * Then use these lines in your API handler to verify token:
 *
 * if(\CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::isNotAuthenticated($request)){
        return \CPSIT\ApiToken\Request\Validation\ApiTokenAuthenticator::returnErrorResponse();
    }
 *
 * Call X-API-IDENTIFIER = {your identifier} and application-authorization = {generated secret from above}
 * in your http header call of API endpoint which uses ApiTokenAuthenticator class above.
 */
class ApiTokenAuthenticator implements Authenticator
{
    #[\Override]
    public static function isNotAuthenticated(ServerRequestInterface $request): bool
    {
        return !(new AuthenticatedValidator())->validate($request);
    }

    #[\Override]
    public static function returnErrorResponse(): JsonResponse
    {
        return ResponseFactory::createForbiddenResponse();
    }
}
