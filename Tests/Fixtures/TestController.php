<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Fixtures;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Test controller for E2E testing
 * Provides real API endpoints that can be called via HTTP
 */
final class TestController
{
    #[AsApiEndpoint(method: 'GET', path: '/test/hello')]
    public function hello(): ResponseInterface
    {
        return new JsonResponse([
            'success' => true,
            'message' => 'Hello from Simple REST API!',
            'timestamp' => time(),
        ]);
    }

    #[AsApiEndpoint(method: 'GET', path: '/test/echo/{message}')]
    public function echo(string $message): ResponseInterface
    {
        return new JsonResponse([
            'success' => true,
            'message' => $message,
            'length' => strlen($message),
        ]);
    }

    #[AsApiEndpoint(method: 'POST', path: '/test/users')]
    public function createUser(ServerRequestInterface $request): ResponseInterface
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
                ['success' => false, 'errors' => $errors],
                400
            );
        }

        return new JsonResponse(
            [
                'success' => true,
                'user' => [
                    'id' => random_int(1000, 9999),
                    'name' => $body['name'],
                    'email' => $body['email'],
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ],
            201
        );
    }

    #[AsApiEndpoint(method: 'PUT', path: '/test/users/{id}')]
    public function updateUser(int $id, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);
        assert(is_array($body));

        return new JsonResponse([
            'success' => true,
            'user' => [
                'id' => $id,
                'name' => $body['name'] ?? 'Unknown',
                'email' => $body['email'] ?? 'unknown@example.com',
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ]);
    }

    #[AsApiEndpoint(method: 'DELETE', path: '/test/users/{id}')]
    public function deleteUser(int $id): ResponseInterface
    {
        if ($id === 999) {
            return new JsonResponse(
                ['success' => false, 'error' => 'User not found'],
                404
            );
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'User deleted successfully',
            'deleted_id' => $id,
        ]);
    }

    #[AsApiEndpoint(method: 'GET', path: '/test/search')]
    public function search(ServerRequestInterface $request): ResponseInterface
    {
        $queryParams = $request->getQueryParams();

        return new JsonResponse([
            'success' => true,
            'query' => $queryParams['q'] ?? '',
            'limit' => (int)($queryParams['limit'] ?? 10),
            'offset' => (int)($queryParams['offset'] ?? 0),
            'results' => [
                ['id' => 1, 'title' => 'Result 1'],
                ['id' => 2, 'title' => 'Result 2'],
            ],
        ]);
    }

    #[AsApiEndpoint(method: 'GET', path: '/test/headers')]
    public function checkHeaders(ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'success' => true,
            'headers' => [
                'authorization' => $request->hasHeader('Authorization'),
                'content_type' => $request->getHeader('Content-Type')[0] ?? null,
                'custom_header' => $request->getHeader('X-Custom-Header')[0] ?? null,
            ],
        ]);
    }

    #[AsApiEndpoint(method: 'PATCH', path: '/test/users/{id}')]
    public function patchUser(int $id, ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);
        assert(is_array($body));

        return new JsonResponse([
            'success' => true,
            'id' => $id,
            'updated_fields' => array_keys($body),
            'data' => $body,
        ]);
    }

    #[AsApiEndpoint(method: 'GET', path: '/test/types/{intParam}/{stringParam}')]
    public function testTypes(int $intParam, string $stringParam): ResponseInterface
    {
        return new JsonResponse([
            'success' => true,
            'intParam' => $intParam,
            'stringParam' => $stringParam,
            'types' => [
                'intParam' => gettype($intParam),
                'stringParam' => gettype($stringParam),
            ],
        ]);
    }
}
