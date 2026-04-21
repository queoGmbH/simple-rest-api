<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Value;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ApiEndpointParameter::class)]
final class ApiEndpointParameterTest extends UnitTestCase
{
    #[Test]
    public function constructor_stores_name_correctly(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('userId', 'int', 'The user identifier');

        // Assert
        self::assertSame('userId', $parameter->name);
    }

    #[Test]
    public function constructor_stores_type_correctly(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('userId', 'int', 'The user identifier');

        // Assert
        self::assertSame('int', $parameter->type);
    }

    #[Test]
    public function constructor_stores_description_correctly(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('userId', 'int', 'The user identifier');

        // Assert
        self::assertSame('The user identifier', $parameter->description);
    }

    #[Test]
    public function type_defaults_to_empty_string_when_omitted(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('myParam');

        // Assert
        self::assertSame('', $parameter->type);
    }

    #[Test]
    public function description_defaults_to_empty_string_when_omitted(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('myParam');

        // Assert
        self::assertSame('', $parameter->description);
    }

    #[Test]
    public function description_defaults_to_empty_string_when_only_type_is_provided(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('myParam', 'string');

        // Assert
        self::assertSame('', $parameter->description);
    }

    #[Test]
    public function supports_string_type(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('name', 'string', 'A name value');

        // Assert
        self::assertSame('string', $parameter->type);
    }

    #[Test]
    public function supports_float_type(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('price', 'float', 'A price value');

        // Assert
        self::assertSame('float', $parameter->type);
    }

    #[Test]
    public function supports_bool_type(): void // phpcs:ignore
    {
        // Arrange / Act
        $parameter = new ApiEndpointParameter('active', 'bool', 'Whether resource is active');

        // Assert
        self::assertSame('bool', $parameter->type);
    }
}
