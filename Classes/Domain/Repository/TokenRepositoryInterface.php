<?php
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

namespace Fr\ApiToken\Domain\Repository;

use Fr\ApiToken\Domain\Model\Token;

interface TokenRepositoryInterface
{

    public const TABLE_NAME = Token::TABLE_NAME, IDENTIFIER_COLUMN = Token::IDENTIFIER;

    /**
     * @param string $identifier
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findOneByIdentifier(string $identifier): array;
}