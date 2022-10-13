<?php
declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Service;


use CPSIT\ApiToken\Domain\Model\Token;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TokenBuildService
{
    private const MAGIG_TIME_STRING = '+1 year';

    /**
     * @param string $name
     * @param string $description
     * @param string $identifier
     * @param string $hash
     * @return Token
     * @throws \Exception
     */
    public function buildInitialToken(
        string $name,
        string $description,
        string $identifier,
        string $hash
    ): Token
    {
        $duration = $this->getDuration();
        return GeneralUtility::makeInstance(Token::class)
            ->setName($name)
            ->setDescription($description)
            ->setIdentifier($identifier)
            ->setHash($hash)
            ->setValidUntil($duration);
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    private function getDuration(): \DateTime
    {
        return new \DateTime(self::MAGIG_TIME_STRING, new \DateTimeZone(date_default_timezone_get()));
    }
}
