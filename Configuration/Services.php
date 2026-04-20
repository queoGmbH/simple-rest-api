<?php

declare(strict_types=1);

use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use Queo\SimpleRestApi\DependencyInjection\ApiEndpointProviderPass;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use TYPO3\CMS\Core\Core\Environment;

return static function (ContainerConfigurator $container, ContainerBuilder $containerBuilder): void {
    if (Environment::getContext()->isDevelopment()) {
        $container->services()
            ->defaults()
            ->autowire()
            ->autoconfigure()
            ->load('Queo\\SimpleRestApi\\Dev\\', '../Dev/');
    }

    $containerBuilder->registerAttributeForAutoconfiguration(
        AsApiEndpoint::class,
        static function (ChildDefinition $definition, AsApiEndpoint $attribute, Reflector $reflector): void {
            $definition->addTag(
                AsApiEndpoint::TAG_NAME,
                [
                    'method' => $reflector instanceof ReflectionMethod ? $reflector->getName() : null,
                    'http_method' => $attribute->method,
                    'path' => $attribute->path,
                    'summary' => $attribute->summary,
                    'description' => $attribute->description,
                    'tags' => $attribute->tags,
                ]
            );
        }
    );

    $containerBuilder->addCompilerPass(new ApiEndpointProviderPass('api.endpoint'));
};
