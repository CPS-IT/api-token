<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Request\Validation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Validates API Requests
 */
interface RequestValidatorInterface
{
    /**
     * Returns true if request is
     * valid in the scope of this validator
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function validate(ServerRequestInterface $request): bool;
}
