<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Domain\Repository;

use CPSIT\ApiToken\Domain\Model\Token;

interface TokenRepositoryInterface
{
    public const TABLE_NAME = Token::TABLE_NAME, IDENTIFIER_COLUMN = Token::IDENTIFIER;

    /**
     * @param string $identifier
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findOneRecordByIdentifier(string $identifier): array;
}
