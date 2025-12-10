<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Controller;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\Builder\OpenApiSpecBuilder;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\JsonResponse;

final readonly class OpenApiController
{
    private ServerRequestInterface $request;

    public function __construct(
        private Context $context,
        private OpenApiSpecBuilder $specBuilder
    ) {
        /** @var SimpleRestApiAspect $simpleRestApi */
        $simpleRestApi = $this->context->getAspect(SimpleRestApiAspect::ASPECT_IDENTIFIER);
        $this->request = $simpleRestApi->getRequest();
    }

    #[AsApiEndpoint(
        method: 'GET',
        path: '/openapi.json',
        summary: 'OpenAPI Specification',
        description: 'Returns the OpenAPI 3.0 specification for all registered API endpoints',
        tags: ['documentation'],
        responses: [
            '200' => [
                'description' => 'OpenAPI specification in JSON format',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object'
                        ]
                    ]
                ]
            ]
        ]
    )]
    public function getOpenApiSpec(): JsonResponse
    {
        $spec = $this->specBuilder->buildSpec($this->request);

        return new JsonResponse($spec);
    }
}
