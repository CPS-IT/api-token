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

use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Domain\Model\Token;
use CPSIT\ApiToken\Middleware\ApiKeyAuthenticator;
use CPSIT\ApiToken\Request\Validation\AuthenticatedValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequestContext;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * End-to-end checks that a token only authenticates while it is usable:
 * not deleted, not disabled ("hidden"), not expired, and with the right secret.
 *
 * Every case sends a real frontend request to a plugin that calls
 * ApiTokenAuthenticator::isNotAuthenticated(), so the whole chain
 * (middleware -> authentication -> repository -> hashing) is exercised.
 */
#[CoversClass(ApiKeyAuthenticator::class)]
#[CoversClass(ApiKeyAuthentication::class)]
#[CoversClass(AuthenticatedValidator::class)]
final class TokenRevocationTest extends FunctionalTestCase
{
    private const SECRET = 'b2f1c0de-0000-4000-8000-000000000001';

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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Database/be_users.csv');
        $this->setUpFrontendRootPage(1, [
            'EXT:test_api_extension/Configuration/TypoScript/setup.typoscript',
        ]);

        $inOneYear = time() + 31536000;
        $this->insertToken('id-active', self::legacyHash(self::SECRET), $inOneYear);
        $this->insertToken('id-hidden', self::legacyHash(self::SECRET), $inOneYear, hidden: 1);
        $this->insertToken('id-deleted', self::legacyHash(self::SECRET), $inOneYear, deleted: 1);
        $this->insertToken('id-expired', self::legacyHash(self::SECRET), time() - 86400);
        $this->insertToken('id-no-expiry', self::legacyHash(self::SECRET), 0);
        $this->insertToken('id-sha256', 'sha256:' . hash('sha256', self::SECRET), $inOneYear);
    }

    #[Test]
    public function activeTokenIsGranted(): void
    {
        $response = $this->requestWithToken('id-active', self::SECRET);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Access granted', (string)$response->getBody());
    }

    #[Test]
    public function hiddenTokenIsRejected(): void
    {
        self::assertSame(403, $this->requestWithToken('id-hidden', self::SECRET)->getStatusCode());
    }

    #[Test]
    public function deletedTokenIsRejected(): void
    {
        self::assertSame(403, $this->requestWithToken('id-deleted', self::SECRET)->getStatusCode());
    }

    #[Test]
    public function expiredTokenIsRejected(): void
    {
        self::assertSame(403, $this->requestWithToken('id-expired', self::SECRET)->getStatusCode());
    }

    /**
     * valid_until = 0 means "expired", not "never expires" (unlike TYPO3's endtime).
     */
    #[Test]
    public function tokenWithoutValidUntilIsRejected(): void
    {
        self::assertSame(403, $this->requestWithToken('id-no-expiry', self::SECRET)->getStatusCode());
    }

    #[Test]
    public function wrongSecretIsRejected(): void
    {
        self::assertSame(403, $this->requestWithToken('id-active', 'wrong-secret')->getStatusCode());
    }

    #[Test]
    public function requestWithoutCredentialsIsRejected(): void
    {
        $response = $this->executeFrontendSubRequest(new InternalRequest('http://typo3-testing.local/'));

        self::assertSame(403, $response->getStatusCode());
    }

    /**
     * The middleware runs on every frontend request. An HTTP method outside
     * RestApiInterface::VALID_METHODS is simply not authenticated.
     */
    #[Test]
    public function unsupportedHttpMethodDoesNotBreakTheRequest(): void
    {
        $response = $this->executeFrontendSubRequest(
            $this->buildRequest('id-active', self::SECRET)->withMethod('PROPFIND')
        );

        self::assertSame(403, $response->getStatusCode());
    }

    /**
     * A logged-in backend user must not replace an API token unless the
     * integrator opted in via extension configuration.
     */
    #[Test]
    public function backendUserWithoutTokenIsRejectedByDefault(): void
    {
        $response = $this->executeFrontendSubRequest(
            new InternalRequest('http://typo3-testing.local/'),
            (new InternalRequestContext())->withBackendUserId(1)
        );

        self::assertSame(403, $response->getStatusCode());
    }

    #[Test]
    public function sha256HashedTokenIsGranted(): void
    {
        self::assertSame(200, $this->requestWithToken('id-sha256', self::SECRET)->getStatusCode());
    }

    /**
     * Legacy password hashes (argon2/bcrypt) are expensive to verify. After the
     * first successful use the stored hash is replaced by the cheap sha256 form.
     */
    #[Test]
    public function legacyHashIsUpgradedAfterSuccessfulAuthentication(): void
    {
        self::assertSame(200, $this->requestWithToken('id-active', self::SECRET)->getStatusCode());

        $hash = $this->fetchHash('id-active');
        self::assertSame('sha256:' . hash('sha256', self::SECRET), $hash);

        // and the upgraded hash keeps working
        self::assertSame(200, $this->requestWithToken('id-active', self::SECRET)->getStatusCode());
    }

    #[Test]
    public function legacyHashIsNotUpgradedWhenAuthenticationFails(): void
    {
        $before = $this->fetchHash('id-active');
        $this->requestWithToken('id-active', 'wrong-secret');
        $this->requestWithToken('id-hidden', self::SECRET);

        self::assertSame($before, $this->fetchHash('id-active'));
        self::assertStringStartsWith('$argon2', $this->fetchHash('id-hidden'));
    }

    private function requestWithToken(string $identifier, string $secret): ResponseInterface
    {
        return $this->executeFrontendSubRequest($this->buildRequest($identifier, $secret));
    }

    private function buildRequest(string $identifier, string $secret): InternalRequest
    {
        return (new InternalRequest('http://typo3-testing.local/'))
            ->withHeader(RestApiInterface::HEADER_NAME_IDENTIFIER, $identifier)
            ->withHeader(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION, $secret);
    }

    /**
     * Argon2id with minimal cost: previous hash format, fast in tests.
     */
    private static function legacyHash(string $secret): string
    {
        return password_hash($secret, PASSWORD_ARGON2ID, ['memory_cost' => 1024, 'time_cost' => 1, 'threads' => 1]);
    }

    private function insertToken(string $identifier, string $hash, int $validUntil, int $hidden = 0, int $deleted = 0): void
    {
        $this->getConnectionPool()->getConnectionForTable(Token::TABLE_NAME)->insert(Token::TABLE_NAME, [
            'pid' => 0,
            'name' => $identifier,
            'identifier' => $identifier,
            'hash' => $hash,
            'valid_until' => $validUntil,
            'hidden' => $hidden,
            'deleted' => $deleted,
            'crdate' => time(),
            'tstamp' => time(),
        ]);
    }

    private function fetchHash(string $identifier): string
    {
        // no restrictions: hidden/deleted rows must be readable here
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable(Token::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll();

        return (string)$queryBuilder->select('hash')
            ->from(Token::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter($identifier)))
            ->executeQuery()
            ->fetchOne();
    }
}
