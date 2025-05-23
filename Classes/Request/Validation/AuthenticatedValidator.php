<?php

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

use CPSIT\ApiToken\Context\AuthenticatedAspect;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validates for any authentication
 *
 * It has to be done beforehand.
 */
class AuthenticatedValidator implements RequestValidatorInterface
{
    /**
     * @var Context
     */
    protected $context;

    public const ASPECTS_TO_CHECK = [
        'backend.user' => 'isLoggedIn',
        AuthenticatedAspect::ASPECT_NAME => AuthenticatedAspect::AUTHENTICATED,
    ];

    public function __construct(Context $context = null)
    {
        $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
    }

    #[\Override]
    public function validate(ServerRequestInterface $request): bool
    {
        foreach (static::ASPECTS_TO_CHECK as $aspect => $property) {
            if (!$this->context->hasAspect($aspect)) {
                continue;
            }
            if ($this->context->getPropertyFromAspect($aspect, $property, false)) {
                return true;
            }
        }

        return false;
    }
}
