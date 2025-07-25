<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class TestController
{
    private ServerRequestInterface $request;

    public function __construct(private Context $context)
    {
        /** @var SimpleRestApiAspect $simpleRestApi */
        $simpleRestApi = $this->context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);
        $this->request = $simpleRestApi->getRequest();
    }

    #[AsApiEndpoint(method: 'GET', path: '/v1/my-get-endpoint')]
    public function myEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    #[AsApiEndpoint(method: 'POST', path: '/v1/my-post-endpoint')]
    public function someOtherEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    #[AsApiEndpoint(method: 'GET', path: '/v1/my-param-endpoint/{param1}/{param2}')]
    public function someParamEndpoint(int $param1, string $param2): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId,
            'parameters' => [$param1, $param2]
        ]);
    }
}
