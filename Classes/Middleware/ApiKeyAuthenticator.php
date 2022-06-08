<?php
/**
 * This file is part of the api_token Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Middleware;

use Fr\ApiToken\Authentication\ApiKeyAuthentication;
use Fr\ApiToken\Context\AuthenticatedAspect;
use Fr\ApiToken\Exception\InvalidHttpMethodException;
use Fr\ApiToken\Request\Validation\ResolvedRouteValidator;
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
    public function __construct(Context $context = null, ApiKeyAuthentication $authentication = null, ResolvedRouteValidator $routeValidator = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->authentication = $authentication ?? GeneralUtility::makeInstance(ApiKeyAuthentication::class);
        $this->routeValidator = $routeValidator ?? GeneralUtility::makeInstance(ResolvedRouteValidator::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws InvalidHttpMethodException
     */
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
     * @param ServerRequestInterface $request
     * @throws InvalidHttpMethodException
     * @throws \Exception
     */
    protected function setApiAuthenticatedAspect(ServerRequestInterface $request): void
    {
        $this->authentication->withMethod($request->getMethod());

        if ($request->hasHeader(ApiKeyAuthentication::HEADER_NAME_IDENTIFIER)) {
            $identifierHeader = $request->getHeader(ApiKeyAuthentication::HEADER_NAME_IDENTIFIER);
            $this->authentication->withIdentifier($identifierHeader[0]);
        }
        if ($request->hasHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION)) {
            $authHeader = $request->getHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION);
            $this->authentication->fromHeader($authHeader[0]);
        }

        $aspect = new AuthenticatedAspect($this->authentication);
        $this->context->setAspect(AuthenticatedAspect::ASPECT_NAME, $aspect);
    }
}
