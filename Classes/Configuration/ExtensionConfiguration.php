<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Site\Entity\Site;

final class ExtensionConfiguration implements ExtensionConfigurationInterface
{
    private const DEFAULT_API_BASE_PATH = '/api/';

    private const SETTING_KEY_BASE_PATH = 'simple_rest_api.basePath';

    private const SETTING_KEY_DEBUG_MODE = 'simple_rest_api.debugMode';

    private const BASE_PATH_PATTERN = '/^\/([a-zA-Z0-9_-]+\/)+$/';

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

            if (is_string($basePath) && $this->isValidBasePath($basePath)) {
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

    /**
     * Check if debug mode is enabled.
     *
     * When debug mode is disabled (default), endpoints from the extension's own
     * namespace (Queo\SimpleRestApi\*) are hidden in the backend module.
     * This prevents test/example controllers from cluttering production installations.
     *
     * Enable via:
     * - Site settings: settings.simple_rest_api.debugMode: true
     * - LocalConfiguration: $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']['debugMode'] = true
     */
    public function isDebugMode(): bool
    {
        // Try to get from site settings first
        $site = $this->getCurrentSite();
        if ($site instanceof Site) {
            $settings = $site->getSettings();
            $debugMode = $settings->get(self::SETTING_KEY_DEBUG_MODE);

            if (is_bool($debugMode)) {
                return $debugMode;
            }

            // Handle string values like "1", "true", "0", "false"
            if (is_string($debugMode)) {
                return filter_var($debugMode, FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Fallback to extension configuration
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $extensionConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] ?? null;
        if (is_array($extensionConfig) && isset($extensionConfig['debugMode'])) {
            return (bool)$extensionConfig['debugMode'];
        }

        // Default: false (debug mode off, hide extension endpoints)
        return false;
    }

    /**
     * Validates that the base path matches the required format.
     *
     * The base path must:
     * - Start with a forward slash
     * - End with a forward slash
     * - Only contain letters, numbers, hyphens, and underscores between slashes
     *
     * Valid examples: /api/, /rest/, /services/, /api/v2/
     * Invalid examples: api/, /api, /api/v2, /api with spaces/
     */
    private function isValidBasePath(string $basePath): bool
    {
        return preg_match(self::BASE_PATH_PATTERN, $basePath) === 1;
    }
}
