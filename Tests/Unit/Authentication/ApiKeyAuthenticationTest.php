<?php
declare(strict_types=1);

/*
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Tests\Unit\Authentication;

use DateTimeImmutable;
use DateTimeZone;
use CPSIT\ApiToken\Authentication\ApiKeyAuthentication;
use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Exception\InvalidHttpMethodException;
use CPSIT\ApiToken\Service\TokenServiceInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ApiKeyAuthenticationTest extends UnitTestCase
{
    /**
     * @var ApiKeyAuthentication
     */
    protected $subject;

    /**
     * @var TokenRepository|MockObject
     */
    protected $tokenRepository;

    /**
     * @var TokenServiceInterface
     */
    protected $tokenService;

    public function setUp(): void
    {
        $this->tokenService = $this->getMockForAbstractClass(TokenServiceInterface::class);
        $this->tokenRepository = $this->getMockBuilder(TokenRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                'findOneRecordByIdentifier'
                ]
            )
            ->getMock();
        $this->subject = new ApiKeyAuthentication($this->tokenService, $this->tokenRepository);
    }

    public function testIsAuthenticatedInitiallyReturnsFalse(): void
    {
        $this->assertFalse($this->subject->isAuthenticated());
    }

    public function testValidateHeaderNameReturnsFalseForInvalidName(): void
    {
        $name = 'invalidFooName';
        $this->assertFalse($this->subject->validateHeaderName($name));
    }

    public function testValidateHeaderNameReturnsTrueForValidName(): void
    {
        $this->assertTrue($this->subject->validateHeaderName(ApiKeyAuthentication::HEADER_NAME_AUTHORIZATION));
    }

    public function testValidUntilInitiallyReturnsPastDateTime(): void
    {
        $now = new DateTimeImmutable('now');
        $validUntil = $this->subject->validUntil();

        $this->assertTrue($now > $validUntil);
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForInvalidName(): void
    {
        $invalidHeaderName = 'booFar';
        $value = 'no';

        $invalidAuthentication = $this->subject->fromHeader($invalidHeaderName, $value);
        $this->assertFalse($invalidAuthentication->isAuthenticated());
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyValue(): void
    {
        $invalidHeaderName = '';
        $secret = 'no';

        $invalidAuthentication = $this->subject->fromHeader($secret, $invalidHeaderName);
        $this->assertFalse($invalidAuthentication->isAuthenticated());
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptResultFromRepository(): void
    {
        $secret = 'foo';
        $identifier = 'baz';
        $emptyResultFromRepository = [];

        $this->tokenRepository->expects($this->once())
            ->method('findOneRecordByIdentifier')
            ->with($identifier)
            ->willReturn($emptyResultFromRepository);

        $this->subject->withIdentifier($identifier);

        $invalidAuthentication = $this->subject->fromHeader($secret);
        $this->assertFalse($invalidAuthentication->isAuthenticated());
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyIdentifier(): void
    {
        $secret = 'foo';
        $authentication = $this->subject->fromHeader($secret);
        $this->assertFalse($authentication->isAuthenticated());
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsAuthenticatedInstanceForValidHeader(): void
    {
        $validSecret = 'foo';
        $hashOfSecret = 'bar';
        $identifier = 'baz';

        $timeZone = new DateTimeZone(date_default_timezone_get());
        $validUntil = new DateTimeImmutable('tomorrow', $timeZone);
        $validRecord = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $hashOfSecret,
            'valid_until' => $validUntil->format('U')
        ];

        $this->tokenRepository->expects($this->once())
            ->method('findOneRecordByIdentifier')
            ->with($identifier)
            ->willReturn($validRecord);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->expects($this->once())
            ->method('check')
            ->with($validSecret, $hashOfSecret)
            ->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);
        $this->assertTrue($authentication->isAuthenticated());

        $this->assertEquals($validUntil, $authentication->validUntil());
    }

    public function testGetMethodInitiallyReturnsGet(): void
    {
        $this->assertSame(RestApiInterface::METHOD_GET, $this->subject->getMethod());
    }

    public function validMethodsDataProvider(): array
    {
        $data = [];
        foreach (RestApiInterface::VALID_METHODS as $method) {
            $data[$method] = [$method];
        }

        return $data;
    }

    /**
     * @param $method
     * @dataProvider validMethodsDataProvider
     * @throws InvalidHttpMethodException
     */
    public function testWithMethodSetsValidMethods($method): void
    {
        $this->subject->withMethod($method);
        $this->assertSame($method, $this->subject->getMethod());
    }

    /**
     * @throws InvalidHttpMethodException
     */
    public function testWithMethodThrowsExceptionForInvalidMethod(): void
    {
        $invalidMethod = 'foo';
        $this->expectException(InvalidHttpMethodException::class);
        $this->expectExceptionCode(1585497878);
        $this->subject->withMethod($invalidMethod);
    }

    /**
     * @throws \Exception
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptySecret(): void
    {
        $secret = '';
        $result = $this->subject->fromHeader($secret);
        $this->assertFalse($result->isAuthenticated());
    }

    /**
     * @throws \Exception
     * @noinspection DuplicatedCode
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForExpiredValidUntil(): void
    {
        $validSecret = 'foo';
        $hashOfSecret = 'bar';
        $identifier = 'baz';

        $timeZone = new DateTimeZone(date_default_timezone_get());
        $expiredDate = new DateTimeImmutable('yesterday', $timeZone);
        $expiredToken = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $hashOfSecret,
            'valid_until' => $expiredDate->format('U')
        ];

        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn($expiredToken);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->method('check')->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);

        $this->assertFalse($authentication->isAuthenticated());
    }

    /**
     * @throws \Exception
     * @noinspection DuplicatedCode
     */
    public function testFromHeaderReturnsNonAuthenticatedInstanceForEmptyHash(): void
    {
        $validSecret = 'foo';
        $emptyHashOfSecret = '';
        $identifier = 'baz';

        $timeZone = new DateTimeZone(date_default_timezone_get());
        $validDate = new DateTimeImmutable('tomorrow', $timeZone);
        $expiredToken = [
            'uid' => 1,
            'name' => 'bar',
            'identifier' => $identifier,
            'hash' => $emptyHashOfSecret,
            'valid_until' => $validDate->format('U')
        ];

        $this->tokenRepository->method('findOneRecordByIdentifier')->willReturn($expiredToken);
        $this->subject->withIdentifier($identifier);

        $this->tokenService->method('check')->willReturn(true);

        $authentication = $this->subject->fromHeader($validSecret);

        $this->assertFalse($authentication->isAuthenticated());
    }
}
