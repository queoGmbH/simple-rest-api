<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class DummyController
{
    public function dummyMethodWithRequest(int $param1, string $param2, ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['success' => true, 'param1' => $param1, 'param2' => $param2, 'requestUrl' => (string)$request->getUri()]);
    }

    public function dummyMethodWithoutRequest(int $param1, string $param2): ResponseInterface
    {
        return new JsonResponse(['success' => true, 'param1' => $param1, 'param2' => $param2]);
    }
}
