<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Authentication;

use DateTimeImmutable;

interface AuthenticationInterface
{
    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @return string
     */
    public function getMethod():string;

    /**
     * Until when the authentication is valid
     *
     * @return DateTimeImmutable
     */
    public function validUntil(): DateTimeImmutable;
}