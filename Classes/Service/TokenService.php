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
use TYPO3\CMS\Core\Crypto\PasswordHashing\InvalidPasswordHashException;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Secrets are 122 bit random values (UUID v4), not user chosen passwords.
 * A slow password hash (argon2, bcrypt) adds nothing against brute force
 * at that entropy, but makes every verification expensive. Secrets are
 * therefore stored as a plain SHA-256 digest ("sha256:<hex>").
 *
 * Hashes in the previous format (TYPO3 password hashes) are still
 * verified; needsRehash() reports them so callers can upgrade them after
 * a successful verification.
 */
class TokenService implements TokenServiceInterface, HashUpgradeAwareInterface
{
    public const HASH_PREFIX = 'sha256:';

    /**
     * @var RandomInterface
     */
    protected $random;

    /**
     * Only used to verify legacy hashes. When null, the matching TYPO3
     * password hash implementation is resolved from the hash itself.
     *
     * @var PasswordHashInterface|null
     */
    protected $hashInstance;

    public function __construct(?RandomInterface $random = null, ?PasswordHashInterface $hashInstance = null)
    {
        $this->random = $random ?? GeneralUtility::makeInstance(Random::class);
        $this->hashInstance = $hashInstance;
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
    public function generateIdentifier(int $lenght = 13): string
    {
        return $this->random->generateRandomHexString($lenght);
    }

    /**
     * Returns the storable hash of a secret ("sha256:<hex>")
     *
     * @param string $secret
     * @return string
     */
    #[\Override]
    public function hash(string $secret): string
    {
        return self::HASH_PREFIX . hash('sha256', $secret);
    }

    /**
     * Tells if the plain text secret matches the stored hash.
     * Accepts current (sha256) and legacy (TYPO3 password hash) formats.
     *
     * @param string $secret The plain text secret to check for
     * @param string $saltedHash The stored hash to check against
     * @return bool
     */
    #[\Override]
    public function check(string $secret, string $saltedHash): bool
    {
        if ($secret === '' || $saltedHash === '') {
            return false;
        }

        if (str_starts_with($saltedHash, self::HASH_PREFIX)) {
            return hash_equals($saltedHash, $this->hash($secret));
        }

        return $this->checkLegacyHash($secret, $saltedHash);
    }

    #[\Override]
    public function needsRehash(string $hash): bool
    {
        return !str_starts_with($hash, self::HASH_PREFIX);
    }

    protected function checkLegacyHash(string $secret, string $legacyHash): bool
    {
        if ($this->hashInstance instanceof PasswordHashInterface) {
            return $this->hashInstance->checkPassword($secret, $legacyHash);
        }

        try {
            return GeneralUtility::makeInstance(PasswordHashFactory::class)
                ->get($legacyHash, 'BE')
                ->checkPassword($secret, $legacyHash);
        } catch (InvalidPasswordHashException) {
            return false;
        }
    }
}
