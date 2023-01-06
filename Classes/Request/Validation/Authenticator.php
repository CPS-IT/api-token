<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Request\Validation;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

interface Authenticator
{
    public static function isNotAuthenticated(ServerRequestInterface $request): bool;
    public static function returnErrorResponse(): JsonResponse;
}