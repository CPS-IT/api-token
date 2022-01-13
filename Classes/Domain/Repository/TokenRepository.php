<?php
declare(strict_types=1);

namespace Fr\ApiToken\Domain\Repository;

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

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TokenRepository implements TokenRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function findOneByIdentifier(string $identifier): array
    {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $result = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(self::IDENTIFIER_COLUMN, $queryBuilder->createNamedParameter($identifier))
            )->execute()->fetchAssociative();

        return $result ? :[];
    }

    public function findAll(): array
    {
        $record = [];
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(self::TABLE_NAME);
        $result = $queryBuilder
            ->select('crdate','valid_until','uid','name','identifier','description')
            ->from(self::TABLE_NAME)
            ->execute();

        while ($row = $result->fetchAssociative()) {
            // Do something with that single row
            $record[] = $row;
        }
        return $record;
    }


}
