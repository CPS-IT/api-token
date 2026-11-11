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
use CPSIT\ApiToken\Service\HashUpgradeAwareInterface;
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
        $this->authenticated = false;
        $this->validUntil = null;
        $this->identifier = $identifier;
        $this->token = $identifier !== '' ? $this->repository->findOneRecordByIdentifier($identifier) : [];

        return $this;
    }

    /**
     * Returns a copy without any per-request state (identifier, token,
     * result, method). Use it when the instance is a shared service,
     * so a previous request's result can never leak into the next one.
     */
    public function forNewRequest(): self
    {
        $clone = clone $this;
        $clone->authenticated = null;
        $clone->identifier = '';
        $clone->token = [];
        $clone->validUntil = null;
        $clone->method = RestApiInterface::METHOD_GET;

        return $clone;
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
        // never let an earlier result survive a new check
        $this->authenticated = false;
        $this->validUntil = null;

        if (
            empty($secret)
            || empty($this->identifier)
            || !$this->validateHeaderName($name)
            || empty($this->token)
        ) {
            return $this;
        }

        $timeZone = new \DateTimeZone(date_default_timezone_get());
        $now = new \DateTimeImmutable('now', $timeZone);
        $this->validUntil = $now->setTimestamp((int)($this->token['valid_until'] ?? 0));

        // hidden/deleted are already excluded by the repository; checked again
        // so a replaced or extended repository cannot reintroduce revoked tokens
        if (
            $this->validUntil < $now
            || !empty($this->token['hidden'])
            || !empty($this->token['deleted'])
            || empty($this->token['hash'])
        ) {
            return $this;
        }

        $this->authenticated = $this->tokenService->check($secret, (string)$this->token['hash']);

        if ($this->authenticated) {
            $this->upgradeHash($secret);
        }

        return $this;
    }

    /**
     * Replaces a legacy (slow) hash with the current format after the secret
     * was verified, so the expensive verification happens at most once.
     */
    protected function upgradeHash(string $secret): void
    {
        if (
            !$this->tokenService instanceof HashUpgradeAwareInterface
            || empty($this->token['uid'])
            || !$this->tokenService->needsRehash((string)$this->token['hash'])
        ) {
            return;
        }

        try {
            $newHash = $this->tokenService->hash($secret);
            $this->repository->updateHash((int)$this->token['uid'], $newHash);
            $this->token['hash'] = $newHash;
        } catch (\Throwable) {
            // The upgrade is an optimisation: a failed write must not fail an
            // authenticated request. It is retried on the next successful use.
        }
    }
}
