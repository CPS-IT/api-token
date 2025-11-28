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

namespace CPSIT\ApiToken\Authentication;

use CPSIT\ApiToken\Configuration\RestApiInterface;
use CPSIT\ApiToken\Domain\Repository\TokenRepository;
use CPSIT\ApiToken\Exception\InvalidHttpMethodException;
use CPSIT\ApiToken\Service\TokenService;
use CPSIT\ApiToken\Service\TokenServiceInterface;
use CPSIT\ApiToken\Traits\TokenRepositoryTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiKeyAuthentication implements HeaderAwareInterface
{
    use TokenRepositoryTrait;
    public const HEADER_NAME_AUTHORIZATION = 'application-authorization';
    public const HEADER_NAME_IDENTIFIER = RestApiInterface::HEADER_NAME_IDENTIFIER;

    /**
     * @var bool|null
     */
    protected $authenticated;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var TokenServiceInterface
     */
    protected $tokenService;

    /**
     * @var string
     */
    protected $method = RestApiInterface::METHOD_GET;

    /**
     * @var array<string, mixed>
     */
    protected $token;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $validUntil;

    public function __construct(?TokenServiceInterface $tokenService = null, ?TokenRepository $repository = null)
    {
        $this->tokenService = $tokenService ?? GeneralUtility::makeInstance(TokenService::class);
        $this->repository = $repository ?? GeneralUtility::makeInstance(TokenRepository::class);
    }

    #[\Override]
    public function isAuthenticated(): bool
    {
        return $this->authenticated ?? false;
    }

    #[\Override]
    public function getMethod(): string
    {
        return $this->method;
    }

    #[\Override]
    public function validUntil(): \DateTimeImmutable
    {
        return $this->validUntil ?? new \DateTimeImmutable('0000-00-00T00:00:00+00:00');
    }

    /**
     * @param string $name
     * @return bool
     */
    #[\Override]
    public function validateHeaderName(string $name): bool
    {
        return strtolower($name) === static::HEADER_NAME_AUTHORIZATION;
    }

    public function withIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        $this->token = $this->repository->findOneRecordByIdentifier($this->identifier);

        return $this;
    }

    /**
     * @param string $method Any valid HTTP method
     * @return ApiKeyAuthentication
     * @throws InvalidHttpMethodException
     */
    public function withMethod(string $method): ApiKeyAuthentication
    {
        if (!in_array($method, RestApiInterface::VALID_METHODS)) {
            throw new InvalidHttpMethodException(
                sprintf('API does not support method %s!', $method),
                1585497878
            );
        }
        $this->method = $method;
        return $this;
    }

    /**
     * @param string $name
     * @param string $secret
     * @return HeaderAwareInterface
     * @throws \Exception
     */
    #[\Override]
    public function fromHeader(string $secret, string $name = self::HEADER_NAME_AUTHORIZATION): HeaderAwareInterface
    {

        if (empty($secret) ||
            empty($this->identifier) ||
            !$this->validateHeaderName($name)) {
            goto onFailure;
        }

        if (!empty($this->token)) {

            $timeZone = new \DateTimeZone(date_default_timezone_get());
            $now = new \DateTimeImmutable('now', $timeZone);
            $this->validUntil = (clone $now)->setTimestamp((int)$this->token['valid_until']);

            if (
                $this->validUntil <  $now ||
                empty($this->token['hash'])
            ) {
                goto onFailure;
            }

            $this->authenticated = $this->tokenService->check($secret, $this->token['hash']);

            return $this;
        }

        // return default (invalid) instance
        onFailure: {
            $this->authenticated = false;
        }

        return $this;
    }

}
