<?php
declare(strict_types=1);
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Domain\Repository;

use ApacheSolrForTypo3\Solr\System\Data\DateTime;
use Fr\ApiToken\Domain\Model\Token;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class TokenRepository implements TokenRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    /**
     * @var ?PersistenceManagerInterface
     */
    protected ?PersistenceManagerInterface $persistenceManager;

    public function __construct(PersistenceManagerInterface $persistenceManager)
    {
        $this->persistenceManager = $persistenceManager;
        $this->queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $this->queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class));
    }

    /**
     * @inheritDoc
     */
    public function findOneRecordByIdentifier(string $identifier): array
    {
        $result = $this->queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $this->queryBuilder->expr()->eq(self::IDENTIFIER_COLUMN, $this->queryBuilder->createNamedParameter($identifier))
            )->execute()->fetchAssociative();

        return $result ? :[];
    }

    public function findAllRecords(): array
    {
        $records = [];
        $result = $this->queryBuilder
            ->select('crdate','valid_until','uid','name','identifier','description', 'hidden')
            ->from(self::TABLE_NAME)
            ->execute();

        while ($row = $result->fetchAssociative()) {
            // Do something with that single row
            $records[] = $row;
        }

        return array_map(
            static function ($tokenRecord)  {
                $tokenRecord['is_expired'] = ($tokenRecord['valid_until'] < (new DateTime('now'))->getTimestamp());
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


}
