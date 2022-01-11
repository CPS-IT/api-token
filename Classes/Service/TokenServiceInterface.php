<?php
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

namespace Fr\ApiToken\Service;


use Exception;

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
     * @throws Exception
     */
    public function generateIdentifier(int $lenght = 13);

    /**
     * Returns a salted hashed key
     * for the secret
     *
     * @param string $secret
     * @return mixed
     */
    public function hash(string $secret);

    /**
     * Tells if the plain text secret is correct by comparing it
     * with the salted hash
     *
     * @param string $secret The plain text secret to check for
     * @param string $saltedHash The salted hash to check agains
     * @return bool
     */
    public function check(string $secret, $saltedHash);
}