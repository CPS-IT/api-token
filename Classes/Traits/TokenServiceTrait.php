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
