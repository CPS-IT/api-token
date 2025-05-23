<?php

/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Authentication;

interface AuthenticationInterface
{
    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * Until when the authentication is valid
     *
     * @return \DateTimeImmutable
     */
    public function validUntil(): \DateTimeImmutable;
}
