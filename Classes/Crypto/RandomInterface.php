<?php

/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
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
