<?php

/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Request\Validation;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\RouteResultInterface;

class ResolvedRouteValidator implements RequestValidatorInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function validate(ServerRequestInterface $request): bool
    {
        $pageArguments = $request->getAttribute('routing');
        return $pageArguments instanceof RouteResultInterface;
    }
}
