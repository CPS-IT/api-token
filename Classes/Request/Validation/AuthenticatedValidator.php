<?php
declare(strict_types=1);

namespace Fr\ApiToken\Request\Validation;

use Fr\ApiToken\Context\AuthenticatedAspect;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Dirk Wenzel
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

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