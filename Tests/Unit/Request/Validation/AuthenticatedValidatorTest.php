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

namespace CPSIT\ApiToken\Tests\Unit\Request\Validation;

use CPSIT\ApiToken\Context\AuthenticatedAspect;
use CPSIT\ApiToken\Request\Validation\AuthenticatedValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(AuthenticatedValidator::class)]
final class AuthenticatedValidatorTest extends UnitTestCase
{
    #[Test]
    public function loggedInBackendUserIsIgnoredWhenNotAllowed(): void
    {
        $subject = new AuthenticatedValidator($this->context(backendUser: true, apiToken: false), false);

        self::assertFalse($subject->validate(new ServerRequest()));
    }

    #[Test]
    public function loggedInBackendUserIsAcceptedWhenAllowed(): void
    {
        $subject = new AuthenticatedValidator($this->context(backendUser: true, apiToken: false), true);

        self::assertTrue($subject->validate(new ServerRequest()));
    }

    #[Test]
    public function validApiTokenIsAcceptedRegardlessOfBackendSetting(): void
    {
        $subject = new AuthenticatedValidator($this->context(backendUser: false, apiToken: true), false);

        self::assertTrue($subject->validate(new ServerRequest()));
    }

    #[Test]
    public function nothingAuthenticatedIsRejected(): void
    {
        $subject = new AuthenticatedValidator($this->context(backendUser: false, apiToken: false), true);

        self::assertFalse($subject->validate(new ServerRequest()));
    }

    /**
     * The "setting not configured at all" case needs the PackageManager
     * (TYPO3 syncs ext_conf_template.txt defaults); it is covered by
     * TokenRevocationTest::backendUserWithoutTokenIsRejectedByDefault.
     */
    #[Test]
    public function disabledSettingDisallowsBackendUsers(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['api_token'] = [
            AuthenticatedValidator::SETTING_ALLOW_BACKEND_USER => '0',
        ];
        $subject = new AuthenticatedValidator($this->context(backendUser: true, apiToken: false));

        self::assertFalse($subject->validate(new ServerRequest()));
    }

    #[Test]
    public function enabledSettingAllowsBackendUsers(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['api_token'] = [
            AuthenticatedValidator::SETTING_ALLOW_BACKEND_USER => '1',
        ];
        $subject = new AuthenticatedValidator($this->context(backendUser: true, apiToken: false));

        self::assertTrue($subject->validate(new ServerRequest()));
    }

    #[\Override]
    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['api_token']);
        parent::tearDown();
    }

    private function context(bool $backendUser, bool $apiToken): Context|MockObject
    {
        $context = $this->createMock(Context::class);
        $context->method('hasAspect')->willReturn(true);
        $context->method('getPropertyFromAspect')->willReturnMap([
            [AuthenticatedValidator::BACKEND_USER_ASPECT, 'isLoggedIn', false, $backendUser],
            [AuthenticatedAspect::ASPECT_NAME, AuthenticatedAspect::AUTHENTICATED, false, $apiToken],
        ]);

        return $context;
    }
}
