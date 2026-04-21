<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class DummyController
{
    public function dummyApiMethod(): JsonResponse
    {
        return new JsonResponse(['success' => true]);
    }

    public function dummyApiMethodWithParams(int $param1, string $param2, ServerRequestInterface $request): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => true,
                'parameters' => [
                    'param1' => $param1,
                    'param2' => $param2,
                    'requestUri' => $request->getUri()->__toString(),
                ]
            ]
        );
    }

    // POST method - create resource
    public function createResource(ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        return new JsonResponse(
            [
                'method' => 'POST',
                'data' => $body,
                'created' => true
            ],
            201
        );
    }

    // POST with path parameter
    public function createSubResource(int $resourceId, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        return new JsonResponse(
            [
                'method' => 'POST',
                'resourceId' => $resourceId,
                'data' => $body,
                'created' => true
            ],
            201
        );
    }

    // PUT method - update/replace resource
    public function updateResource(int $id, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        return new JsonResponse(
            [
                'method' => 'PUT',
                'id' => $id,
                'data' => $body,
                'updated' => true
            ]
        );
    }

    // PATCH method - partial update
    public function patchResource(int $id, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        return new JsonResponse(
            [
                'method' => 'PATCH',
                'id' => $id,
                'data' => $body,
                'patched' => true
            ]
        );
    }

    // DELETE method
    public function deleteResource(int $id): ResponseInterface
    {
        return new JsonResponse(
            [
                'method' => 'DELETE',
                'id' => $id,
                'deleted' => true
            ]
        );
    }

    // DELETE with confirmation
    public function deleteResourceWithConfirmation(int $id, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);
        assert(is_array($body));
        $confirmed = $body['confirmed'] ?? false;

        if (!$confirmed) {
            return new JsonResponse(
                ['error' => 'Deletion requires confirmation'],
                400
            );
        }

        return new JsonResponse(
            [
                'method' => 'DELETE',
                'id' => $id,
                'deleted' => true,
                'confirmed' => true
            ]
        );
    }

    // Method with multiple parameter types
    public function multiTypeParams(int $intParam, string $stringParam, float $floatParam, bool $boolParam): ResponseInterface
    {
        return new JsonResponse(
            [
                'intParam' => $intParam,
                'stringParam' => $stringParam,
                'floatParam' => $floatParam,
                'boolParam' => $boolParam,
                'types' => [
                    'int' => gettype($intParam),
                    'string' => gettype($stringParam),
                    'float' => gettype($floatParam),
                    'bool' => gettype($boolParam),
                ]
            ]
        );
    }

    // Method that checks headers
    public function checkHeaders(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(
            [
                'hasAuthHeader' => $request->hasHeader('Authorization'),
                'authHeader' => $request->getHeader('Authorization')[0] ?? null,
                'contentType' => $request->getHeader('Content-Type')[0] ?? null,
                'customHeader' => $request->getHeader('X-Custom-Header')[0] ?? null,
            ]
        );
    }

    // Method that checks query parameters
    public function checkQueryParams(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        return new JsonResponse(
            [
                'queryParams' => $queryParams,
                'hasLimit' => isset($queryParams['limit']),
                'hasOffset' => isset($queryParams['offset']),
            ]
        );
    }

    // Method that returns error
    public function notFoundResource(int $id): ResponseInterface
    {
        return new JsonResponse(
            ['error' => 'Resource not found', 'id' => $id],
            404
        );
    }

    // Method that returns a non-ResponseInterface value (invalid endpoint implementation)
    public function returnsNonResponse(): string
    {
        return 'this is not a ResponseInterface';
    }

    // Method with an int parameter that fails coercion when given a non-integer URL segment
    public function requiresIntParam(int $id): ResponseInterface
    {
        return new JsonResponse(['id' => $id]);
    }

    // Method with validation that returns 400
    public function validateInput(ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);
        assert(is_array($body));
        $errors = [];

        if (empty($body['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($body['email'])) {
            $errors[] = 'Email is required';
        }

        if ($errors !== []) {
            return new JsonResponse(
                ['errors' => $errors],
                400
            );
        }

        return new JsonResponse(
            ['success' => true, 'data' => $body]
        );
    }
}
