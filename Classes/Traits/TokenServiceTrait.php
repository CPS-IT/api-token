<?php
declare(strict_types=1);
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace Fr\ApiToken\Traits;

use Fr\ApiToken\Service\TokenServiceInterface;

/**
 * Provides a TokenService
 */
trait TokenServiceTrait
{
    /**
     * @var TokenServiceInterface
     */
    protected $tokenService;

    public function injectTokenService(TokenServiceInterface $tokenService)
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