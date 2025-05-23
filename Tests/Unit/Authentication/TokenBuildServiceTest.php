<?php

declare(strict_types=1);

/*
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Tests\Unit\Authentication;

use CPSIT\ApiToken\Service\TokenBuildService;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

class TokenBuildServiceTest extends UnitTestCase
{
    protected TokenBuildService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TokenBuildService();
    }
}
