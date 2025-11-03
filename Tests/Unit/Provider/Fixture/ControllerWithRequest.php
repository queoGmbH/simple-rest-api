<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Provider\Fixture;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class ControllerWithRequest
{
    public function methodWithRequest(int $userId, ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse(['userId' => $userId]);
    }

    public function methodWithRequestFirst(ServerRequestInterface $request, int $userId): ResponseInterface
    {
        return new JsonResponse(['userId' => $userId]);
    }
}
