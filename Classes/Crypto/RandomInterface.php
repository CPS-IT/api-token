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

namespace CPSIT\ApiToken\Crypto;

interface RandomInterface
{
    /**
     * Generates cryptographic secure pseudo-random bytes
     *
     * @param int $length
     * @return string
     */
    public function generateRandomBytes(int $length): string;

    /**
     * Generates cryptographic secure pseudo-random integers
     *
     * @param int $min
     * @param int $max
     * @return int
     */
    public function generateRandomInteger(int $min, int $max): int;

    /**
     * Generates cryptographic secure pseudo-random hex string
     *
     * @param int $length
     * @return string
     */
    public function generateRandomHexString(int $length): string;
}
