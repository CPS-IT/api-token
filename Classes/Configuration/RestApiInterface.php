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

namespace CPSIT\ApiToken\Configuration;

interface RestApiInterface
{
    /**
     * HTTP methods
     */
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_GET = 'GET';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_UPDATE = 'UPDATE';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';

    /**
     * Valid methods
     */
    public const VALID_METHODS = [
        RestApiInterface::METHOD_DELETE,
        RestApiInterface::METHOD_GET,
        RestApiInterface::METHOD_PATCH,
        RestApiInterface::METHOD_POST,
        RestApiInterface::METHOD_PUT,
        RestApiInterface::METHOD_UPDATE,
        RestApiInterface::METHOD_HEAD,
        RestApiInterface::METHOD_OPTIONS,
        RestApiInterface::METHOD_TRACE,
        RestApiInterface::METHOD_CONNECT,
    ];

    public const HEADER_NAME_IDENTIFIER = 'X-API-IDENTIFIER';
}
