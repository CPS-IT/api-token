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

namespace CPSIT\ApiToken\Tests\Unit\Authentication;

use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Exception\InvalidHttpMethodException;
use CPSIT\ApiToken\Service\TokenService;
use CPSIT\ApiToken\Service\TokenServiceInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiKeyAuthentication::class)]
class ApiKeyAuthenticationTest extends UnitTestCase
{
    protected ApiKeyAuthentication $subject;
    protected TokenRepository|MockObject $tokenRepository;
    protected TokenServiceInterface|MockObject $tokenService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenService = $this->createMock(TokenServiceInterface::class);
        $this->tokenRepository = $this->getMockBuilder(TokenRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'findOneRecordByIdentifier',
                ]
            )
            ->getMock();
        $this->subject = new ApiKeyAuthentication($this->tokenService, $this->tokenRepository);
    }

    #[Test]
    public function testIsAuthenticatedInitiallyReturnsFalse(): void
    {
        self::assertFalse($this->subject->isAuthenticated());
    }

    #[Test]
    public function testValidateHeaderNameReturnsFalseForInvalidName(): void
    {
        $name = 'invalidFooName';
        self::assertFalse($this->subject->validateHeaderName($name));
    }

    #[Test]
    public function testValidateHeaderNameReturnsTrueForValidName(): void
    {
        self::assertTrue($this->subject->validateHeaderName(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION));
    }

    #[Test]
    public function testValidUntilInitiallyReturnsPastDateTime(): void
    {
        $now = new \DateTimeImmutable('now');
        $validUntil = $this->subject->validUntil();

        self::assertTrue($now > $validUntil);
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForInvalidName(): void
    {
        $invalidHeaderName = 'booFar';
        $value = 'no';

        $invalidAuthentication = $this->subject->fromHeader($invalidHeaderName, $value);
        self::assertFalse($invalidAuthentication->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyValue(): void
    {
        $invalidHeaderName = '';
        $secret = 'no';

        $invalidAuthentication = $this->subject->fromHeader($secret, $invalidHeaderName);
        self::assertFalse($invalidAuthentication->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptResultFromRepository(): void
    {
        $secret = 'foo';
        $identifier = 'baz';
        $emptyResultFromRepository = [];

        $this->tokenRepository->expects(self::once())
            ->method('findOneRecordByIdentifier')
            ->with($identifier)
            ->willReturn($emptyResultFromRepository);

        $this->subject->withIdentifier($identifier);

        $invalidAuthentication = $this->subject->fromHeader($secret);
        self::assertFalse($invalidAuthentication->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyIdentifier(): void
    {
        $secret = 'foo';
        $authentication = $this->subject->fromHeader($secret);
        self::assertFalse($authentication->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsAuthenticatedInstanceForValidHeader(): void
    {
        $validSecret = 'foo';
        $hashOfSecret = 'bar';
        $identifier = 'baz';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $validUntil = new \DateTimeImmutable('tomorrow', $timeZone);
        $validRecord = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $hashOfSecret,
            'valid_until' => $validUntil->format('U'),
        ];

        $this->tokenRepository->expects(self::once())
            ->method('findOneRecordByIdentifier')
            ->with($identifier)
            ->willReturn($validRecord);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->expects(self::once())
            ->method('check')
            ->with($validSecret, $hashOfSecret)
            ->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);
        self::assertTrue($authentication->isAuthenticated());

        self::assertEquals($validUntil, $authentication->validUntil());
    }

    #[Test]
    public function testGetMethodInitiallyReturnsGet(): void
    {
        self::assertSame(RestApiInterface::METHOD_GET, $this->subject->getMethod());
    }

    /**
     * @return array<string, array<string>>
     */
    public static function validMethodsDataProvider(): array
    {
        $data = [];
        foreach (RestApiInterface::VALID_METHODS as $method) {
            $data[$method] = [$method];
        }

        return $data;
    }

    #[Test]
    #[DataProvider('validMethodsDataProvider')]
    public function testWithMethodSetsValidMethods(string $method): void
    {
        $this->subject->withMethod($method);
        self::assertSame($method, $this->subject->getMethod());
    }

    #[Test]
    public function testWithMethodThrowsExceptionForInvalidMethod(): void
    {
        $invalidMethod = 'foo';
        $this->expectException(InvalidHttpMethodException::class);
        $this->expectExceptionCode(1585497878);
        $this->subject->withMethod($invalidMethod);
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptySecret(): void
    {
        $secret = '';
        $result = $this->subject->fromHeader($secret);
        self::assertFalse($result->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForExpiredValidUntil(): void
    {
        $validSecret = 'foo';
        $hashOfSecret = 'bar';
        $identifier = 'baz';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $expiredDate = new \DateTimeImmutable('yesterday', $timeZone);
        $expiredToken = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $hashOfSecret,
            'valid_until' => $expiredDate->format('U'),
        ];

        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn($expiredToken);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->method('check')->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);

        self::assertFalse($authentication->isAuthenticated());
    }

    #[Test]
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyHash(): void
    {
        $validSecret = 'foo';
        $emptyHashOfSecret = '';
        $identifier = 'baz';

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $validDate = new \DateTimeImmutable('tomorrow', $timeZone);
        $expiredToken = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $emptyHashOfSecret,
            'valid_until' => $validDate->format('U'),
        ];

        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn($expiredToken);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->method('check')->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);

        self::assertFalse($authentication->isAuthenticated());
    }

    /**
     * @return array<string, array{array<string, int>}>
     */
    public static function revokedRecordDataProvider(): array
    {
        return [
            'hidden' => [['hidden' => 1]],
            'deleted' => [['deleted' => 1]],
        ];
    }

    /**
     * Defense in depth: even if a (replaced) repository returns a revoked
     * record, it must not authenticate.
     *
     * @param array<string, int> $revokedFlags
     */
    #[Test]
    #[DataProvider('revokedRecordDataProvider')]
    public function fromHeaderRejectsRevokedRecordsEvenWithCorrectSecret(array $revokedFlags): void
    {
        $this->tokenRepository->method('findOneRecordByIdentifier')
            ->willReturn(self::validRecord($revokedFlags));
        $this->tokenService->method('check')->willReturn(true);

        $this->subject->withIdentifier('baz');

        self::assertFalse($this->subject->fromHeader('foo')->isAuthenticated());
    }

    #[Test]
    public function fromHeaderDoesNotKeepAnEarlierPositiveResult(): void
    {
        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn(self::validRecord());
        $this->tokenService->method('check')->willReturnOnConsecutiveCalls(true, false);

        $this->subject->withIdentifier('baz');
        self::assertTrue($this->subject->fromHeader('foo')->isAuthenticated());
        self::assertFalse($this->subject->fromHeader('wrong')->isAuthenticated());
        self::assertFalse($this->subject->fromHeader('')->isAuthenticated());
    }

    #[Test]
    public function forNewRequestReturnsUnauthenticatedCopyAndKeepsOriginal(): void
    {
        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn(self::validRecord());
        $this->tokenService->method('check')->willReturn(true);
        $this->subject->withMethod(RestApiInterface::METHOD_POST)->withIdentifier('baz')->fromHeader('foo');

        $fresh = $this->subject->forNewRequest();

        self::assertNotSame($this->subject, $fresh);
        self::assertFalse($fresh->isAuthenticated());
        self::assertSame(RestApiInterface::METHOD_GET, $fresh->getMethod());
        // a fresh copy has no identifier, so even a valid secret fails
        self::assertFalse($fresh->fromHeader('foo')->isAuthenticated());
        self::assertTrue($this->subject->isAuthenticated());
    }

    #[Test]
    public function legacyHashIsUpgradedAfterSuccessfulCheck(): void
    {
        $legacyHasher = $this->createMock(PasswordHashInterface::class);
        $legacyHasher->method('checkPassword')->willReturn(true);
        $tokenService = new TokenService(null, $legacyHasher);
        $repository = $this->createRepositoryMock(self::validRecord(['hash' => '$argon2id$v=19$legacy']));
        $repository->expects(self::once())
            ->method('updateHash')
            ->with(1, TokenService::HASH_PREFIX . hash('sha256', 'foo'));

        $subject = new ApiKeyAuthentication($tokenService, $repository);

        self::assertTrue($subject->withIdentifier('baz')->fromHeader('foo')->isAuthenticated());
    }

    #[Test]
    public function hashIsNotUpgradedWhenCheckFails(): void
    {
        $legacyHasher = $this->createMock(PasswordHashInterface::class);
        $legacyHasher->method('checkPassword')->willReturn(false);
        $repository = $this->createRepositoryMock(self::validRecord(['hash' => '$argon2id$v=19$legacy']));
        $repository->expects(self::never())->method('updateHash');

        $subject = new ApiKeyAuthentication(new TokenService(null, $legacyHasher), $repository);

        self::assertFalse($subject->withIdentifier('baz')->fromHeader('foo')->isAuthenticated());
    }

    #[Test]
    public function currentHashIsNotRewritten(): void
    {
        $tokenService = new TokenService(null, $this->createMock(PasswordHashInterface::class));
        $repository = $this->createRepositoryMock(self::validRecord(['hash' => $tokenService->hash('foo')]));
        $repository->expects(self::never())->method('updateHash');

        $subject = new ApiKeyAuthentication($tokenService, $repository);

        self::assertTrue($subject->withIdentifier('baz')->fromHeader('foo')->isAuthenticated());
    }

    #[Test]
    public function failingHashUpgradeDoesNotFailAuthentication(): void
    {
        $legacyHasher = $this->createMock(PasswordHashInterface::class);
        $legacyHasher->method('checkPassword')->willReturn(true);
        $repository = $this->createRepositoryMock(self::validRecord(['hash' => '$argon2id$v=19$legacy']));
        $repository->method('updateHash')->willThrowException(new \RuntimeException('db down'));

        $subject = new ApiKeyAuthentication(new TokenService(null, $legacyHasher), $repository);

        self::assertTrue($subject->withIdentifier('baz')->fromHeader('foo')->isAuthenticated());
    }

    /**
     * @param array<string, mixed> $record
     */
    private function createRepositoryMock(array $record): TokenRepository|MockObject
    {
        $repository = $this->getMockBuilder(TokenRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findOneRecordByIdentifier', 'updateHash'])
            ->getMock();
        $repository->method('findOneRecordByIdentifier')->willReturn($record);

        return $repository;
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private static function validRecord(array $overrides = []): array
    {
        return array_replace([
            'uid' => 1,
            'name' => 'bar',
            'identifier' => 'baz',
            'hash' => 'bar',
            'valid_until' => time() + 86400,
            'hidden' => 0,
            'deleted' => 0,
        ], $overrides);
    }
}
