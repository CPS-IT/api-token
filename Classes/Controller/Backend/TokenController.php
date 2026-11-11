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

namespace CPSIT\ApiToken\Controller\Backend;

use CPSIT\ApiToken\Configuration\Extension;
use CPSIT\ApiToken\Domain\Model\Token;
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Service\TokenBuildService;
use CPSIT\ApiToken\Traits\TokenServiceTrait;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

#[AsController]
final class TokenController extends ActionController
{
    use TokenServiceTrait;

    public const string TABLE_NAME = Token::TABLE_NAME;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly TokenBuildService $tokenBuildService,
        protected readonly TokenRepository $tokenRepository
    ) {}

    /**
     * Displays list of tokens
     */
    public function listAction(): ResponseInterface
    {
        $records = $this->tokenRepository->findAllRecords();
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->setTitle('title');
        $moduleTemplate->assignMultiple(
            [
                'records' => $records,
                'tokenIconIdentifier' => Extension::TOKEN_SVG,
                'route' => '/',
                'tableName' => Token::TABLE_NAME,
            ]
        );
        return $moduleTemplate->renderResponse('Backend/Token/List');
    }

    /**
     * Displays a form for a new token (name and description only).
     * Identifier and secret are generated in createAction, server-side.
     *
     * @param Token|null $newToken
     */
    public function newAction(?Token $newToken = null): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple(
            [
                'newToken' => $newToken ?? new Token(),
            ]
        );
        return $moduleTemplate->renderResponse('Backend/Token/New');
    }

    /**
     * Creates and persists a new token and shows its secret exactly once.
     *
     * Identifier, secret and hash are generated here; nothing credential
     * related is taken from the submitted form. The result is rendered
     * directly (no redirect + flash message) so the plain secret is never
     * stored in the backend session.
     *
     * @param array $newToken
     * @throws \Exception
     */
    public function createAction(array $newToken): ResponseInterface
    {
        $name = trim((string)($newToken['name'] ?? ''));
        if ($name === '') {
            $this->addFlashMessage($this->translate('message.nameRequired'), '', ContextualFeedbackSeverity::ERROR);
            return $this->redirect('new');
        }

        $identifier = $this->tokenService->generateIdentifier();
        $secret = $this->tokenService->generateSecret();
        $token = $this->tokenBuildService->buildInitialToken(
            $name,
            trim((string)($newToken['description'] ?? '')),
            $identifier,
            $this->tokenService->hash($secret)
        );
        $this->tokenRepository->persistNewToken($token);

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple(
            [
                'name' => $name,
                'identifier' => $identifier,
                'secret' => $secret,
                'validUntil' => $token->getValidUntil(),
            ]
        );
        return $moduleTemplate->renderResponse('Backend/Token/Created')
            ->withHeader('Cache-Control', 'no-store');
    }

    /**
     * Deletes (revokes) a token. POST only; the backend route token
     * protects it against CSRF. Deletion goes through DataHandler, so it
     * is logged in the backend log like any other record deletion.
     *
     * The argument must not be called "token": backend module arguments
     * are not namespaced, and "token" is the backend route's CSRF parameter.
     */
    public function deleteAction(int $tokenUid): ResponseInterface
    {
        if ($this->request->getMethod() !== 'POST') {
            return $this->redirect('list');
        }

        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start([], [Token::TABLE_NAME => [$tokenUid => ['delete' => 1]]]);
        $dataHandler->process_cmdmap();

        if ($dataHandler->errorLog === []) {
            $this->addFlashMessage($this->translate('message.tokenDeleted'));
        } else {
            $this->addFlashMessage(
                implode(LF, $dataHandler->errorLog),
                $this->translate('message.tokenDeleteFailed'),
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $this->redirect('list');
    }

    protected function translate(string $key, ?array $arguments = null): string
    {
        return LocalizationUtility::translate($key, Extension::NAME, $arguments) ?? $key;
    }

}
