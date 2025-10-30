<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Context;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SimpleRestApiAspect implements AspectInterface
{
    public const ASPECT_IDENTIFIER = 'simple_rest_api';

    private ExtensionConfiguration $configuration;

    public function __construct(private ServerRequestInterface $request)
    {
        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class, $this->request);
    }

    public function get(string $name): mixed
    {
        return match ($name) {
            'configuration' => $this->configuration,
            'request' => $this->request,
            default => throw new InvalidArgumentException(
                sprintf('Unknown property "%s" requested. Valid properties are: configuration, request', $name),
                1647890123
            ),
        };
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getConfiguration(): ExtensionConfiguration
    {
        return $this->configuration;
    }
}
