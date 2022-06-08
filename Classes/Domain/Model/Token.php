<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;

/**
 * Class Token
 */
class Token extends AbstractDomainObject
{
    public const TABLE_NAME = 'tx_apitoken_domain_model_token', IDENTIFIER = 'identifier';

    /**
     * @var string
     */
    protected string $name = '';

    /**
     * @var string
     */
    protected string $identifier = '';

    /**
     * @var string
     */
    protected string $hash = '';

    /**
     * @var string
     */
    protected string $description = '';

    /**
     * Must be nullable for data mapper
     * @var ?DateTime
     */
    protected ?DateTime $validUntil = null;

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
