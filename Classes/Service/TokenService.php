<?php
declare(strict_types=1);

namespace Fr\ApiToken\Service;

use Exception;
use Fr\ApiToken\Crypto\Random;
use Fr\ApiToken\Crypto\RandomInterface;
use Ramsey\Uuid\Rfc4122\UuidV4;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Dirk Wenzel
 *  All rights reserved
 *
 * The GNU General Public License can be found at
 * http://www.gnu.org/copyleft/gpl.html.
 * A copy is found in the text file GPL.txt and important notices to the license
 * from the author is found in LICENSE.txt distributed with these scripts.
 * This script is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class TokenService
 */
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
     * @throws Exception
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