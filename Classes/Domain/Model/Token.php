<?php

namespace Fr\ApiToken\Domain\Model;

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

use DateTime;
use Fr\ApiToken\Configuration\SettingsInterface as SI;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Class Token
 */
class Token extends AbstractDomainObject
{
    public const TABLE_NAME = SI::TABLE_TOKEN;

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $identifier = '';

    /**
     * @var string
     */
    protected $hash = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var DateTime
     */
    protected $validUntil;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Token
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param string $identifier
     * @return self
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @param string $hash
     * @return Token
     */
    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getValidUntil(): ?DateTime
    {
        return $this->validUntil;
    }

    /**
     * @param DateTime $validUntil
     * @return Token
     */
    public function setValidUntil(DateTime $validUntil): self
    {
        $this->validUntil = $validUntil;
        return $this;
    }

}