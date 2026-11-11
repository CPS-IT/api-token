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

namespace CPSIT\ApiToken\Tests\Unit\Middleware;

use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Context\AuthenticatedAspect;
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Middleware\ApiKeyAuthenticator;
use CPSIT\ApiToken\Request\Validation\ResolvedRouteValidator;
use CPSIT\ApiToken\Service\TokenServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiKeyAuthenticator::class)]
final class ApiKeyAuthenticatorTest extends UnitTestCase
{
    private Context $context;
    private TokenRepository|MockObject $repository;
    private ApiKeyAuthenticator $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->context = new Context();

        $tokenService = $this->createMock(TokenServiceInterface::class);
        $tokenService->method('check')->willReturnCallback(
            static fn(string $secret): bool => $secret === 'valid-secret'
        );
        $this->repository = $this->getMockBuilder(TokenRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneRecordByIdentifier'])
            ->getMock();

        $routeValidator = $this->createMock(ResolvedRouteValidator::class);
        $routeValidator->method('validate')->willReturn(true);

        $this->subject = new ApiKeyAuthenticator(
            $this->context,
            new ApiKeyAuthentication($tokenService, $this->repository),
            $routeValidator
        );
    }

    #[Test]
    public function unsupportedHttpMethodIsUnauthenticatedInsteadOfThrowing(): void
    {
        $this->repository->expects(self::never())->method('findOneRecordByIdentifier');

        $this->subject->process($this->request('PROPFIND', 'valid-secret'), $this->handler());

        self::assertFalse($this->isAuthenticated());
    }

    #[Test]
    public function lowercaseHttpMethodIsUnauthenticatedInsteadOfThrowing(): void
    {
        $this->subject->process($this->request('get', 'valid-secret'), $this->handler());

        self::assertFalse($this->isAuthenticated());
    }

    #[Test]
    public function validCredentialsAuthenticate(): void
    {
        $this->repository->method('findOneRecordByIdentifier')->willReturn(self::validRecord());

        $this->subject->process($this->request('GET', 'valid-secret'), $this->handler());

        self::assertTrue($this->isAuthenticated());
    }

    /**
     * The middleware (and its ApiKeyAuthentication) is a shared service. A
     * request without credentials must not inherit an earlier positive result,
     * e.g. in long-running worker processes or in-process sub-requests.
     */
    #[Test]
    public function resultDoesNotLeakIntoTheNextRequest(): void
    {
        $this->repository->method('findOneRecordByIdentifier')->willReturn(self::validRecord());

        $this->subject->process($this->request('GET', 'valid-secret'), $this->handler());
        self::assertTrue($this->isAuthenticated());

        $this->subject->process(new ServerRequest('http://localhost/', 'GET'), $this->handler());
        self::assertFalse($this->isAuthenticated());
    }

    #[Test]
    public function identifierWithoutSecretDoesNotQueryTheDatabase(): void
    {
        $this->repository->expects(self::never())->method('findOneRecordByIdentifier');

        $request = (new ServerRequest('http://localhost/', 'GET'))
            ->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, 'some-identifier');
        $this->subject->process($request, $this->handler());

        self::assertFalse($this->isAuthenticated());
    }

    private function isAuthenticated(): bool
    {
        return (bool)$this->context->getPropertyFromAspect(
            AuthenticatedAspect::ASPECT_NAME,
            AuthenticatedAspect::AUTHENTICATED,
            false
        );
    }

    private function request(string $method, string $secret): ServerRequestInterface
    {
        return (new ServerRequest('http://localhost/', $method))
            ->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, 'some-identifier')
            ->withHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION, $secret);
    }

    private function handler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new Response();
            }
        };
    }

    /**
     * @return array<string, mixed>
     */
    private static function validRecord(): array
    {
        return [
            'uid' => 1,
            'identifier' => 'some-identifier',
            'hash' => 'irrelevant-for-mocked-service',
            'valid_until' => time() + 86400,
            'hidden' => 0,
            'deleted' => 0,
        ];
    }
}
