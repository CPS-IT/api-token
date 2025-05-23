<?php

declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace CPSIT\ApiToken\Service;

use CPSIT\ApiToken\Crypto\Random;
use CPSIT\ApiToken\Crypto\RandomInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
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
    public function generateSecret(): string
    {
        $uuid = UuidV4::fromBytes(
            $this->random->generateRandomBytes(16)
        );

        return $uuid->toString();
    }

    /**
     * @param int $lenght
     * @return string
     * @throws \Exception
     */
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
    public function check(string $secret, $saltedHash): bool
    {
        return $this->hashInstance->checkPassword($secret, $saltedHash);
    }
}
