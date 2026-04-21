<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Configuration;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteSettings;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ExtensionConfiguration::class)]
final class ExtensionConfigurationTest extends UnitTestCase
{
    #[Test]
    public function returnsDefaultBasePathWhenNoSiteAvailable(): void
    {
        $config = new ExtensionConfiguration();

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function returnsDefaultBasePathWhenRequestHasNoSite(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn(null);

        $config = new ExtensionConfiguration($request);

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function returnsCustomBasePathFromSiteSettings(): void
    {
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'basePath' => '/rest/',
            ],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        $basePath = $config->getApiBasePath();

        $this->assertSame('/rest/', $basePath);
    }

    #[Test]
    public function returnsDefaultBasePathWhenSettingIsEmpty(): void
    {
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'basePath' => '',
            ],
        ]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        $basePath = $config->getApiBasePath();

        $this->assertSame('/api/', $basePath);
    }

    #[Test]
    public function returnsDefaultBasePathWhenSettingIsNotSet(): void
    {
        $site = $this->createSiteWithSettings([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

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
            $site = $this->createSiteWithSettings([
                'simple_rest_api' => [
                    'basePath' => $input,
                ],
            ]);

            $request = $this->createMock(ServerRequestInterface::class);
            $request->expects(self::once())
                ->method('getAttribute')
                ->with('site')
                ->willReturn($site);

            $config = new ExtensionConfiguration($request);

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
            $site = $this->createSiteWithSettings([
                'simple_rest_api' => [
                    'basePath' => $invalidPath,
                ],
            ]);

            $request = $this->createMock(ServerRequestInterface::class);
            $request->expects(self::once())
                ->method('getAttribute')
                ->with('site')
                ->willReturn($site);

            $config = new ExtensionConfiguration($request);

            $this->assertSame('/api/', $config->getApiBasePath(), 'Should return default for invalid path: ' . $invalidPath);
        }
    }

    #[Test]
    public function returnsDebugModeAsTrueWhenSiteSettingIsBoolTrue(): void
    {
        // Arrange
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'debugMode' => true,
            ],
        ]);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        // Act
        $result = $config->isDebugMode();

        // Assert
        self::assertTrue($result);
    }

    #[Test]
    public function returnsDebugModeAsFalseWhenSiteSettingIsBoolFalse(): void
    {
        // Arrange
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'debugMode' => false,
            ],
        ]);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        // Act
        $result = $config->isDebugMode();

        // Assert
        self::assertFalse($result);
    }

    #[Test]
    public function returnsDebugModeAsTrueWhenSiteSettingIsStringTrue(): void
    {
        // Arrange
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'debugMode' => 'true',
            ],
        ]);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        // Act
        $result = $config->isDebugMode();

        // Assert
        self::assertTrue($result);
    }

    #[Test]
    public function returnsDebugModeAsTrueWhenSiteSettingIsStringOne(): void
    {
        // Arrange
        $site = $this->createSiteWithSettings([
            'simple_rest_api' => [
                'debugMode' => '1',
            ],
        ]);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::once())
            ->method('getAttribute')
            ->with('site')
            ->willReturn($site);

        $config = new ExtensionConfiguration($request);

        // Act
        $result = $config->isDebugMode();

        // Assert
        self::assertTrue($result);
    }

    #[Test]
    public function returnsDebugModeAsFalseWhenNoSiteAndNothingSet(): void
    {
        // Arrange
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']);

        $config = new ExtensionConfiguration();

        // Act
        $result = $config->isDebugMode();

        // Assert
        self::assertFalse($result);
    }

    #[Test]
    public function returnsDebugModeAsTrueWhenSetViaTypo3ConfVars(): void
    {
        // Arrange
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']['debugMode'] = true;

        $config = new ExtensionConfiguration();

        // Act
        $result = $config->isDebugMode();

        // Cleanup
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['simple_rest_api']);

        // Assert
        self::assertTrue($result);
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function createSiteWithSettings(array $settings): Site
    {
        return new Site(
            'test-site',
            1,
            [
                'base' => 'https://example.com/',
                'settings' => $settings,
            ]
        );
    }
}
