<?php
declare(strict_types=1);
namespace Fr\ApiToken\Request\Validation;

use Psr\Http\Message\ServerRequestInterface;

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
 * RequestValidatorInterface
 *
 * Validates API Requests
 */
interface RequestValidatorInterface
{
    /**
     * Returns true if request is
     * valid in the scope of this validator
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    public function validate(ServerRequestInterface $request): bool;
}