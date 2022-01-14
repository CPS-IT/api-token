<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace Fr\ApiToken\Tests\Unit\Authentication;

use Fr\ApiToken\Service\TokenBuildService;
use Fr\IkiProjectImport\Domain\Model\Job;
use Nimut\TestingFramework\TestCase\UnitTestCase;

class TokenBuildServiceTest extends UnitTestCase
{
    protected TokenBuildService $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new TokenBuildService();
    }

}