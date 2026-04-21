<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Context;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Configuration\ExtensionConfiguration;
use Queo\SimpleRestApi\Context\SimpleRestApiAspect;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(SimpleRestApiAspect::class)]
final class SimpleRestApiAspectTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    private function makeServerRequest(): ServerRequest
    {
        $site = new Site('test', 1, ['base' => 'https://example.com/', 'settings' => []]);
        $uri = new Uri('https://example.com/api/v1/test');
        return (new ServerRequest($uri, 'GET'))
            ->withAttribute('site', $site);
    }

    #[Test]
    public function get_request_returns_server_request(): void // phpcs:ignore
    {
        // Arrange
        $serverRequest = $this->makeServerRequest();
        $aspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $serverRequest);

        // Act
        $result = $aspect->getRequest();

        // Assert
        self::assertSame($serverRequest, $result);
    }

    #[Test]
    public function get_configuration_returns_extension_configuration(): void // phpcs:ignore
    {
        // Arrange
        $serverRequest = $this->makeServerRequest();
        $aspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $serverRequest);

        // Act
        $result = $aspect->getConfiguration();

        // Assert
        self::assertInstanceOf(ExtensionConfiguration::class, $result);
    }

    #[Test]
    public function get_with_request_property_returns_server_request(): void // phpcs:ignore
    {
        // Arrange
        $serverRequest = $this->makeServerRequest();
        $aspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $serverRequest);

        // Act
        $result = $aspect->get('request');

        // Assert
        self::assertSame($serverRequest, $result);
    }

    #[Test]
    public function get_with_configuration_property_returns_extension_configuration(): void // phpcs:ignore
    {
        // Arrange
        $serverRequest = $this->makeServerRequest();
        $aspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $serverRequest);

        // Act
        $result = $aspect->get('configuration');

        // Assert
        self::assertInstanceOf(ExtensionConfiguration::class, $result);
    }

    #[Test]
    public function get_with_unknown_property_throws_invalid_argument_exception(): void // phpcs:ignore
    {
        // Arrange
        $serverRequest = $this->makeServerRequest();
        $aspect = GeneralUtility::makeInstance(SimpleRestApiAspect::class, $serverRequest);

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(1647890123);

        // Act
        $aspect->get('unknownProperty');
    }

    #[Test]
    public function aspect_identifier_constant_has_correct_value(): void // phpcs:ignore
    {
        // Assert
        self::assertSame('simple_rest_api', SimpleRestApiAspect::ASPECT_IDENTIFIER);
    }
}
