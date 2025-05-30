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

namespace CPSIT\ApiToken\Context;

use CPSIT\ApiToken\Authentication\AuthenticationInterface;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Aspect contains information about the authentication status of
 * a request. It should be used in context of REST API calls
 * only.
 */
class AuthenticatedAspect implements AspectInterface, SingletonInterface
{
    use AspectPropertyAccessTrait;

    public const ASPECT_NAME = 'RestApiAuthenticated';
    public const MESSAGE_INVALID_PROPERTY = 'Invalid property %s in class %s';
    public const METHOD_NONE = 'none';

    /**
     * Property names
     */
    public const AUTHENTICATED = 'authenticated';

    /**
     * end of authentication status
     */
    public const VALID_UNTIL = 'validUntil';

    /**
     * Authentication method
     */
    public const METHOD = 'method';

    /**
     * valid properties for get method
     */
    public const PROPERTIES = [
        self::AUTHENTICATED,
        self::VALID_UNTIL,
        self::METHOD,
    ];
    /**
     * @var bool
     */
    protected static $authenticated = false;
    /**
     * @var \DateTimeImmutable|null
     */
    protected static $validUntil;
    /**
     * Authentication method
     *
     * @var string
     */
    protected static $method = self::METHOD_NONE;

    public function __construct(AuthenticationInterface $authentication = null)
    {
        if ($authentication !== null) {
            self::$authenticated = $authentication->isAuthenticated();
            self::$method = $authentication->getMethod();
            self::$validUntil = $authentication->validUntil();
        }
    }

    public function isAuthenticated(): bool
    {
        return self::$authenticated;
    }

}
