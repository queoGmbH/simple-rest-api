<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

final readonly class ExtensionConfiguration implements ExtensionConfigurationInterface
{
    private const DEFAULT_API_BASE_PATH = '/api/';

    private const SETTING_KEY_BASE_PATH = 'simple_rest_api.basePath';

    public function __construct(
        private ?ServerRequestInterface $request = null
    ) {
    }

    public function getApiBasePath(): string
    {
        // Try to get from site settings
        $site = $this->getCurrentSite();
        if ($site instanceof Site) {
            $settings = $site->getSettings();
            $basePath = $settings->get(self::SETTING_KEY_BASE_PATH);

            if (is_string($basePath) && $basePath !== '') {
                return $basePath;
            }
        }

        // Fallback to default
        return self::DEFAULT_API_BASE_PATH;
    }

    private function getCurrentSite(): ?Site
    {
        // Get site from request
        if ($this->request instanceof ServerRequestInterface) {
            $site = $this->request->getAttribute('site');
            if ($site instanceof Site) {
                return $site;
            }
        }

        return null;
    }
}
