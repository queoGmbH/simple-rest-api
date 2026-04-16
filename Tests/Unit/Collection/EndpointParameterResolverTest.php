<?php

declare(strict_types=1);

namespace Queo\SimpleRestApi\Tests\Unit\Collection;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ServerRequestInterface;
use Queo\SimpleRestApi\Collection\ApiEndpointParameterCollection;
use Queo\SimpleRestApi\Collection\EndpointParameterResolver;
use Queo\SimpleRestApi\Exception\InvalidParameterException;
use Queo\SimpleRestApi\Value\ApiEndpointParameter;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(EndpointParameterResolver::class)]
final class EndpointParameterResolverTest extends UnitTestCase
{
    #[Test]
    public function resolverGeneratesParameterArrayForMethodWithRequest(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int'),
                new ApiEndpointParameter('param2', 'string'),
                new ApiEndpointParameter('request', ServerRequestInterface::class)
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(3, $result);
        $this->assertSame(123, $result[0]);
        $this->assertSame('value', $result[1]);
        $this->assertSame($currentRequest, $result[2]);
    }

    #[Test]
    public function resolverGeneratesParameterArrayForMethodWithoutRequest(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int'),
                new ApiEndpointParameter('param2', 'string')
            ),
            [
                '123',
                'value'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(2, $result);
        $this->assertSame(123, $result[0]);
        $this->assertSame('value', $result[1]);
    }

    #[Test]
    public function resolverHandlesRequestAtBeginning(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('request', ServerRequestInterface::class),
                new ApiEndpointParameter('userId', 'int')
            ),
            [
                '42'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(2, $result);
        $this->assertSame($currentRequest, $result[0]);
        $this->assertSame(42, $result[1]);
    }

    #[Test]
    public function resolverHandlesRequestInMiddle(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('userId', 'int'),
                new ApiEndpointParameter('request', ServerRequestInterface::class),
                new ApiEndpointParameter('postId', 'int')
            ),
            [
                '42',
                '99'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertCount(3, $result);
        $this->assertSame(42, $result[0]);
        $this->assertSame($currentRequest, $result[1]);
        $this->assertSame(99, $result[2]);
    }

    #[Test]
    public function resolverCastsTypesCorrectly(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('intParam', 'int'),
                new ApiEndpointParameter('stringParam', 'string'),
                new ApiEndpointParameter('floatParam', 'float'),
                new ApiEndpointParameter('boolParam', 'bool')
            ),
            [
                '123',
                'hello',
                '3.14',
                '1'
            ],
            $currentRequest
        );

        $result = $resolver->buildMethodParameters();

        $this->assertSame(123, $result[0]);
        $this->assertIsInt($result[0]);
        $this->assertSame('hello', $result[1]);
        $this->assertIsString($result[1]);
        $this->assertSame(3.14, $result[2]);
        $this->assertIsFloat($result[2]);
        $this->assertSame(true, $result[3]);
        $this->assertIsBool($result[3]);
    }

    /**
     * @return array<string, array{string, string, mixed}>
     */
    public static function validCoercions(): array
    {
        return [
            'int positive'      => ['int',   '42',    42],
            'int negative'      => ['int',   '-7',    -7],
            'int zero'          => ['int',   '0',     0],
            'float decimal'     => ['float', '3.14',  3.14],
            'float integer'     => ['float', '2',     2.0],
            'float negative'    => ['float', '-1.5',  -1.5],
            'bool 1'            => ['bool',  '1',     true],
            'bool 0'            => ['bool',  '0',     false],
            'bool true'         => ['bool',  'true',  true],
            'bool false'        => ['bool',  'false', false],
            'bool yes'          => ['bool',  'yes',   true],
            'bool no'           => ['bool',  'no',    false],
            'bool on'           => ['bool',  'on',    true],
            'bool off'          => ['bool',  'off',   false],
            'string any'        => ['string', 'hello', 'hello'],
            'string empty'      => ['string', '',      ''],
        ];
    }

    #[Test]
    #[DataProvider('validCoercions')]
    public function resolverCoercesValidValues(string $type, string $raw, mixed $expected): void // phpcs:ignore
    {
        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(new ApiEndpointParameter('p', $type)),
            [$raw],
            $this->createStub(ServerRequestInterface::class)
        );

        $result = $resolver->buildMethodParameters();

        $this->assertSame($expected, $result[0]);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidCoercions(): array
    {
        return [
            'int letters'       => ['int',   'abc'],
            'int float string'  => ['int',   '1.5'],
            'int empty'         => ['int',   ''],
            'float letters'     => ['float', 'abc'],
            'float empty'       => ['float', ''],
            'bool random'       => ['bool',  'maybe'],
            'bool number two'   => ['bool',  '2'],
            'bool empty'        => ['bool',  ''],
        ];
    }

    #[Test]
    #[DataProvider('invalidCoercions')]
    public function resolverThrowsOnInvalidValue(string $type, string $raw): void // phpcs:ignore
    {
        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(new ApiEndpointParameter('myParam', $type)),
            [$raw],
            $this->createStub(ServerRequestInterface::class)
        );

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessageMatches("/myParam/");

        $resolver->buildMethodParameters();
    }

    #[Test]
    public function resolverReturnsParameterArray(): void
    {
        $currentRequest = $this->createMock(ServerRequestInterface::class);

        $resolver = new EndpointParameterResolver(
            new ApiEndpointParameterCollection(
                new ApiEndpointParameter('param1', 'int')
            ),
            [
                '123',
                'extra'
            ],
            $currentRequest
        );

        $paramArray = $resolver->getParameterArray();

        $this->assertSame(['123', 'extra'], $paramArray);
    }
}
