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
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class TokenRepository implements TokenRepositoryInterface
{
    public function __construct(protected ?PersistenceManagerInterface $persistenceManager) {}

    /**
     * Returns the token record for authentication.
     *
     * Deleted and disabled ("hidden") tokens are never returned: disabling a
     * token in the backend is how it gets revoked. Expiry (valid_until) is
     * deliberately NOT filtered here but checked by the caller, because a
     * valid_until of 0 means "expired" for tokens, while TYPO3's
     * EndTimeRestriction would treat 0 as "never expires".
     *
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
                ),
                $queryBuilder->expr()->eq(
                    'hidden',
                    $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)
                )
            )
            ->executeQuery()
            ->fetchAssociative();

        return $result ?: [];
    }

    /**
     * Replaces the stored hash of a token, e.g. to upgrade a legacy hash format.
     */
    public function updateHash(int $uid, string $hash): void
    {
        GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(self::TABLE_NAME)
            ->update(self::TABLE_NAME, ['hash' => $hash], ['uid' => $uid]);
    }

    public function findAllRecords(): array
    {
        $records = [];
        $result = $this->getQueryBuilder()
            ->select('crdate', 'valid_until', 'uid', 'name', 'identifier', 'description', 'hidden')
            ->from(self::TABLE_NAME)
            ->executeQuery();

        while ($row = $result->fetchAssociative()) {
            $records[] = $row;
        }

        // casts: depending on the DB driver, integer columns may come back as strings
        return array_map(
            static function ($tokenRecord) {
                $tokenRecord['is_expired'] = (int)$tokenRecord['valid_until'] < time();
                $tokenRecord['is_hidden'] = (int)$tokenRecord['hidden'] === 1;
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
