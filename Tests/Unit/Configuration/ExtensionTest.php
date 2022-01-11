<?php
declare(strict_types=1);
namespace Fr\ApiToken\Tests\Unit\Configuration;

use Fr\ApiToken\Configuration\Extension;

/**
 * This file is part of the "Fr/ApiToken" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
class ExtensionTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->setBackupGlobals(true);
    }

    public function testRegisterRoutingComponentsAddsEnhancers(): void
    {
        $this->unsetEnhancer();
        self::assertEmpty($GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']);

//todo...        Extension::registerRoutingComponents();
//        foreach (Extension::ENHANCERS_TO_REGISTER as $key => $className) {
//            self::assertArrayHasKey(
//                $key,
//                $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']
//            );
//
//            self::assertSame(
//                $className,
//                $GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers'][$key]
//            );
//        }
    }

    protected function unsetEnhancer(): void
    {
        unset ($GLOBALS['TYPO3_CONF_VARS']['SYS']['routing']['enhancers']);
    }
}
