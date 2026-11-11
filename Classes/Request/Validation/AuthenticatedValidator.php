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

namespace CPSIT\ApiToken\Request\Validation;

use CPSIT\ApiToken\Configuration\Extension;
use CPSIT\ApiToken\Context\AuthenticatedAspect;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validates for any authentication
 *
 * It has to be done beforehand.
 *
 * A logged-in backend user only counts as authenticated when the extension
 * setting "allowBackendUserAuthentication" is enabled.
 */
class AuthenticatedValidator implements RequestValidatorInterface
{
    public const BACKEND_USER_ASPECT = 'backend.user';
    public const SETTING_ALLOW_BACKEND_USER = 'allowBackendUserAuthentication';

    /**
     * @var Context
     */
    protected $context;

    protected bool $allowBackendUserAuthentication;

    public const ASPECTS_TO_CHECK = [
        self::BACKEND_USER_ASPECT => 'isLoggedIn',
        AuthenticatedAspect::ASPECT_NAME => AuthenticatedAspect::AUTHENTICATED,
    ];

    public function __construct(?Context $context = null, ?bool $allowBackendUserAuthentication = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
        $this->allowBackendUserAuthentication = $allowBackendUserAuthentication
            ?? self::isBackendUserAuthenticationEnabled();
    }

    #[\Override]
    public function validate(ServerRequestInterface $request): bool
    {
        foreach (static::ASPECTS_TO_CHECK as $aspect => $property) {
            if ($aspect === self::BACKEND_USER_ASPECT && !$this->allowBackendUserAuthentication) {
                continue;
            }
            if (!$this->context->hasAspect($aspect)) {
                continue;
            }
            if ($this->context->getPropertyFromAspect($aspect, $property, false)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fails closed: a missing or unsynchronised extension configuration
     * means backend users do NOT authenticate API requests.
     */
    protected static function isBackendUserAuthenticationEnabled(): bool
    {
        try {
            return (bool)GeneralUtility::makeInstance(ExtensionConfiguration::class)
                ->get(Extension::KEY, self::SETTING_ALLOW_BACKEND_USER);
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
            return false;
        }
    }
}
