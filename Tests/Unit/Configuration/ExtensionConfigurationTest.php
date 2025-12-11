<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ExtensionConfiguration::class)]
final class ExtensionConfigurationTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected function setUp(): void
    {
        parent::setUp();
        // Reset extension configuration before each test
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']);
    }

    #[Test]
    public function returnsDefaultBasePathWhenNoConfigurationAvailable(): void
    {
        $config = new ExtensionConfiguration();

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function returnsCustomBasePathFromExtensionConfiguration(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
            'basePath' => '/rest/',
        ];

        $config = new ExtensionConfiguration();

        $basePath = $config->getApiBasePath();

        $this->assertSame('/rest/', $basePath);
    }

    #[Test]
    public function returnsDefaultBasePathWhenConfigurationIsEmpty(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
            'basePath' => '',
        ];

        $config = new ExtensionConfiguration();

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function returnsDefaultBasePathWhenBasePathIsNotSet(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [];

        $config = new ExtensionConfiguration();

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function supportsVariousValidBasePathFormats(): void
    {
        $testCases = [
            '/services/' => '/services/',
            '/v1/' => '/v1/',
            '/my-api/' => '/my-api/',
            '/api/v2/' => '/api/v2/',
            '/api_v2/' => '/api_v2/',
            '/123/' => '/123/',
        ];

        foreach ($testCases as $input => $expected) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
                'basePath' => $input,
            ];

            $config = new ExtensionConfiguration();

            $this->assertSame($expected, $config->getApiBasePath(), 'Failed for input: ' . $input);
        }
    }

    #[Test]
    public function returnsDefaultBasePathForInvalidFormats(): void
    {
        $invalidPaths = [
            'api/',              // Missing leading slash
            '/api',              // Missing trailing slash
            'api',               // Missing both slashes
            '/api/v2',           // Missing trailing slash
            '/api with spaces/', // Contains spaces
            '/api@v2/',          // Contains invalid character @
            '/api!/',            // Contains invalid character !
            '/api.v2/',          // Contains invalid character .
            '/api/v2//',         // Double trailing slash
            '//api/',            // Double leading slash
        ];

        foreach ($invalidPaths as $invalidPath) {
            $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
                'basePath' => $invalidPath,
            ];

            $config = new ExtensionConfiguration();

            $this->assertSame('/api/', $config->getApiBasePath(), 'Should return default for invalid path: ' . $invalidPath);
        }
    }

    #[Test]
    public function debugModeIsDisabledByDefault(): void
    {
        $config = new ExtensionConfiguration();

        $this->assertFalse($config->isDebugMode());
    }

    #[Test]
    public function debugModeCanBeEnabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
            'debugMode' => '1',
        ];

        $config = new ExtensionConfiguration();

        $this->assertTrue($config->isDebugMode());
    }

    #[Test]
    public function debugModeCanBeDisabled(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api'] = [
            'debugMode' => '0',
        ];

        $config = new ExtensionConfiguration();

        $this->assertFalse($config->isDebugMode());
    }
}
