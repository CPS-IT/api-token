<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Request\Validation;

use Fr\ApiToken\Context\AuthenticatedAspect;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Validates for any authentication
 * Authentication has to be done in the middleware stack
 * before thish validator is called.
 */
class AuthenticatedValidator implements RequestValidatorInterface
{
    /**
     * @var Context
     */
    protected $context;

    public const ASPECTS_TO_CHECK = [
        'backend.user' => 'isLoggedIn',
        AuthenticatedAspect::ASPECT_NAME => AuthenticatedAspect::AUTHENTICATED
    ];

    public function __construct(Context $context = null)
    {
       $this->context = $context ?? GeneralUtility::makeInstance(Context::class);
    }

    public function validate(ServerRequestInterface $request): bool
    {
        foreach (static::ASPECTS_TO_CHECK as $aspect => $property)
        {
            if(!$this->context->hasAspect($aspect)) {
                continue;
            }
            if ($this->context->getPropertyFromAspect($aspect , $property, false)) {
                return true;
            }
        }

        return false;
    }


}