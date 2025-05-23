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

namespace CPSIT\ApiToken\Http;

use TYPO3\CMS\Core\Http\JsonResponse;

class ResponseFactory
{
    /**
     * @param array $data
     * @return JsonResponse
     */
    public static function createOkResponse(array $data = []): JsonResponse
    {
        return static::createResponse($data, 200, true);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    public static function createCreatedResponse(array $data = []): JsonResponse
    {
        return static::createResponse($data, 201);
    }

    /**
     * @return JsonResponse
     */
    public static function createNoContentResponse(): JsonResponse
    {
        return static::createResponse([], 204);
    }

    /**
     * @param string|null $reason
     * @return JsonResponse
     */
    public static function createNotFoundResponse(string $reason = null): JsonResponse
    {
        $reason ??= 'Resource not found.';
        return static::createErroneousResponse(404, $reason);
    }

    /**
     * @param string|null $reason
     * @return JsonResponse
     */
    public static function createForbiddenResponse(string $reason = null): JsonResponse
    {
        $reason ??= 'Access to requested resource is forbidden.';
        return static::createErroneousResponse(403, $reason);
    }

    /**
     * @param string|null $reason
     * @return JsonResponse
     */
    public static function createUnauthorizedResponse(string $reason = null): JsonResponse
    {
        $reason ??= 'Requested resource needs authentication.';
        return static::createErroneousResponse(401, $reason);
    }

    /**
     * @param string|null $reason
     * @return JsonResponse
     */
    public static function createBadRequestResponse(string $reason = null): JsonResponse
    {
        $reason ??= 'Request contains bad parameters.';
        return static::createErroneousResponse(400, $reason);
    }

    /**
     * @param string|null $reason
     * @param \Exception|null $exception
     * @return JsonResponse
     */
    public static function createInternalServerErrorResponse(string $reason = null, \Exception $exception = null): JsonResponse
    {
        $reason ??= 'Error during API request' . ($exception !== null ? ': ' . $exception->getMessage() : '.');
        return static::createErroneousResponse(500, $reason);
    }

    /**
     * @return JsonResponse
     */
    public static function createEmptyResponse(): JsonResponse
    {
        $response = static::createResponse();
        $response->setPayload();
        return $response;
    }

    /**
     * @param array $data
     * @param int $status
     * @param bool $forceOutput
     * @return JsonResponse
     */
    public static function createResponse(array $data = [], int $status = 200, bool $forceOutput = false): JsonResponse
    {
        $response = new JsonResponse($data, $status);
        if ($forceOutput) {
            $response->setPayload($data);
        }
        return $response;
    }

    /**
     * @param int $status
     * @param string $reason
     * @return JsonResponse
     */
    protected static function createErroneousResponse(int $status, string $reason): JsonResponse
    {
        $response = static::createResponse([
            'message' => $reason,
            'code' => $status,
        ])->withStatus($status, $reason);
        return $response;
    }
}
