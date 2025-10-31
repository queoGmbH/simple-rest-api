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

    #[AsApiEndpoint(
        method: 'GET',
        path: '/v1/my-get-endpoint',
        summary: 'Basic GET endpoint example',
        description: 'Demonstrates a simple GET endpoint that returns success status, current URL, and language information from TYPO3 context'
    )]
    public function myEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    #[AsApiEndpoint(
        method: 'POST',
        path: '/v1/my-post-endpoint',
        summary: 'POST endpoint example',
        description: 'Shows how to create a POST endpoint. Access request body via $this->request or inject ServerRequestInterface as method parameter'
    )]
    public function someOtherEndpoint(): JsonResponse
    {
        $languageId = $this->context->getPropertyFromAspect('language', 'id');

        return new JsonResponse([
            'success' => true,
            'url' => $this->request->getUri()->__toString(),
            'language' => $languageId
        ]);
    }

    /**
     * @param int $param1 The first numeric parameter (e.g., user ID or record ID)
     * @param string $param2 The second string parameter (e.g., username or slug)
     */
    #[AsApiEndpoint(
        method: 'GET',
        path: '/v1/my-param-endpoint/{param1}/{param2}',
        summary: 'Endpoint with URL parameters',
        description: 'Demonstrates parameter mapping from URL path to method arguments. Parameters are automatically type-cast (int, string, etc.) and passed to the method'
    )]
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
