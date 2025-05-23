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

namespace CPSIT\ApiToken\Tests\Functional\Middleware;

use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Middleware\ApiKeyAuthenticator;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ApiKeyAuthenticator::class)]
class ApiKeyAuthenticatorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'cpsit/api-token',
    ];

    protected array $coreExtensionsToLoad = [
        'core',
        'extbase',
        'fluid',
    ];

    private ApiKeyAuthenticator $subject;
    private RequestHandlerInterface $requestHandler;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = $this->get(ApiKeyAuthenticator::class);

        $this->requestHandler = new class () implements RequestHandlerInterface {
            public function handle(\Psr\Http\Message\ServerRequestInterface $request): ResponseInterface
            {
                return new \GuzzleHttp\Psr7\Response(200, [], 'OK');
            }
        };
    }

    #[Test]
    public function processCallsNextHandlerWhenNoAuthenticationRequired(): void
    {
        $request = new ServerRequest('GET', '/api/test');

        $response = $this->subject->process($request, $this->requestHandler);

        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('OK', (string)$response->getBody());
    }

    #[Test]
    public function processReturns401WhenAuthenticationRequiredButHeadersMissing(): void
    {
        // Create a request that would require authentication
        $request = new ServerRequest('POST', '/api/protected');
        $request = $request->withAttribute('route', $this->createMockRoute());

        $response = $this->subject->process($request, $this->requestHandler);

        self::assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function processReturns401WithInvalidCredentials(): void
    {
        $request = new ServerRequest('POST', '/api/protected');
        $request = $request->withAttribute('route', $this->createMockRoute());
        $request = $request->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, 'invalid-identifier');
        $request = $request->withHeader('application-authorization', 'invalid-secret');

        $response = $this->subject->process($request, $this->requestHandler);

        self::assertEquals(401, $response->getStatusCode());
    }

    private function createMockRoute(): object
    {
        return new class () {
            public function getOption(string $name): mixed
            {
                // Mock route that requires authentication
                return $name === 'api_token_required' ? true : null;
            }
        };
    }
}
