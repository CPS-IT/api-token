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
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
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
     * Displays s form for a new token
     *
     * @param Token|null $newToken
     * @throws \Exception
     */
    public function newAction(Token $newToken = null): ResponseInterface
    {
        $newToken ??= new Token();
        $secret = $this->tokenService->generateSecret();
        $hash = $this->tokenService->hash($secret);
        $identifier = $this->tokenService->generateIdentifier();

        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple(
            [
                'identifier' => $identifier,
                'secret' => $secret,
                'hash' => $hash,
                'newToken' => $newToken,
            ]
        );
        return $moduleTemplate->renderResponse('Backend/Token/New');
    }

    /**
     * Creates and persists a new
     * token from form values
     *
     * @param array $newToken
     * @throws StopActionException
     * @throws \Exception
     */
    public function createAction(array $newToken): ResponseInterface
    {
        $this->tokenRepository->persistNewToken(
            $this->tokenBuildService->buildInitialToken(
                $newToken['name'],
                $newToken['description'],
                $newToken['identifier'],
                $newToken['hash']
            )
        );
        return $this->redirect('list');
    }

    protected function translate(string $key, ?array $arguments = null): string
    {
        return LocalizationUtility::translate($key, Extension::NAME, $arguments) ?? $key;
    }

}
