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

use CPSIT\ApiToken\Domain\Model\Token;
use CPSIT\ApiToken\Service\TokenBuildService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(TokenBuildService::class)]
class TokenBuildServiceTest extends UnitTestCase
{
    private TokenBuildService $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TokenBuildService();
    }

    #[Test]
    public function buildInitialTokenReturnsTokenWithCorrectValues(): void
    {
        $name = 'Test Token';
        $description = 'Test Description';
        $identifier = 'test-identifier';
        $hash = 'test-hash';

        $token = $this->subject->buildInitialToken($name, $description, $identifier, $hash);

        self::assertInstanceOf(Token::class, $token);
        self::assertSame($name, $token->getName());
        self::assertSame($description, $token->getDescription());
        self::assertSame($identifier, $token->getIdentifier());
        self::assertSame($hash, $token->getHash());
    }

    #[Test]
    public function buildInitialTokenSetsValidUntilToOneYearFromNow(): void
    {
        $token = $this->subject->buildInitialToken('name', 'desc', 'id', 'hash');

        $validUntil = $token->getValidUntil();
        $oneYearFromNow = new \DateTime('+1 year');

        self::assertInstanceOf(\DateTime::class, $validUntil);
        // Allow for small time differences (within 1 minute)
        self::assertLessThan(60, abs($validUntil->getTimestamp() - $oneYearFromNow->getTimestamp()));
    }
}
