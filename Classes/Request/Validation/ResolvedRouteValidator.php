<?php
namespace Fr\ApiToken\Request\Validation;
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

use Fr\ApiToken\Routing\ApiPageArguments;
use Psr\Http\Message\ServerRequestInterface;

class ResolvedRouteValidator implements RequestValidatorInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function validate(ServerRequestInterface $request): bool
    {
        $pageArguments = $request->getAttribute('routing');
        return $pageArguments instanceof ApiPageArguments;
    }
}
