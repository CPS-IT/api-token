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

use TYPO3\CMS\Core\Crypto\Random as CoreRandom;

class Random implements RandomInterface
{
    private readonly CoreRandom $coreRandom;

    public function __construct()
    {
        $this->coreRandom = new CoreRandom();
    }

    #[\Override]
    public function generateRandomBytes(int $length): string
    {
        return $this->coreRandom->generateRandomBytes($length);
    }

    #[\Override]
    public function generateRandomInteger(int $min, int $max): int
    {
        return $this->coreRandom->generateRandomInteger($min, $max);
    }

    #[\Override]
    public function generateRandomHexString(int $length): string
    {
        return $this->coreRandom->generateRandomHexString($length);
    }
}
