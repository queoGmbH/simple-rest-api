<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Context;

use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\AspectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SimpleRestApiAspect implements AspectInterface
{
    public const ASPECT_IDENTIFIER = 'simple_rest_api';
    private ExtensionConfiguration $configuration;

    public function __construct(private readonly ServerRequestInterface $request)
    {
        $this->configuration = GeneralUtility::makeInstance(ExtensionConfiguration::class, $this->request);
    }

    public function get(string $name)
    {
        return match($name) {
            'configuration' => $this->configuration,
            'request' => $this->request,
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
