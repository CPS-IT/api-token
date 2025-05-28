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
