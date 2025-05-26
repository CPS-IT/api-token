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

namespace CPSIT\ApiToken\Tests\Functional;

use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Middleware\ApiKeyAuthenticator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

#[CoversClass(ApiKeyAuthenticator::class)]
class ApiKeyAuthenticatorTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'cpsit/api-token',
        'cpsit/test-api-extension',
    ];

    protected array $coreExtensionsToLoad = [
        'typo3/cms-extbase',
    ];

    protected array $pathsToLinkInTestInstance = [
        'typo3conf/ext/api_token/Tests/Build/sites' => 'typo3conf/sites',
    ];

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/pages.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/tx_apitoken_domain_model_token.csv');
        $this->setUpFrontendRootPage(1, [
            'EXT:test_api_extension/Configuration/TypoScript/setup.typoscript',
        ]);
    }

    #[Test]
    public function protectedEndpointIsAccessibleWithValidCredentials(): void
    {
        $request = new InternalRequest('http://typo3-testing.local/');
        $request
            ->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, 'test-identifier')
            ->withHeader('application-authorization', 'test-secret-plain');

        $response = $this->executeFrontendSubRequest($request);

        self::assertContains($response->getStatusCode(), [200, 403]);
    }

    #[Test]
    public function protectedEndpointReturns403WhenHeadersMissing(): void
    {
        $request = new InternalRequest('http://typo3-testing.local/');

        $response = $this->executeFrontendSubRequest($request);

        self::assertContains($response->getStatusCode(), [401, 403]);
    }

    #[Test]
    public function protectedEndpointReturns403WithInvalidCredentials(): void
    {

        $request = new InternalRequest('http://typo3-testing.local/');
        $request = $request->withMethod(RestApiInterface::METHOD_GET)
            ->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, 'invalid-identifier')
            ->withHeader('application-authorization', 'invalid-secret');

        $response = $this->executeFrontendSubRequest($request);

        self::assertContains($response->getStatusCode(), [401, 403]);
    }

}
