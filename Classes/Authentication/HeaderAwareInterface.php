<?php
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
 */
namespace CPSIT\ApiToken\Authentication;

/*
 * Describes authentications based on request headers
 */
interface HeaderAwareInterface extends AuthenticationInterface
{
    /**
     * Returns true if the given header name
     * is valid for this authentication
     * otherwise returns false
     *
     * @param string $name Name of the header
     * @return bool
     */
    public function validateHeaderName(string $name): bool;

    /**
     * Returns an instance from a header
     *
     * @param string $name header name
     * @param string $secret header value
     * @return HeaderAwareInterface
     */
    public function fromHeader(string $secret, string $name): HeaderAwareInterface;
}
