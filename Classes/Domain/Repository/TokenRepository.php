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

namespace CPSIT\ApiToken\Domain\Repository;

use CPSIT\ApiToken\Domain\Model\Token;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class TokenRepository implements TokenRepositoryInterface
{
    public function __construct(protected ?PersistenceManagerInterface $persistenceManager) {}

    /**
     * @inheritDoc
     */
    #[\Override]
    public function findOneRecordByIdentifier(string $identifier): array
    {
        $queryBuilder = $this->getQueryBuilder();
        $result = $queryBuilder->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    self::IDENTIFIER_COLUMN,
                    $queryBuilder->createNamedParameter($identifier)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        return $result ?: [];
    }

    public function findAllRecords(): array
    {
        $records = [];
        $result = $this->getQueryBuilder()
            ->select('crdate', 'valid_until', 'uid', 'name', 'identifier', 'description', 'hidden')
            ->from(self::TABLE_NAME)
            ->executeQuery();

        while ($row = $result->fetchAssociative()) {
            // Do something with that single row
            $records[] = $row;
        }

        return array_map(
            static function ($tokenRecord) {
                $tokenRecord['is_expired'] = ($tokenRecord['valid_until'] < (new \DateTime('now'))->getTimestamp());
                $tokenRecord['is_hidden'] = $tokenRecord['hidden'] === 1;
                return $tokenRecord;
            },
            $records
        );
    }

    /**
     * @param Token $token
     */
    public function persistNewToken(Token $token): void
    {
        $this->persistenceManager->add($token);
        $this->persistenceManager->persistAll();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(self::TABLE_NAME);
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return $queryBuilder;
    }
}
