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

namespace CPSIT\ApiToken\Tests\Unit\Service;

use CPSIT\ApiToken\Crypto\RandomInterface;
use CPSIT\ApiToken\Service\TokenService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(TokenService::class)]
class TokenServiceTest extends UnitTestCase
{
    private TokenService $subject;
    private RandomInterface|MockObject $randomMock;
    private PasswordHashInterface|MockObject $passwordHasherMock;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->randomMock = $this->createMock(RandomInterface::class);
        $this->passwordHasherMock = $this->createMock(PasswordHashInterface::class);

        $this->subject = new TokenService($this->randomMock, $this->passwordHasherMock);
    }

    #[Test]
    public function generateSecretReturnsUuidString(): void
    {
        // Mock 16 bytes with proper UUID v4 structure
        $mockBytes = hex2bin('550e8400e29b41d4a716446655440000');
        $this->randomMock
            ->expects(self::once())
            ->method('generateRandomBytes')
            ->with(16)
            ->willReturn($mockBytes);

        $secret = $this->subject->generateSecret();

        self::assertIsString($secret);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $secret);
    }

    #[Test]
    public function generateIdentifierUsesRandomService(): void
    {
        $expectedIdentifier = 'abc123def456';
        $expectedLength = 13;

        $this->randomMock
            ->expects(self::once())
            ->method('generateRandomHexString')
            ->with($expectedLength)
            ->willReturn($expectedIdentifier);

        $result = $this->subject->generateIdentifier($expectedLength);

        self::assertSame($expectedIdentifier, $result);
    }

    #[Test]
    public function generateIdentifierUsesDefaultLengthWhenNotProvided(): void
    {
        $expectedIdentifier = 'defaultlength';

        $this->randomMock
            ->expects(self::once())
            ->method('generateRandomHexString')
            ->with(13)
            ->willReturn($expectedIdentifier);

        $result = $this->subject->generateIdentifier();

        self::assertSame($expectedIdentifier, $result);
    }

    #[Test]
    public function hashUsesPasswordHasher(): void
    {
        $plainTextSecret = 'mySecret123';
        $expectedHash = '$2y$10$abcdefghijklmnopqrstuvwxyz123456789';

        $this->passwordHasherMock
            ->expects(self::once())
            ->method('getHashedPassword')
            ->with($plainTextSecret)
            ->willReturn($expectedHash);

        $result = $this->subject->hash($plainTextSecret);

        self::assertSame($expectedHash, $result);
    }

    #[Test]
    public function checkUsesPasswordHasher(): void
    {
        $plainTextSecret = 'mySecret123';
        $hashedSecret = '$2y$10$abcdefghijklmnopqrstuvwxyz123456789';

        $this->passwordHasherMock
            ->expects(self::once())
            ->method('checkPassword')
            ->with($plainTextSecret, $hashedSecret)
            ->willReturn(true);

        $result = $this->subject->check($plainTextSecret, $hashedSecret);

        self::assertTrue($result);
    }

    #[Test]
    public function checkReturnsFalseForInvalidPassword(): void
    {
        $plainTextSecret = 'wrongSecret';
        $hashedSecret = '$2y$10$abcdefghijklmnopqrstuvwxyz123456789';

        $this->passwordHasherMock
            ->expects(self::once())
            ->method('checkPassword')
            ->with($plainTextSecret, $hashedSecret)
            ->willReturn(false);

        $result = $this->subject->check($plainTextSecret, $hashedSecret);

        self::assertFalse($result);
    }
}
