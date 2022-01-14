<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Context;
use TYPO3\CMS\Core\Context\Exception\AspectPropertyNotFoundException;

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
