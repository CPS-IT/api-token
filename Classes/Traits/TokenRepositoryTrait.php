<?php
declare(strict_types=1);
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Traits;

use Fr\ApiToken\Domain\Repository\TokenRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;


/**
 * Provides a token repository
 * and injection method
 */
trait TokenRepositoryTrait
{
    /**
     * @var RepositoryInterface|TokenRepository
     */
    protected $repository;

    /**
     * Inject a token repository
     *
     * @param TokenRepository $repository
     */
    public function injectTokenRepository(TokenRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return TokenRepository
     */
    public function getRepository(): TokenRepository
    {
        if (!$this->repository instanceof TokenRepository) {
            $this->repository = GeneralUtility::makeInstance(TokenRepository::class);
        }
        return $this->repository;
    }

}