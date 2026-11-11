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

use CPSIT\ApiToken\Domain\Model\Token;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TokenBuildService
{
    public const string DEFAULT_VALIDITY = '+1 year';

    /**
     * @param string $name
     * @param string $description
     * @param string $identifier
     * @param string $hash
     * @param \DateTime|null $validUntil defaults to DEFAULT_VALIDITY from now
     * @return Token
     * @throws \Exception
     */
    public function buildInitialToken(
        string $name,
        string $description,
        string $identifier,
        string $hash,
        ?\DateTime $validUntil = null
    ): Token {
        return GeneralUtility::makeInstance(Token::class)
            ->setName($name)
            ->setDescription($description)
            ->setIdentifier($identifier)
            ->setHash($hash)
            ->setValidUntil($validUntil ?? $this->parseValidUntil(self::DEFAULT_VALIDITY));
    }

    /**
     * Parses an expiry given as interval or date ("+6 months", "2027-12-31",
     * ISO 8601) in the server's default time zone.
     *
     * @throws \InvalidArgumentException for unparsable values and dates that are not in the future
     */
    public function parseValidUntil(string $expires): \DateTime
    {
        try {
            $validUntil = new \DateTime($expires, new \DateTimeZone(date_default_timezone_get()));
        } catch (\Exception $exception) {
            throw new \InvalidArgumentException(
                sprintf('Invalid expiration "%s". Use formats like "+1 year", "2027-12-31" or ISO 8601.', $expires),
                1762850001,
                $exception
            );
        }

        if ($validUntil->getTimestamp() <= time()) {
            throw new \InvalidArgumentException(
                sprintf('Expiration "%s" is not in the future.', $expires),
                1762850002
            );
        }

        return $validUntil;
    }
}
