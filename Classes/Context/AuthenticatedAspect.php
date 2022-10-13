<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Context;

use DateTimeImmutable;
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
        self::METHOD
    ];
    /**
     * @var bool
     */
    static protected $authenticated = false;
    /**
     * @var DateTimeImmutable|null
     */
    static protected $validUntil;
    /**
     * Authentication method
     *
     * @var string
     */
    static protected $method = self::METHOD_NONE;

    public function __construct(AuthenticationInterface $authentication = null)
    {
        if (null !== $authentication) {
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
