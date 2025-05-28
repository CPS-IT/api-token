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

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Routing\RouteResultInterface;

class ResolvedRouteValidator implements RequestValidatorInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return bool
     */
    #[\Override]
    public function validate(ServerRequestInterface $request): bool
    {
        $pageArguments = $request->getAttribute('routing');
        return $pageArguments instanceof RouteResultInterface;
    }
}
