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

namespace CPSIT\ApiToken\Tests\Unit\Configuration;

use CPSIT\ApiToken\Configuration\Extension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Extension::class)]
class ExtensionTest extends UnitTestCase
{
    #[Test]
    public function classDefinesExpectedConstants(): void
    {
        self::assertSame('api_token', Extension::KEY);
        self::assertSame('ApiToken', Extension::NAME);
        self::assertSame('CPSIT', Extension::VENDOR_NAME);
        self::assertSame('api_token', Extension::EXTENSION_KEY);
    }

    #[Test]
    public function tokenSvgConstantReturnsTableName(): void
    {
        self::assertSame('tx_apitoken_domain_model_token', Extension::TOKEN_SVG);
    }
}
