<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\DependencyInjection;

use Queo\SimpleRestApi\Provider\ApiEndpointProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final readonly class ApiEndpointProviderPass implements CompilerPassInterface
{
    public function __construct(private string $tagName)
    {
    }

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ApiEndpointProvider::class)) {
            // If there's no api endpoint provider registered to begin with, don't bother registering listeners with it.
            return;
        }

        $apiEndpointProviderDefinition = $container->findDefinition(ApiEndpointProvider::class);
        $taggedServices = $container->findTaggedServiceIds($this->tagName);

        foreach ($taggedServices as $serviceName => $tags) {
            $service = $container->findDefinition($serviceName);
            $service->setPublic(true);
            $className = $service->getClass();

            foreach ($tags as $attributes) {
                $methodName = $attributes['method'] ?? '';
                $httpMethod = $attributes['http_method'] ?? 'GET';
                $path = $attributes['path'] ?? '';
                $summary = $attributes['summary'] ?? '';
                $description = $attributes['description'] ?? '';
                $endpointTags = $attributes['tags'] ?? [];
                $requestBody = $attributes['requestBody'] ?? [];
                $responses = $attributes['responses'] ?? [];
                $openApiParameters = $attributes['parameters'] ?? [];
                $security = $attributes['security'] ?? [];

                $apiEndpointProviderDefinition->addMethodCall('addEndpoint', [
                    $className,
                    $methodName,
                    $httpMethod,
                    $path,
                    $summary,
                    $description,
                    $endpointTags,
                    $requestBody,
                    $responses,
                    $openApiParameters,
                    $security
                ]);
            }
        }
    }
}
