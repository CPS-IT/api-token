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

namespace CPSIT\ApiToken\Middleware;

use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Context\AuthenticatedAspect;
use CPSIT\ApiToken\Request\Validation\ResolvedRouteValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiKeyAuthenticator implements MiddlewareInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ApiKeyAuthentication
     */
    protected $authentication;

    /**
     * @var ResolvedRouteValidator
     */
    protected $routeValidator;

    /**
     * ApiKeyAuthenticator constructor.
     *
     * @param Context|null $context
     * @param ApiKeyAuthentication|null $authentication
     * @param ResolvedRouteValidator|null $routeValidator
     */
    public function __construct(?Context $context = null, ?ApiKeyAuthentication $authentication = null, ?ResolvedRouteValidator $routeValidator = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->authentication = $authentication ?? GeneralUtility::makeInstance(ApiKeyAuthentication::class);
        $this->routeValidator = $routeValidator ?? GeneralUtility::makeInstance(ResolvedRouteValidator::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /**
         * Fixme We should probably remove the routeValidator and let
         * this class be responsible for the api key validation only
         */
        if ($this->routeValidator->validate($request)) {
            $this->setApiAuthenticatedAspect($request);
        }
        return $handler->handle($request);
    }

    /**
     * Runs for every frontend request with a resolved route, so it must
     * never throw: an unsupported HTTP method simply stays unauthenticated.
     *
     * @param ServerRequestInterface $request
     * @throws \Exception
     */
    protected function setApiAuthenticatedAspect(ServerRequestInterface $request): void
    {
        // The injected instance is a shared service; work on a stateless copy
        // so nothing from a previous request (or another consumer) carries over.
        $authentication = $this->authentication->forNewRequest();

        if (
            in_array($request->getMethod(), RestApiInterface::VALID_METHODS, true)
            && $request->hasHeader(ApiKeyAuthentication::HEADER_NAME_IDENTIFIER)
            && $request->hasHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION)
        ) {
            $authentication->withMethod($request->getMethod());
            $authentication->withIdentifier($request->getHeader(ApiKeyAuthentication::HEADER_NAME_IDENTIFIER)[0]);
            $authentication->fromHeader($request->getHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION)[0]);
        }

        $aspect = new AuthenticatedAspect($authentication);
        $this->context->setAspect(AuthenticatedAspect::ASPECT_NAME, $aspect);
    }
}
