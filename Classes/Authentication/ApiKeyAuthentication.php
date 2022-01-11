<?php
namespace Fr\ApiToken\Authentication;
/**
 * This file is part of the iki Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Fr\ApiToken\Configuration\RestApiInterface;
use Fr\ApiToken\Domain\Repository\TokenRepository;
use Fr\ApiToken\Exception\InvalidHttpMethodException;
use Fr\ApiToken\Service\TokenService;
use Fr\ApiToken\Service\TokenServiceInterface;
use Fr\ApiToken\Traits\TokenRepositoryTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ApiKeyAuthentication implements HeaderAwareInterface
{
    use TokenRepositoryTrait;
    public const HEADER_NAME_AUTHORIZATION = 'application-authorization';
    public const HEADER_NAME_IDENTIFIER = RestApiInterface::HEADER_NAME_IDENTIFIER;


    /**
     * @var bool
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
     * @var array
     */
    protected $token;

    /**
     * @var DateTimeImmutable
     */
    protected $validUntil;

    public function __construct(TokenServiceInterface $tokenService = null, TokenRepository $repository = null)
    {
        $this->tokenService = $tokenService ?? GeneralUtility::makeInstance(TokenService::class);
        $this->repository = $repository ?? GeneralUtility::makeInstance(TokenRepository::class);
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated ?? false;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function validUntil(): DateTimeImmutable
    {
        return $this->validUntil ?? new DateTimeImmutable('0000-00-00T00:00:00+00:00');
    }

    /**
     * @param string $name
     * @return bool
     */
    public function validateHeaderName(string $name): bool
    {
        return (strtolower($name) === static::HEADER_NAME_AUTHORIZATION);
    }

    public function withIdentifier(string $identifier):self
    {
        $this->identifier = $identifier;
        $this->token = $this->repository->findOneByIdentifier($this->identifier);

        return $this;
    }

    /**
     * @param string $method Any valid HTTP method
     * @return ApiKeyAuthentication
     * @throws InvalidHttpMethodException
     */
    public function withMethod(string $method):ApiKeyAuthentication
    {
        if (!in_array($method, RestApiInterface::VALID_METHODS))
        {
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
     * @throws Exception
     */
    public function fromHeader(string $secret, string $name = self::HEADER_NAME_AUTHORIZATION): HeaderAwareInterface
    {

        if (empty($secret) ||
            empty($this->identifier) ||
            !$this->validateHeaderName($name)) {
            goto onFailure;
        }

        if (!empty($this->token)) {

            $timeZone = new DateTimeZone(date_default_timezone_get());
            $now = new DateTimeImmutable('now', $timeZone);
            $this->validUntil = (clone $now)->setTimestamp($this->token['valid_until']);

            if(
                $this->validUntil <  $now ||
                empty($this->token['hash'])
            ) {
                goto onFailure;
            }

            $this->authenticated = $this->tokenService->check($secret, $this->token['hash']);

            return  $this;
        }

        // return default (invalid) instance
        onFailure: {
            $this->authenticated = false;
        }

        return $this;
    }


}