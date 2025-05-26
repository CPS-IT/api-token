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

namespace CPSIT\ApiToken\Service;

/**
 * Class TokenService
 */
interface TokenServiceInterface
{
    /**
     * Generates a secret using
     * random functions
     *
     * @return string
     */
    public function generateSecret(): string;

    /**
     * @param int $lenght
     * @return string
     * @throws \Exception
     */
    public function generateIdentifier(int $lenght = 13): string;

    /**
     * Returns a salted hashed key
     * for the secret
     *
     * @param string $secret
     * @return string
     */
    public function hash(string $secret): string;

    /**
     * Tells if the plain text secret is correct by comparing it
     * with the salted hash
     *
     * @param string $secret The plain text secret to check for
     * @param string $saltedHash The salted hash to check against
     * @return bool
     */
    public function check(string $secret, string $saltedHash): bool;
}
