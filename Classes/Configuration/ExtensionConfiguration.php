<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Configuration;

final readonly class ExtensionConfiguration implements ExtensionConfigurationInterface
{
    private const DEFAULT_API_BASE_PATH = '/api/';

    private const BASE_PATH_PATTERN = '/^\/([a-zA-Z0-9_-]+\/)+$/';

    public function getApiBasePath(): string
    {
        // Get from extension configuration
        // @phpstan-ignore-next-line offsetAccess.nonOffsetAccessible
        $extensionConfig = $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] ?? null;

        if (is_array($extensionConfig) && isset($extensionConfig['basePath'])) {
            $basePath = $extensionConfig['basePath'];

            if (is_string($basePath) && $this->isValidBasePath($basePath)) {
                return $basePath;
            }
        }

        // Fallback to default
        return self::DEFAULT_API_BASE_PATH;
    }

    /**
     * Check if debug mode is enabled.
     *
     * When debug mode is disabled (default), endpoints from the extension's own
     * namespace (Queo\SimpleRestApi\*) are hidden in the backend module.
     * This prevents test/example controllers from cluttering production installations.
     *
     * Enable via extension configuration in the Extension Manager or LocalConfiguration.
     */
    public function isDebugMode(): bool
    {
        // Get from extension configuration
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
