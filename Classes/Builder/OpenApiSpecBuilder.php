<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Builder;

use TYPO3\CMS\Core\Site\Entity\Site;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfigurationInterface;
use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Queo\SimpleRestApi\Value\ApiEndpoint;

final readonly class OpenApiSpecBuilder
{
    public function __construct(
        private ApiEndpointProvider $endpointProvider,
        private ExtensionConfigurationInterface $extensionConfiguration
    ) {
    }

    /**
     * Generate OpenAPI 3.0.3 specification as array.
     *
     * @return array<string, mixed>
     */
    public function buildSpec(ServerRequestInterface $request): array
    {
        $endpoints = $this->endpointProvider->getAllEndpoints();

        // Hide extension's own endpoints unless debug mode is enabled
        if (!$this->extensionConfiguration->isDebugMode()) {
            $endpoints = array_filter(
                $endpoints,
                fn(ApiEndpoint $endpoint): bool => !str_starts_with($endpoint->className, 'Queo\\SimpleRestApi\\')
            );
        }

        $site = $request->getAttribute('site');
        $baseUrl = $site instanceof Site ? $site->getBase()->__toString() : '';
        $basePath = $this->extensionConfiguration->getApiBasePath();

        $paths = [];
        $allTags = [];

        foreach ($endpoints as $endpoint) {
            $path = $endpoint->path;
            $method = strtolower($endpoint->httpMethod);

            if (!isset($paths[$path])) {
                $paths[$path] = [];
            }

            $operation = $this->buildOperation($endpoint);
            $paths[$path][$method] = $operation;

            // Collect unique tags
            foreach ($endpoint->tags as $tag) {
                if (!in_array($tag, $allTags, true)) {
                    $allTags[] = $tag;
                }
            }
        }

        // Sort paths alphabetically
        ksort($paths);

        // Build tags array with descriptions
        $tags = array_map(fn(string $tag): array => ['name' => $tag], $allTags);

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'REST API',
                'description' => 'REST API endpoints managed by Simple REST API extension',
                'version' => '1.0.0'
            ],
            'servers' => [
                [
                    'url' => rtrim($baseUrl, '/') . $basePath,
                    'description' => 'API Server'
                ]
            ],
            'paths' => $paths,
            'tags' => $tags,
            'components' => [
                'schemas' => []
            ]
        ];
    }

    /**
     * Build an operation object for a single endpoint.
     *
     * @return array<string, mixed>
     */
    private function buildOperation(ApiEndpoint $endpoint): array
    {
        $operation = [
            'summary' => $endpoint->summary ?: 'API Endpoint',
            'description' => $endpoint->description,
            'operationId' => $this->generateOperationId($endpoint),
        ];

        // Add tags if present
        if ($endpoint->tags !== []) {
            $operation['tags'] = $endpoint->tags;
        }

        // Add parameters (path and OpenAPI parameters)
        $parameters = $this->buildParameters($endpoint);
        if ($parameters !== []) {
            $operation['parameters'] = $parameters;
        }

        // Add request body if present
        if ($endpoint->requestBody !== []) {
            $operation['requestBody'] = $endpoint->requestBody;
        }

        // Add responses
        $operation['responses'] = $this->buildResponses($endpoint);

        // Add security if present
        if ($endpoint->security !== []) {
            $operation['security'] = $endpoint->security;
        }

        return $operation;
    }

    /**
     * Generate a unique operation ID for an endpoint.
     */
    private function generateOperationId(ApiEndpoint $endpoint): string
    {
        // Use method name if available, otherwise generate from HTTP method and path
        if ($endpoint->method !== '' && $endpoint->method !== '0') {
            return $endpoint->method;
        }

        $pathParts = array_filter(explode('/', $endpoint->path), fn($part): bool => !empty($part) && !str_starts_with((string) $part, '{'));
        return strtolower($endpoint->httpMethod) . ucfirst(implode('', array_map(ucfirst(...), $pathParts)));
    }

    /**
     * Build parameters array from path parameters and OpenAPI parameters.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildParameters(ApiEndpoint $endpoint): array
    {
        $parameters = [];

        // Add path parameters from the endpoint path
        foreach ($endpoint->parameters as $param) {
            // Skip ServerRequestInterface
            if ($param->type === ServerRequestInterface::class) {
                continue;
            }

            $parameters[] = [
                'name' => $param->name,
                'in' => 'path',
                'required' => true,
                'description' => $param->description ?: 'Path parameter: ' . $param->name,
                'schema' => [
                    'type' => $this->mapPhpTypeToOpenApiType($param->type)
                ]
            ];
        }

        // Add additional OpenAPI parameters (query, header, etc.)
        return array_merge($parameters, $endpoint->openApiParameters);
    }

    /**
     * Build responses object.
     *
     * @return array<string|int, mixed>
     */
    private function buildResponses(ApiEndpoint $endpoint): array
    {
        // If custom responses are defined, use them
        if ($endpoint->responses !== []) {
            return $endpoint->responses;
        }

        // Default responses
        return [
            '200' => [
                'description' => 'Successful response',
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object'
                        ]
                    ]
                ]
            ],
            '400' => [
                'description' => 'Bad request'
            ],
            '404' => [
                'description' => 'Not found'
            ],
            '500' => [
                'description' => 'Internal server error'
            ]
        ];
    }

    /**
     * Map PHP type to OpenAPI type.
     */
    private function mapPhpTypeToOpenApiType(string $phpType): string
    {
        return match ($phpType) {
            'int' => 'integer',
            'float' => 'number',
            'bool' => 'boolean',
            'string' => 'string',
            'array' => 'array',
            default => 'string'
        };
    }
}
