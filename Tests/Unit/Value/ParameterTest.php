<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Value;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Tests\Unit\Value\Fixture\DummyObject;

use ReflectionParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use Queo\SimpleRestApi\Value\Parameter;

#[CoversClass(Parameter::class)]
final class ParameterTest extends UnitTestCase
{
    #[Test]
    public function parameter_has_a_name(): void //phpcs:ignore
    {
        $reflectionNamedType = new ReflectionParameter([DummyObject::class, 'integerParamMethod'], 'param');
        $parameter = Parameter::createFromReflectionParameter($reflectionNamedType, 'value');

        $this->assertSame('param', $parameter->getName());
    }

    #[Test]
    #[DataProvider(methodName: 'parameterValues')]
    public function parameter_has_value(string $class, string $method, mixed $value, mixed $expected): void //phpcs:ignore
    {
        $reflectionNamedType = new ReflectionParameter([DummyObject::class, $method], 'param');
        $parameter = Parameter::createFromReflectionParameter($reflectionNamedType, $value);

        $this->assertSame($expected, $parameter->getValue());
    }

    public static function parameterValues(): array
    {
        return [
            'integer' => [
                'class' => DummyObject::class,
                'method' => 'integerParamMethod',
                'value' => '123',
                'expected' => 123,
            ],
            'float' => [
                'class' => DummyObject::class,
                'method' => 'floatParamMethod',
                'value' => '1.23',
                'expected' => 1.23
            ],
            'string' => [
                'class' => DummyObject::class,
                'method' => 'stringParamMethod',
                'value' => '123',
                'expected' => '123'
            ],
            'bool - 1' => [
                'class' => DummyObject::class,
                'method' => 'boolParamMethod',
                'value' => '1',
                'expected' => true
            ],
            'bool - true' => [
                'class' => DummyObject::class,
                'method' => 'boolParamMethod',
                'value' => 'true',
                'expected' => true
            ],
            'bool - 0' => [
                'class' => DummyObject::class,
                'method' => 'boolParamMethod',
                'value' => '0',
                'expected' => false
            ],
            'bool - false' => [
                'class' => DummyObject::class,
                'method' => 'boolParamMethod',
                'value' => 'false',
                'expected' => false
            ],
            'request object' => [
                'class' => DummyObject::class,
                'method' => 'requestParamMethod',
                'value' => $request = function (ParameterTest $test) {
                    return $test->createMock(ServerRequestInterface::class);
                },
                'expected' => $request
            ],
            'dummy object' => [
                'class' => DummyObject::class,
                'method' => 'objectParamMethod',
                'value' => $object = new DummyObject(),
                'expected' => $object
            ],
        ];
    }
}
