<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Integration\Middleware\Fixture;

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
}
