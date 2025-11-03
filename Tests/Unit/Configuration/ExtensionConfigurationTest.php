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
    public function supportsVariousBasePathFormats(): void
    {
        $testCases = [
            '/services/' => '/services/',
            '/v1/' => '/v1/',
            '/my-api/' => '/my-api/',
            '/api/v2/' => '/api/v2/',
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
