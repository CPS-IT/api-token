<?php
declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Traits;

use CPSIT\ApiToken\Service\TokenServiceInterface;

/**
 * Provides a TokenService
 */
trait TokenServiceTrait
{
    /**
     * @var TokenServiceInterface
     */
    protected $tokenService;

    public function injectTokenService(TokenServiceInterface $tokenService): void
    {
        $this->tokenService = $tokenService;
    }

    /**
     * @return
     */
    public function getTokenService(): TokenServiceInterface
    {
        return $this->tokenService;
    }
}
