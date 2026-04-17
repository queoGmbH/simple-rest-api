<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Dev\Controller;

use Psr\Http\Message\ResponseInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Stable test endpoints for Playwright E2E tests.
 *
 * Registered only in Development context via Configuration/Services.dev.yaml.
 * Must NOT be loaded in production.
 */
final class E2eTestController
{
    // -------------------------------------------------------------------------
    // Endpoint resolution — #13
    // -------------------------------------------------------------------------

    #[AsApiEndpoint(method: 'GET', path: '/e2e/ping')]
    public function pingGet(): ResponseInterface
    {
        return new JsonResponse(['ok' => true, 'method' => 'GET']);
    }

    #[AsApiEndpoint(method: 'POST', path: '/e2e/ping')]
    public function pingPost(): ResponseInterface
    {
        return new JsonResponse(['ok' => true, 'method' => 'POST']);
    }

    #[AsApiEndpoint(method: 'PUT', path: '/e2e/ping')]
    public function pingPut(): ResponseInterface
    {
        return new JsonResponse(['ok' => true, 'method' => 'PUT']);
    }

    #[AsApiEndpoint(method: 'PATCH', path: '/e2e/ping')]
    public function pingPatch(): ResponseInterface
    {
        return new JsonResponse(['ok' => true, 'method' => 'PATCH']);
    }

    #[AsApiEndpoint(method: 'DELETE', path: '/e2e/ping')]
    public function pingDelete(): ResponseInterface
    {
        return new JsonResponse(['ok' => true, 'method' => 'DELETE']);
    }

    // -------------------------------------------------------------------------
    // Parameter handling — #14
    // -------------------------------------------------------------------------

    #[AsApiEndpoint(method: 'GET', path: '/e2e/params/int/{value}')]
    public function paramInt(int $value): ResponseInterface
    {
        return new JsonResponse(['value' => $value, 'type' => 'int']);
    }

    #[AsApiEndpoint(method: 'GET', path: '/e2e/params/float/{value}')]
    public function paramFloat(float $value): ResponseInterface
    {
        return new JsonResponse(['value' => $value, 'type' => 'float']);
    }

    #[AsApiEndpoint(method: 'GET', path: '/e2e/params/bool/{value}')]
    public function paramBool(bool $value): ResponseInterface
    {
        return new JsonResponse(['value' => $value, 'type' => 'bool']);
    }

    #[AsApiEndpoint(method: 'GET', path: '/e2e/params/string/{value}')]
    public function paramString(string $value): ResponseInterface
    {
        return new JsonResponse(['value' => $value, 'type' => 'string']);
    }

    #[AsApiEndpoint(method: 'GET', path: '/e2e/params/multi/{intVal}/{stringVal}')]
    public function paramMulti(int $intVal, string $stringVal): ResponseInterface
    {
        return new JsonResponse(['intVal' => $intVal, 'stringVal' => $stringVal]);
    }
}
