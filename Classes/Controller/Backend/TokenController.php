<?php
declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Controller\Backend;

use Fr\ApiToken\Configuration\Extension;
use Fr\ApiToken\Domain\Model\Token;
use Fr\ApiToken\Domain\Repository\TokenRepository;
use Fr\ApiToken\Service\TokenBuildService;
use Fr\ApiToken\Traits\TokenServiceTrait;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class TokenController extends ActionController
{
    use TokenServiceTrait;

    public const TABLE_NAME = Token::TABLE_NAME;

    /**
     * @param TokenRepository $repository
     */
    protected TokenRepository $repository;

    /**
     * @var TokenBuildService
     */
    protected TokenBuildService $tokenBuildService;

    public function __construct(?TokenBuildService $tokenBuildService = null, ?TokenRepository $repository = null)
    {
        $this->tokenBuildService = $tokenBuildService ?? GeneralUtility::makeInstance(TokenBuildService::class);
        $this->repository = $repository ?? GeneralUtility::makeInstance(TokenRepository::class);
    }

    /**
     * Displays list of tokens
     */
    public function listAction(): void
    {
        $records = $this->repository->findAllRecords();

        $this->view->assignMultiple(
            [
                'records' => $records,
                'tokenIconIdentifier' => Extension::TOKEN_SVG,
                'route'=> '/',
                'tableName' => \Fr\ApiToken\Domain\Model\Token::TABLE_NAME,
            ]
        );
    }

    /**
     * Displays s form for a new token
     *
     * @param Token|null $newToken
     * @throws \Exception
     */
    public function newAction(Token $newToken = null): void
    {
        $newToken = $newToken ?? new Token();
        $secret = $this->tokenService->generateSecret();
        $hash = $this->tokenService->hash($secret);
        $identifier = $this->tokenService->generateIdentifier();

        $this->addFlashMessage(
            LocalizationUtility::translate('message.saveIdentifierAndSecretNow', Extension::KEY),
            LocalizationUtility::translate('header.saveIdentifierAndSecret', Extension::KEY),
            FlashMessage::INFO
        );

        $this->view->assignMultiple(
            [
                'identifier' => $identifier,
                'secret' => $secret,
                'hash' => $hash,
                'newToken' => $newToken
            ]
        );
    }

    /**
     * Creates and persists a new
     * token from form values
     *
     * @param array $newToken
     * @throws StopActionException
     * @throws \Exception
     */
    public function createAction(array $newToken): void
    {
        $this->repository->persistNewToken(
            $this->tokenBuildService->buildInitialToken(
                $newToken['name'],
                $newToken['description'],
                $newToken['identifier'],
                $newToken['hash']
            )
        );
        $this->redirect('list');
    }

}
