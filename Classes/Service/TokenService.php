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

use CPSIT\ApiToken\Crypto\Random;
use CPSIT\ApiToken\Crypto\RandomInterface;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TokenService implements TokenServiceInterface
{
    /**
     * @var
     */
    protected $random;

    /**
     * @var PasswordHashInterface
     */
    protected $hashInstance;

    public function __construct(RandomInterface $random = null, PasswordHashInterface $hashInstance = null)
    {
        $this->random = $random ?? GeneralUtility::makeInstance(Random::class);
        $this->hashInstance = $hashInstance ?? GeneralUtility::makeInstance(PasswordHashFactory::class)
                ->getDefaultHashInstance('BE');
    }

    /**
     * Generates a secret using
     * random functions
     *
     * @return string
     */
    #[\Override]
    public function generateSecret(): string
    {
        $randomBytes = $this->random->generateRandomBytes(16);

        // Set version (4) and variant bits for UUID v4
        $randomBytes[6] = chr(ord($randomBytes[6]) & 0x0f | 0x40); // Version 4
        $randomBytes[8] = chr(ord($randomBytes[8]) & 0x3f | 0x80); // Variant bits

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr((string)$randomBytes, 0, 4)),
            bin2hex(substr((string)$randomBytes, 4, 2)),
            bin2hex(substr((string)$randomBytes, 6, 2)),
            bin2hex(substr((string)$randomBytes, 8, 2)),
            bin2hex(substr((string)$randomBytes, 10, 6))
        );
    }

    /**
     * @param int $lenght
     * @return string
     * @throws \Exception
     */
    #[\Override]
    public function generateIdentifier(int $lenght = 13)
    {
        return $this->random->generateRandomHexString($lenght);
    }

    /**
     * Returns a salted hashed key
     * for the secret
     *
     * @param string $secret
     * @return mixed
     */
    #[\Override]
    public function hash(string $secret)
    {
        return $this->hashInstance->getHashedPassword($secret);
    }

    /**
     * Tells if the plain text secret is correct by comparing it
     * with the salted hash
     *
     * @param string $secret The plain text secret to check for
     * @param string $saltedHash The salted hash to check agains
     * @return bool
     */
    #[\Override]
    public function check(string $secret, $saltedHash): bool
    {
        return $this->hashInstance->checkPassword($secret, $saltedHash);
    }
}
