<?php

namespace Fr\ApiToken\Context;

use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2020 Dirk Wenzel <wenzel@cps-it.de>
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
trait AspectPropertyAccessTrait
{
    /**
     * @inheritDoc
     */
    public function get(string $name)
    {
        if (!in_array($name, static::PROPERTIES, true)) {

            $message = sprintf(
                'Invalid property %s in class %s',
                $name,
                get_class($this)
            );

            throw new AspectPropertyNotFoundException(
                $message,
                1585257543
            );
        }

        return self::${$name};
    }
}
