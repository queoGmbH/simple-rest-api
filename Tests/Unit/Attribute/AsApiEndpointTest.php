<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Attribute;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Attribute\AsApiEndpoint;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(AsApiEndpoint::class)]
final class AsApiEndpointTest extends UnitTestCase
{
    #[Test]
    public function constructor_stores_http_method(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test');

        // Assert
        self::assertSame('GET', $attribute->method);
    }

    #[Test]
    public function constructor_stores_path(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'POST', path: '/v1/my-endpoint');

        // Assert
        self::assertSame('/v1/my-endpoint', $attribute->path);
    }

    #[Test]
    public function summary_defaults_to_empty_string(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test');

        // Assert
        self::assertSame('', $attribute->summary);
    }

    #[Test]
    public function description_defaults_to_empty_string(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test');

        // Assert
        self::assertSame('', $attribute->description);
    }

    #[Test]
    public function tags_default_to_empty_array(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test');

        // Assert
        self::assertSame([], $attribute->tags);
    }

    #[Test]
    public function constructor_stores_summary(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test', summary: 'My endpoint summary');

        // Assert
        self::assertSame('My endpoint summary', $attribute->summary);
    }

    #[Test]
    public function constructor_stores_description(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test', description: 'Detailed description');

        // Assert
        self::assertSame('Detailed description', $attribute->description);
    }

    #[Test]
    public function constructor_stores_tags(): void // phpcs:ignore
    {
        // Arrange / Act
        $attribute = new AsApiEndpoint(method: 'GET', path: '/v1/test', tags: ['authenticated', 'public']);

        // Assert
        self::assertSame(['authenticated', 'public'], $attribute->tags);
    }

    #[Test]
    public function tag_name_constant_has_correct_value(): void // phpcs:ignore
    {
        // Assert
        self::assertSame('api.endpoint', AsApiEndpoint::TAG_NAME);
    }
}
