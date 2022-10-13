<?php
declare(strict_types=1);
/**
 * This file is part of the api_token extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * README.md file that was distributed with this source code.
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
    ];

    public const HEADER_NAME_IDENTIFIER = 'X-API-IDENTIFIER';
}
